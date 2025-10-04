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

    /** OBTENER ENCABEZADO + DETALLE POR ID */
    public function obtenerPedidoPorId($id) {
        try {
            $stmt = $this->db->prepare("CALL spObtenerPedidoPorID(?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($pedido) {
                $pedido['detalles'] = $this->obtenerDetallesPedido($pedido['ID_ped']);
            }
            return $pedido ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener pedido por ID: " . $e->getMessage());
            return null;
        }
    }

    /** INSERTAR PEDIDO (ENCABEZADO + DETALLE) */
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

            // (Opcional) Recalcular totales en BD
            try {
                $recalc = $this->db->prepare("CALL spRecalcularTotalesPedido(?)");
                $recalc->bindParam(1, $pedidoId, PDO::PARAM_INT);
                $recalc->execute();
                $recalc->closeCursor();
            } catch (PDOException $e) {
                // Si no existe el SP, ignoramos y seguimos (ya calculaste en PHP)
                error_log("Aviso: spRecalcularTotalesPedido no disponible o falló en insertar(): ".$e->getMessage());
            }

            $this->db->commit();
            return $pedidoId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log("Error al insertar pedido: " . $e->getMessage());
            return false;
        }
    }

    /** ACTUALIZAR SOLO ESTADO (desde listado, si lo usas) */
    public function actualizarEstado($id, $estado) {
        try {
            $stmt = $this->db->prepare("CALL spActualizarEstadoPedido(?, ?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->bindParam(2, $estado, PDO::PARAM_STR);
            $ok = $stmt->execute();
            $stmt->closeCursor();
            return $ok;
        } catch (PDOException $e) {
            error_log("Error al actualizar estado del pedido: " . $e->getMessage());
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

    /**
     * === NUEVO ===
     * Actualiza TODO el pedido en una sola operación:
     * 1) Actualiza encabezado
     * 2) Elimina detalle existente
     * 3) Inserta detalle nuevo
     * 4) Recalcula totales
     *
     * @param array $datosPedido  (ID_ped, ID_emp?, ID_cli, ID_us, Fecha_ped, Descuento, Total_ped, Estado)
     * @param array $detalles     Array de ['ID_pro','Cantidad','Precio']
     * @return bool
     */
    public function actualizarPedidoCompleto($datosPedido, $detalles) {
    try {
        $this->db->beginTransaction();

        // 1) Actualizar ENCABEZADO
        // CALL spEditarPedido(p_id, p_cliente, p_fecha, p_descuento, p_total, p_estado)
        $stmtEnc = $this->db->prepare("CALL spEditarPedido(?, ?, ?, ?, ?, ?)");
        $stmtEnc->execute([
            (int)$datosPedido['ID_ped'],
            (int)$datosPedido['ID_cli'],
            $datosPedido['Fecha_ped'],
            (float)$datosPedido['Descuento'],
            (float)$datosPedido['Total_ped'],   // total ya calculado en PHP
            (string)$datosPedido['Estado']      // 'pendiente' | 'en proceso' | ...
        ]);
        $stmtEnc->closeCursor();

        // 2) LIMPIAR DETALLE
        $stmtDel = $this->db->prepare("CALL spEliminarDetallesPedido(?)");
        $stmtDel->execute([(int)$datosPedido['ID_ped']]);
        $stmtDel->closeCursor();

        // 3) INSERTAR NUEVO DETALLE
        // CALL spInsertarDetallePedido(pedido, producto, cantidad, precio)
        $stmtDet = $this->db->prepare("CALL spInsertarDetallePedido(?, ?, ?, ?)");
        foreach ($detalles as $d) {
            $stmtDet->execute([
                (int)$datosPedido['ID_ped'],
                (int)$d['ID_pro'],
                (float)$d['Cantidad'],
                (float)$d['Precio']
            ]);
            $stmtDet->closeCursor();
        }

        $this->db->commit();
        return true;

    } catch (Throwable $e) {
        if ($this->db->inTransaction()) $this->db->rollBack();
        error_log("actualizarPedidoCompleto(): ".$e->getMessage());
        return false;
    }
}


}
