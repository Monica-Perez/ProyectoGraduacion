<?php
class Pedido {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

    /** LISTADO CON DETALLES */
    public function ver() {
        try {
            $stmt = $this->db->prepare("CALL spVerPedidos()");
            $stmt->execute();
            $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();

            foreach ($pedidos as &$pedido) {
                $pedido['detalles'] = $this->obtenerDetallesPedido($pedido['ID_ped']);
            }
            return $pedidos;
        } catch (PDOException $e) {
            error_log("Error al obtener pedidos: " . $e->getMessage());
            return [];
        }
    }

    /* OBTENER ENCABEZADO */
    public function obtenerPedidoPorId($id) {
        try {
            $stmt = $this->db->prepare("CALL spObtenerPedidoPorID(?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();

            if ($pedido) {
                $pedido['detalles'] = $this->obtenerDetallesPedido($pedido['ID_ped']);
                if (method_exists($this, 'obtenerAbonos')) {
                    $pedido['abonos'] = $this->obtenerAbonos($pedido['ID_ped']);
                }
            }
            return $pedido ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener pedido por ID: " . $e->getMessage());
            return null;
        }
    }

    /* INSERTAR PEDIDO */
    public function insertar($datosPedido, $detalles) {
        try {
            $this->db->beginTransaction();

            // Insertar encabezado
            $stmt = $this->db->prepare(
                "CALL spInsertarPedido(:cliente, :usuario, :fecha, :descuento, :total, :estado, @nuevo_id)"
            );
            $stmt->bindValue(':cliente',  (int)$datosPedido['ID_cli'], PDO::PARAM_INT);

            $usuarioId = $datosPedido['ID_us'] ?? null;
            if ($usuarioId === null) {
                $stmt->bindValue(':usuario', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':usuario', (int)$usuarioId, PDO::PARAM_INT);
            }

            $stmt->bindValue(':fecha',     $datosPedido['Fecha_ped']);
            $stmt->bindValue(':descuento', (float)$datosPedido['Descuento']);
            $stmt->bindValue(':total',     (float)$datosPedido['Total_ped']);
            $stmt->bindValue(':estado',    (string)$datosPedido['Estado']);
            $stmt->execute();
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();

            // Obtener ID generado
            $idResult  = $this->db->query("SELECT @nuevo_id AS pedido_id");
            $pedidoRow = $idResult->fetch(PDO::FETCH_ASSOC);
            $idResult->closeCursor();
            $pedidoId = $pedidoRow['pedido_id'] ?? null;

            if (!$pedidoId) {
                throw new Exception('No se pudo obtener el ID del pedido generado.');
            }

            // Insertar detalle
            $detalleStmt = $this->db->prepare("CALL spInsertarDetallePedido(:pedido, :producto, :cantidad, :precio)");
            foreach ($detalles as $detalle) {
                $ok = $detalleStmt->execute([
                    'pedido'   => (int)$pedidoId,
                    'producto' => (int)$detalle['ID_pro'],
                    'cantidad' => (float)$detalle['Cantidad'],
                    'precio'   => (float)$detalle['Precio']
                ]);
                if (!$ok) {
                    $errorInfo = $detalleStmt->errorInfo();
                    throw new Exception("Fallo al insertar detalle: SQLSTATE {$errorInfo[0]}: {$errorInfo[2]}");
                }
                while ($detalleStmt->nextRowset()) { /* drenar */ }
                $detalleStmt->closeCursor();
            }

            $this->db->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error al insertar pedido: " . $e->getMessage());
            return false;
        }
    }

    /** DETALLE POR PEDIDO */
    public function obtenerDetallesPedido($pedidoId) {
        try {
            $stmt = $this->db->prepare("CALL spObtenerDetallesPedido(?)");
            $stmt->bindParam(1, $pedidoId, PDO::PARAM_INT);
            $stmt->execute();
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();
            return $detalles ?: [];
        } catch (PDOException $e) {
            error_log("Error al obtener detalles del pedido: " . $e->getMessage());
            return [];
        }
    }

    /** EDITAR PEDIDO (encabezado + detalle), con drenaje de CALLs */
    public function editar($id, $datos)
    {
        try {
            if (empty($id) || empty($datos['ID_cli']) || empty($datos['fecha']) || !isset($datos['total'])) {
                throw new Exception("Datos incompletos para editar pedido");
            }

            $detalle = $datos['detalle'] ?? [];
            if (empty($detalle)) {
                throw new Exception("El detalle no puede estar vacío");
            }

            // Normaliza estado por seguridad extra
            $estado = strtolower(trim((string)$datos['estado'] ?? 'pendiente'));

            // Helper para ejecutar CALL y consumir SIEMPRE todos los result sets
            $runCall = function (string $sql, array $params = []) {
                $stmt = $this->db->prepare($sql);
                if (!$stmt->execute($params)) {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception("Fallo CALL: {$sql} - SQLSTATE {$errorInfo[0]}: {$errorInfo[2]}");
                }
                while ($stmt->nextRowset()) { /* drenar */ }
                $stmt->closeCursor();
                return true;
            };

            $this->db->beginTransaction();

            // 1) Editar encabezado del pedido (incluye estado)
            $runCall(
                "CALL spEditarPedido(?,?,?,?,?,?)",
                [
                    (int)$id,
                    (int)$datos['ID_cli'],
                    (string)$datos['fecha'],
                    (float)($datos['descuento'] ?? 0),
                    (float)$datos['total'],
                    (string)$estado
                ]
            );

            // 2) Eliminar los detalles anteriores
            $runCall("CALL spEliminarDetallesPedido(?)", [(int)$id]);

            // 3) Insertar nuevamente los detalles
            $stmtDet = $this->db->prepare("CALL spInsertarDetallePedido(?,?,?,?)");
            foreach ($detalle as $d) {
                $ok = $stmtDet->execute([
                    (int)$id,
                    (int)$d['ID_pro'],
                    (float)$d['cantidad'],
                    (float)$d['precio']
                ]);
                if (!$ok) {
                    $errorInfo = $stmtDet->errorInfo();
                    throw new Exception("Fallo al insertar detalle: SQLSTATE {$errorInfo[0]}: {$errorInfo[2]}");
                }
                while ($stmtDet->nextRowset()) { /* drenar */ }
                $stmtDet->closeCursor();
            }

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error al editar pedido: " . $e->getMessage());
            return false;
        }
    }

    /** ELIMINAR PEDIDO + DETALLES */
    public function eliminar($id) {
        try {
            $this->db->beginTransaction();

            // 1) Eliminar detalles
            $stmt = $this->db->prepare("CALL spEliminarDetallesPedido(?)");
            $stmt->execute([$id]);
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();

            // 2) Eliminar encabezado
            $stmt = $this->db->prepare("CALL spEliminarPedido(?)");
            $stmt->execute([$id]);
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error al eliminar pedido: " . $e->getMessage());
            return false;
        }
    }

    /** OBTENER HISTÓRICO DE ABONOS DE UN PEDIDO */
    public function obtenerAbonos($idPedido) {
        try {
            $stmt = $this->db->prepare("CALL spVerAbonosPorPedido(?)");
            $stmt->execute([$idPedido]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();
            return $data;
        } catch (PDOException $e) {
            error_log("Error al obtener abonos: " . $e->getMessage());
            return [];
        }
    }

    /** REGISTRAR NUEVO ABONO (no degrada estados finales) */
    public function registrarAbono($idPedido, $fecha, $monto) {
        try {
            $this->db->beginTransaction();

            // 1) Insertar nuevo abono
            $stmt = $this->db->prepare("CALL spInsertarAbono(?, ?, ?)");
            $stmt->execute([(int)$idPedido, (string)$fecha, (float)$monto]);
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();

            // 2) Recalcular totales y estado actual
            $stmt = $this->db->prepare("
                SELECT p.Estado, p.Total_ped, COALESCE(SUM(a.Monto_abono),0) AS total_abonado
                FROM pedido p
                LEFT JOIN abono a ON a.ID_ped = p.ID_ped
                WHERE p.ID_ped = ?
                GROUP BY p.ID_ped, p.Estado, p.Total_ped
            ");
            $stmt->execute([(int)$idPedido]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($data) {
                $estadoActual = strtolower(trim((string)$data['Estado']));
                $totalPedido  = (float)$data['Total_ped'];
                $totalAbonado = (float)$data['total_abonado'];

                if (!in_array($estadoActual, ['completado','cancelado'], true)) {
                    // Promover a 'en proceso' solo si estaba 'pendiente' y se alcanzó >= 50%
                    if ($totalAbonado >= ($totalPedido * 0.5) && $estadoActual === 'pendiente') {
                        $up = $this->db->prepare("UPDATE pedido SET Estado = 'en proceso' WHERE ID_ped = ? AND Estado = 'pendiente'");
                        $up->execute([(int)$idPedido]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error al registrar abono: " . $e->getMessage()); // <- aquí
            return false;
        }

    }

    public function eliminarAbono($idAbono) {
        try {
            $stmt = $this->db->prepare("CALL spEliminarAbono(?)");
            $stmt->execute([(int)$idAbono]);
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();
            return true;
        } catch (Throwable $e) {
            error_log("Error al eliminar abono: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerResumenPago($idPedido) {
        try {
            $stmt = $this->db->prepare("CALL spObtenerResumenPago(?)");
            $stmt->execute([$idPedido]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            while ($stmt->nextRowset()) { /* drenar */ }
            $stmt->closeCursor();
            return $row ?: ['Total_ped' => 0, 'total_abonado' => 0];
        } catch (PDOException $e) {
            error_log("Error obtenerResumenPago: " . $e->getMessage());
            return ['Total_ped' => 0, 'total_abonado' => 0];
        }
    }
}
