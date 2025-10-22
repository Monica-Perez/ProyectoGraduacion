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
            $stmt->bindValue(':estado',    $datosPedido['Estado']);
            $stmt->execute();
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
                $detalleStmt->execute([
                    'pedido'   => (int)$pedidoId,
                    'producto' => (int)$detalle['ID_pro'],
                    'cantidad' => (float)$detalle['Cantidad'],
                    'precio'   => (float)$detalle['Precio']
                ]);
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
            $stmt->closeCursor();
            return $detalles ?: [];
        } catch (PDOException $e) {
            error_log("Error al obtener detalles del pedido: " . $e->getMessage());
            return [];
        }
    }

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

            $this->db->beginTransaction();

            // 1️⃣ Editar encabezado del pedido
            $stmt = $this->db->prepare("CALL spEditarPedido(?,?,?,?,?,?)");
            $stmt->execute([
                $id,
                $datos['ID_cli'],
                $datos['fecha'],
                $datos['descuento'] ?? 0,
                $datos['total'],
                $datos['estado']
            ]);
            $stmt->closeCursor();

            // 2️⃣ Eliminar los detalles anteriores
            $stmt = $this->db->prepare("CALL spEliminarDetallesPedido(?)");
            $stmt->execute([$id]);
            $stmt->closeCursor();

            // 3️⃣ Insertar nuevamente los detalles
            $stmtDet = $this->db->prepare("CALL spInsertarDetallePedido(?,?,?,?)");
            foreach ($detalle as $d) {
                $stmtDet->execute([
                    $id,
                    $d['ID_pro'],
                    $d['cantidad'],
                    $d['precio']
                ]);
                $stmtDet->closeCursor();
            }

            $this->db->commit();
            return true;

        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log("Error al editar pedido: " . $e->getMessage());
            return false;
        }
    }

    /** ELIMINAR PEDIDO + DETALLES */
    public function eliminar($id) {
        try {
            $this->db->beginTransaction();

            // 1️⃣ Eliminar detalles
            $stmt = $this->db->prepare("CALL spEliminarDetallesPedido(?)");
            $stmt->execute([$id]);
            $stmt->closeCursor();

            // 2️⃣ Eliminar encabezado
            $stmt = $this->db->prepare("CALL spEliminarPedido(?)");
            $stmt->execute([$id]);
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
            $stmt->closeCursor();
            return $data;
        } catch (PDOException $e) {
            error_log("Error al obtener abonos: " . $e->getMessage());
            return [];
        }
    }

    /** REGISTRAR NUEVO ABONO */
    public function registrarAbono($idPedido, $fecha, $monto) {
        try {
            $this->db->beginTransaction();

            // Insertar nuevo abono
            $stmt = $this->db->prepare("CALL spInsertarAbono(?, ?, ?)");
            $stmt->execute([$idPedido, $fecha, $monto]);
            $stmt->closeCursor();

            // Verificar total abonado *************
            $stmt = $this->db->prepare("
                SELECT SUM(Monto_abono) AS total_abonado, p.Total_ped
                FROM abono a
                JOIN pedido p ON p.ID_ped = a.ID_ped
                WHERE a.ID_ped = ?
                GROUP BY p.Total_ped
            ");
            $stmt->execute([$idPedido]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($data && $data['total_abonado'] >= ($data['Total_ped'] * 0.5)) {
                $stmt = $this->db->prepare("UPDATE pedido SET Estado = 'en proceso' WHERE ID_ped = ?");
                $stmt->execute([$idPedido]);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log("Error al registrar abono: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarAbono($idAbono) {
        try {
            $stmt = $this->db->prepare("CALL spEliminarAbono(?)");
            $stmt->execute([$idAbono]);
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
            $stmt->closeCursor();
            return $row ?: ['Total_ped' => 0, 'total_abonado' => 0];
        } catch (PDOException $e) {
            error_log("Error obtenerResumenPago: " . $e->getMessage());
            return ['Total_ped' => 0, 'total_abonado' => 0];
        }
    }

}
