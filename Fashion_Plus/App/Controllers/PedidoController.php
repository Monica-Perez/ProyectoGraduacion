<?php
class PedidoController extends Controller {

    private $estadosPermitidos = ['pendiente', 'en proceso', 'completado', 'cancelado'];

    public function __construct() {
        session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }
    }

    /** VER LISTADO DE PEDIDOS */
    public function ver() {
        $pedidoModel = $this->model('Pedido');
        $pedidos = $pedidoModel->ver();

        $this->view('pedido/ver', [
            'pedidos' => $pedidos,
            'estados' => $this->estadosPermitidos
        ]);
    }

    /** REGISTRAR PEDIDO */
    // public function registrar() {
    //     $clienteModel  = $this->model('Cliente');
    //     $empresaModel  = $this->model('Empresa');
    //     $productoModel = $this->model('Producto');
    //     $pedidoModel   = $this->model('Pedido');
        
    //     $clientes = $clienteModel->ver();
    //     $empresas = $empresaModel->ver();
    //     $productos = $productoModel->ver();

    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $idEmpresa = $_POST['ID_emp'] ?? '';
    //         $idCliente = $_POST['ID_cli'] ?? '';
    //         $fecha     = $_POST['fecha'] ?? date('Y-m-d');
    //         $estado    = $_POST['estado'] ?? 'pendiente';
    //         $descuento = isset($_POST['descuento']) ? (float)$_POST['descuento'] : 0;

    //         $productosSeleccionados = $_POST['producto_id'] ?? [];
    //         $cantidades = $_POST['cantidad'] ?? [];
    //         $precios = $_POST['precio'] ?? [];

    //         // Construir detalle
    //         $detalles = [];
    //         $subtotal = 0;
    //         for ($i = 0; $i < count($productosSeleccionados); $i++) {
    //             $idPro  = (int)$productosSeleccionados[$i];
    //             $cant   = (float)($cantidades[$i] ?? 0);
    //             $precio = (float)($precios[$i] ?? 0);
    //             if ($idPro > 0 && $cant > 0) {
    //                 $detalles[] = ['ID_pro' => $idPro, 'Cantidad' => $cant, 'Precio' => $precio];
    //                 $subtotal += $cant * $precio;
    //             }
    //         }
    //         $total = max($subtotal - $descuento, 0);

    //         $datosPedido = [
    //             'ID_cli'     => $idCliente,
    //             'ID_us'      => $_SESSION['usuario']['ID_us'] ?? null,
    //             'Fecha_ped'  => $fecha,
    //             'Descuento'  => $descuento,
    //             'Total_ped'  => $total,
    //             'Estado'     => $estado
    //         ];

    //         try {
    //             $exito = $pedidoModel->insertar($datosPedido, $detalles);
    //             if ($exito) {
    //                 header('Location: ' . URL . 'pedido/ver');
    //                 exit;
    //             } else {
    //                 $clientes = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
    //                 $this->view('pedido/registrar', [
    //                     'error' => 'Error al registrar pedido',
    //                     'empresas' => $empresas,
    //                     'clientes' => $clientes,
    //                     'productos' => $productos,
    //                     'estados' => $this->estadosPermitidos
    //                 ]);
    //             }
    //         } catch (Exception $e) {
    //             $clientes = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
    //             $this->view('pedido/registrar', [
    //                 'error' => 'Error al registrar pedido: ' . $e->getMessage(),
    //                 'empresas' => $empresas,
    //                 'clientes' => $clientes,
    //                 'productos' => $productos,
    //                 'estados' => $this->estadosPermitidos
    //             ]);
    //         }
    //     } else {
    //         $this->view('pedido/registrar', [
    //             'empresas' => $empresas,
    //             'clientes' => [],
    //             'productos' => $productos,
    //             'estados' => $this->estadosPermitidos
    //         ]);
    //     }
    // }

    /** REGISTRAR PEDIDO (GET/POST, con abonos) */
public function registrar() {
    $pedidoModel   = $this->model('Pedido');
    $clienteModel  = $this->model('Cliente');
    $empresaModel  = $this->model('Empresa');
    $productoModel = $this->model('Producto');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idEmpresa = $_POST['ID_emp'] ?? '';
        $idCliente = $_POST['ID_cli'] ?? '';
        $fecha     = $_POST['fecha'] ?? date('Y-m-d');
        $estado    = $_POST['estado'] ?? 'pendiente';
        $descuento = isset($_POST['descuento']) ? (float)$_POST['descuento'] : 0;

        // Detalle (mismo patrón que en editar, pero el modelo insertar() espera 'Cantidad' y 'Precio' capitalizados)
        $productosSeleccionados = $_POST['producto_id'] ?? [];
        $cantidades             = $_POST['cantidad'] ?? [];
        $precios                = $_POST['precio'] ?? [];

        $detalles = [];
        $subtotal = 0;
        for ($i = 0; $i < count($productosSeleccionados); $i++) {
            $idPro  = (int)$productosSeleccionados[$i];
            $cant   = (float)($cantidades[$i] ?? 0);
            $precio = (float)($precios[$i] ?? 0);
            if ($idPro > 0 && $cant > 0) {
                $detalles[] = ['ID_pro' => $idPro, 'Cantidad' => $cant, 'Precio' => $precio];
                $subtotal  += $cant * $precio;
            }
        }
        $total = max($subtotal - $descuento, 0);

        // Abonos (opcionales) – mismos nombres que usarás en la vista registrar
        $fechasAbono = $_POST['fecha_abono'] ?? [];
        $montosAbono = $_POST['monto_abono'] ?? [];
        $abonos = [];
        $nAb = min(count($fechasAbono), count($montosAbono));
        for ($i = 0; $i < $nAb; $i++) {
            $monto = (float)$montosAbono[$i];
            $fechaAb = $fechasAbono[$i] ?: $fecha;
            if ($monto > 0) {
                $abonos[] = ['fecha' => $fechaAb, 'monto' => $monto];
            }
        }

        // Regla: no permitir saldo negativo
        $totalAbonos = array_sum(array_column($abonos, 'monto'));
        if ($totalAbonos > $total) {
            $empresas  = $empresaModel->ver();
            $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa((int)$idEmpresa) : [];
            $productos = $productoModel->ver();

            $this->view('pedido/registrar', [
                'empresas'  => $empresas,
                'clientes'  => $clientes,
                'productos' => $productos,
                'estados'   => $this->estadosPermitidos,
                'error'     => 'La suma de abonos no puede exceder el total del pedido.',
                'form'      => $_POST
            ]);
            return;
        }

        // Encabezado para insertar
        $datosPedido = [
            'ID_cli'     => $idCliente,
            'ID_us'      => $_SESSION['usuario']['ID_us'] ?? null,
            'Fecha_ped'  => $fecha,
            'Descuento'  => $descuento,
            'Total_ped'  => $total,
            'Estado'     => $estado
        ];

        try {
            $idNuevo = $pedidoModel->insertar($datosPedido, $detalles);
            if ($idNuevo) {
                // Registrar abonos (si los hay)
                foreach ($abonos as $ab) {
                    if ($ab['monto'] > 0) {
                        $pedidoModel->registrarAbono($idNuevo, $ab['fecha'], $ab['monto']);
                    }
                }
                header('Location: ' . URL . 'pedido/ver');
                exit;
            } else {
                $empresas  = $empresaModel->ver();
                $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa((int)$idEmpresa) : [];
                $productos = $productoModel->ver();

                $this->view('pedido/registrar', [
                    'error'     => 'Error al registrar pedido',
                    'empresas'  => $empresas,
                    'clientes'  => $clientes,
                    'productos' => $productos,
                    'estados'   => $this->estadosPermitidos,
                    'form'      => $_POST
                ]);
            }
        } catch (Exception $e) {
            $empresas  = $empresaModel->ver();
            $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa((int)$idEmpresa) : [];
            $productos = $productoModel->ver();

            $this->view('pedido/registrar', [
                'error'     => 'Error al registrar pedido: ' . $e->getMessage(),
                'empresas'  => $empresas,
                'clientes'  => $clientes,
                'productos' => $productos,
                'estados'   => $this->estadosPermitidos,
                'form'      => $_POST
            ]);
        }
    } else {
        // GET
        $empresas  = $empresaModel->ver();
        $productos = $productoModel->ver();

        $this->view('pedido/registrar', [
            'empresas'  => $empresas,
            'clientes'  => [], 
            'productos' => $productos,
            'estados'   => $this->estadosPermitidos
        ]);
    }
}


    // API: CLIENTES POR EMPRESA (para selects dependientes)
    public function clientesPorEmpresa($empresaId = null) {
        header('Content-Type: application/json; charset=utf-8');

        if (!$empresaId) {
            echo json_encode([]);
            exit;
        }

        $clienteModel = $this->model('Cliente');
        $clientes = $clienteModel->obtenerClientesPorEmpresa((int) $empresaId);

        echo json_encode($clientes, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** EDITAR PEDIDO */
    public function editar($id = null) {
        $pedidoModel   = $this->model('Pedido');
        $clienteModel  = $this->model('Cliente');
        $empresaModel  = $this->model('Empresa');
        $productoModel = $this->model('Producto');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['ID_ped'];
            $idEmpresa = $_POST['ID_emp'] ?? '';
            $idCliente = $_POST['ID_cli'] ?? '';
            $fecha     = $_POST['fecha'] ?? date('Y-m-d');
            $estado    = $_POST['estado'] ?? 'pendiente';
            $descuento = isset($_POST['descuento']) ? (float)$_POST['descuento'] : 0;

            $productosSeleccionados = $_POST['producto_id'] ?? [];
            $cantidades = $_POST['cantidad'] ?? [];
            $precios = $_POST['precio'] ?? [];

            // Construir detalle
            $detalles = [];
            $subtotal = 0;
            for ($i = 0; $i < count($productosSeleccionados); $i++) {
                $idPro  = (int)$productosSeleccionados[$i];
                $cant   = (float)($cantidades[$i] ?? 0);
                $precio = (float)($precios[$i] ?? 0);
                if ($idPro > 0 && $cant > 0) {
                    $detalles[] = ['ID_pro' => $idPro, 'cantidad' => $cant, 'precio' => $precio];
                    $subtotal += $cant * $precio;
                }
            }
            $total = max($subtotal - $descuento, 0);

            $datos = [
                'ID_cli'    => $idCliente,
                'fecha'     => $fecha,
                'descuento' => $descuento,
                'total'     => $total,
                'estado'    => $estado,
                'detalle'   => $detalles
            ];

            try {
                $pedidoModel->editar($id, $datos);
                header('Location: ' . URL . 'pedido/ver');
                exit;
            } catch (Exception $e) {
                $pedido = $pedidoModel->obtenerPedidoPorId($id);
                $empresas = $empresaModel->ver();
                $idEmpresa = (int)($pedido['ID_emp'] ?? 0);
                $clientes = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
                $productos = $productoModel->ver();
                
                // 🔹 Cargar abonos del pedido para la vista
                if (method_exists($pedidoModel, 'obtenerAbonos')) {
                    $pedido['abonos'] = $pedidoModel->obtenerAbonos((int)$id);
                }
                $this->view('pedido/editar', [
                    'pedido' => $pedido,
                    'empresas' => $empresas,
                    'clientes' => $clientes,
                    'productos' => $productos,
                    'estados' => $this->estadosPermitidos,
                    'error' => 'Error al editar pedido: ' . $e->getMessage()
                ]);
            }
            
        } else {
            if (!$id) {
                header('Location: ' . URL . 'pedido/ver');
                exit;
            }

            $pedido = $pedidoModel->obtenerPedidoPorId($id);
            $empresas = $empresaModel->ver();
            $idEmpresa = (int)($pedido['ID_emp'] ?? 0);
            $clientes = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
            $productos = $productoModel->ver();

            $this->view('pedido/editar', [
                'pedido' => $pedido,
                'empresas' => $empresas,
                'clientes' => $clientes,
                'productos' => $productos,
                'estados' => $this->estadosPermitidos
            ]);
        }
    }

    /** ELIMINAR PEDIDO */
    public function eliminar($id = null) {
        if (!isset($_SESSION)) session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }

        if ($id) {
            $pedidoModel = $this->model('Pedido');
            $pedidoModel->eliminar($id);
        }

        header('Location: ' . URL . 'pedido/ver');
        exit;
    }

    public function abonar($idPedido = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$idPedido) {
                header('Location: ' . URL . 'pedido/ver');
                exit;
            }

            $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0;
            $fecha = $_POST['fecha_abono'] ?? date('Y-m-d');

            $pedidoModel   = $this->model('Pedido');
            $clienteModel  = $this->model('Cliente');
            $empresaModel  = $this->model('Empresa');
            $productoModel = $this->model('Producto');

            // Intentar guardar
            $exito = false;
            try {
                $exito = $pedidoModel->registrarAbono($idPedido, $fecha, $monto);
            } catch (Exception $e) {
                // opcional: log
            }

            if ($exito) {
                // éxito -> redirige, como en tus otros controladores
                header('Location: ' . URL . 'pedido/editar/' . $idPedido);
                exit;
            } else {
                // fallo -> volver a pintar la vista de editar con 'error'
                $pedido    = $pedidoModel->obtenerPedidoPorId($idPedido);
                $empresas  = $empresaModel->ver();
                $idEmpresa = (int)($pedido['ID_emp'] ?? 0);
                $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
                $productos = $productoModel->ver();

                // si ya tienes obtenerAbonos en el modelo, pásalos; si no, omite esta línea
                if (method_exists($pedidoModel, 'obtenerAbonos')) {
                    $pedido['abonos'] = $pedidoModel->obtenerAbonos($idPedido);
                }

                $this->view('pedido/editar', [
                    'pedido'    => $pedido,
                    'empresas'  => $empresas,
                    'clientes'  => $clientes,
                    'productos' => $productos,
                    'estados'   => $this->estadosPermitidos,
                    'error'     => 'Error al registrar abono'
                ]);
            }
        } else {
            header('Location: ' . URL . 'pedido/ver');
            exit;
        }
    }

    public function eliminarAbono($idAbono = null, $idPedido = null) {
        if (!isset($_SESSION)) session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }

        if ($idAbono) {
            $pedidoModel = $this->model('Pedido');
            try {
                $pedidoModel->eliminarAbono((int)$idAbono);
            } catch (Exception $e) {
                // opcional: log o pasar un mensaje en sesión
            }
        }

        $idPedido = (int)($idPedido ?: 0);
        header('Location: ' . URL . 'pedido/editar/' . $idPedido);
        exit;
    }

}
