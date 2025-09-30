<?php
class Pedido {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conectar();
    }

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

    public function insertar($datosPedido, $detalles) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("CALL spInsertarPedido(:cliente, :usuario, :fecha, :descuento, :total, :estado, @nuevo_id)");
            $stmt->bindValue(':cliente', $datosPedido['ID_cli'], PDO::PARAM_INT);

            $usuarioId = $datosPedido['ID_us'] ?? null;
            if ($usuarioId === null) {
                $stmt->bindValue(':usuario', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':usuario', $usuarioId, PDO::PARAM_INT);
            }

            $stmt->bindValue(':fecha', $datosPedido['Fecha_ped']);
            $stmt->bindValue(':descuento', $datosPedido['Descuento']);
            $stmt->bindValue(':total', $datosPedido['Total_ped']);
            $stmt->bindValue(':estado', $datosPedido['Estado']);

            $stmt->execute();
            $stmt->closeCursor();

            $idResult = $this->db->query("SELECT @nuevo_id AS pedido_id");
            $pedidoRow = $idResult->fetch(PDO::FETCH_ASSOC);
            $idResult->closeCursor();

            $pedidoId = $pedidoRow['pedido_id'] ?? null;

            if (!$pedidoId) {
                throw new Exception('No se pudo obtener el ID del pedido generado.');
            }

            $detalleStmt = $this->db->prepare("CALL spInsertarDetallePedido(:pedido, :producto, :cantidad, :precio)");

            foreach ($detalles as $detalle) {
                $detalleStmt->execute([
                    'pedido' => $pedidoId,
                    'producto' => $detalle['ID_pro'],
                    'cantidad' => $detalle['Cantidad'],
                    'precio' => $detalle['Precio']
                ]);
                $detalleStmt->closeCursor();
            }

            $this->db->commit();
            return $pedidoId;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error al insertar pedido: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error al insertar pedido: " . $e->getMessage());
            return false;
        }
    }

    // public function actualizarEstado($id, $estado) {
    //     try {
    //         $stmt = $this->db->prepare("CALL spActualizarEstadoPedido(?, ?)");
    //         $stmt->bindParam(1, $id, PDO::PARAM_INT);
    //         $stmt->bindParam(2, $estado, PDO::PARAM_STR);
    //         $resultado = $stmt->execute();
    //         $stmt->closeCursor();

    //         return $resultado;
    //     } catch (PDOException $e) {
    //         error_log("Error al actualizar estado del pedido: " . $e->getMessage());
    //         return false;
    //     }
    // }

    public function obtenerDetallesPedido($pedidoId) {
        try {
            $stmt = $this->db->prepare("CALL spObtenerDetallesPedido(?)");
            $stmt->bindParam(1, $pedidoId, PDO::PARAM_INT);
            $stmt->execute();
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $detalles;
        } catch (PDOException $e) {
            error_log("Error al obtener detalles del pedido: " . $e->getMessage());
            return [];
        }
    }

    // public function editar($id = null) {
    //     $pedidoModel   = $this->model('Pedido');
    //     $clienteModel  = $this->model('Cliente');
    //     $empresaModel  = $this->model('Empresa');
    //     $productoModel = $this->model('Producto');

    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $id = (int)($_POST['ID_ped'] ?? 0);

    //         // -------- $datos (encabezado + detalle) --------
    //         $datos = [
    //             'ID_cli'    => (int)($_POST['ID_cli'] ?? 0),
    //             'Fecha_ped' => $_POST['fecha'] ?? date('Y-m-d'),
    //             'Descuento' => (float)($_POST['descuento'] ?? 0),
    //             'Estado'    => $_POST['estado'] ?? 'pendiente',
    //             'detalles'  => []
    //         ];

    //         // construir detalle desde arrays del form
    //         $prods = $_POST['producto_id'] ?? [];
    //         $cants = $_POST['cantidad']    ?? [];
    //         $pres  = $_POST['precio']      ?? [];
    //         $n = min(count($prods), count($cants), count($pres));

    //         $subtotal = 0;
    //         for ($i = 0; $i < $n; $i++) {
    //             $pid = (int)$prods[$i]; $can = (int)$cants[$i]; $pre = (float)$pres[$i];
    //             if ($pid > 0 && $can > 0) {
    //                 $datos['detalles'][] = ['ID_pro' => $pid, 'Cantidad' => $can, 'Precio' => $pre];
    //                 $subtotal += $can * $pre;
    //             }
    //         }
    //         $datos['Total_ped'] = max($subtotal - $datos['Descuento'], 0);

    //         // editar en BD (encabezado + detalle)
    //         if ($pedidoModel->editar($id, $datos)) {
    //             header('Location: ' . URL . 'pedido/ver');
    //             exit;
    //         }

    //         // Si falla, recargar vista con datos (mínimo necesario)
    //         $pedido = $pedidoModel->obtenerPedidoPorId($id);
    //         $idEmp = $this->model('Cliente')->obtenerClientePorId($datos['ID_cli'])['ID_emp'] ?? null;

    //         $this->view('pedido/editar', [
    //             'ID_ped'    => $id,
    //             'empresas'  => $empresaModel->ver(),
    //             'clientes'  => $idEmp ? $clienteModel->obtenerClientesPorEmpresa((int)$idEmp) : [],
    //             'productos' => $productoModel->ver(),
    //             'estados'   => $this->estadosPermitidos,
    //             'form'      => [
    //                 'ID_emp'    => $idEmp,
    //                 'ID_cli'    => $datos['ID_cli'],
    //                 'fecha'     => $datos['Fecha_ped'],
    //                 'estado'    => $datos['Estado'],
    //                 'descuento' => $datos['Descuento'],
    //             ],
    //             'rowsData'  => array_map(fn($d)=>[
    //                 'producto_id'=>$d['ID_pro'],'cantidad'=>$d['Cantidad'],'precio'=>$d['Precio']
    //             ], $datos['detalles']),
    //             'errores'   => ['No se pudo actualizar el pedido.']
    //         ]);
    //         return;
    //     }

    //     // GET: pintar vista editar con datos actuales
    //     if (!$id) { header('Location: ' . URL . 'pedido/ver'); exit; }

    //     $pedido = $pedidoModel->obtenerPedidoPorId((int)$id);
    //     if (!$pedido) { header('Location: ' . URL . 'pedido/ver'); exit; }

    //     // obtener empresa desde el cliente del pedido
    //     $cli   = $this->model('Cliente')->obtenerClientePorId($pedido['ID_cli']);
    //     $idEmp = $cli['ID_emp'] ?? null;

    //     $this->view('pedido/editar', [
    //         'ID_ped'    => $pedido['ID_ped'],
    //         'empresas'  => $empresaModel->ver(),
    //         'clientes'  => $idEmp ? $clienteModel->obtenerClientesPorEmpresa((int)$idEmp) : [],
    //         'productos' => $productoModel->ver(),
    //         'estados'   => $this->estadosPermitidos,
    //         'form'      => [
    //             'ID_emp'    => $idEmp,
    //             'ID_cli'    => $pedido['ID_cli'],
    //             'fecha'     => $pedido['Fecha_ped'],
    //             'estado'    => $pedido['Estado'],
    //             'descuento' => (float)$pedido['Descuento'],
    //         ],
    //         'rowsData'  => array_map(fn($d)=>[
    //             'producto_id'=>$d['ID_pro'],'cantidad'=>(int)$d['Cantidad'],'precio'=>(float)$d['Precio']
    //         ], $pedido['detalles'] ?? []),
    //         'errores'   => []
    //     ]);
    // }

}