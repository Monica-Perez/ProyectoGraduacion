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

    // LISTADO
    public function ver() {
        $pedidoModel = $this->model('Pedido');
        $pedidos = $pedidoModel->ver();

        $this->view('pedido/ver', [
            'pedidos' => $pedidos,
            'estados' => $this->estadosPermitidos
        ]);
    }

    // REGISTRO (GET/POST)
    public function registrar() {
        $clienteModel = $this->model('Cliente');
        $empresaModel = $this->model('Empresa');
        $productoModel = $this->model('Producto');

        $empresas = $empresaModel->ver();
        $productos = $productoModel->ver();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errores = [];
            $pedidoModel = $this->model('Pedido');

            $idCliente = $_POST['ID_cli'] ?? '';
            $idEmpresa = $_POST['ID_emp'] ?? '';
            $fecha = $_POST['fecha'] ?? date('Y-m-d');
            $estado = $_POST['estado'] ?? 'pendiente';
            $descuento = isset($_POST['descuento']) ? (float) $_POST['descuento'] : 0;

            $productosSeleccionados = $_POST['producto_id'] ?? [];
            $cantidades = $_POST['cantidad'] ?? [];
            $precios = $_POST['precio'] ?? [];

            if (!$idEmpresa) $errores[] = 'Debe seleccionar una empresa.';
            if (!$idCliente) $errores[] = 'Debe seleccionar un cliente.';
            if (!in_array($estado, $this->estadosPermitidos, true)) $errores[] = 'El estado seleccionado no es válido.';
            if ($descuento < 0) $errores[] = 'El descuento no puede ser negativo.';

            $clientesDisponibles = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa((int) $idEmpresa) : [];

            if ($idCliente) {
                $clienteSeleccionado = $clienteModel->obtenerClientePorId($idCliente);
                if (!$clienteSeleccionado || (int) ($clienteSeleccionado['ID_emp'] ?? 0) !== (int) $idEmpresa) {
                    $errores[] = 'El cliente seleccionado no pertenece a la empresa elegida.';
                }
            }

            $detalles = [];
            $subtotal = 0;
            $detallesCount = min(count($productosSeleccionados), count($cantidades));

            for ($i = 0; $i < $detallesCount; $i++) {
                $productoId = $productosSeleccionados[$i] ?? '';
                $cantidad = isset($cantidades[$i]) ? (int) $cantidades[$i] : 0;
                $precio = isset($precios[$i]) ? (float) $precios[$i] : 0;

                if ($productoId && $cantidad > 0) {
                    $detalles[] = [
                        'ID_pro' => $productoId,
                        'Cantidad' => $cantidad,
                        'Precio' => $precio
                    ];
                    $subtotal += $cantidad * $precio;
                }
            }

            if (empty($detalles)) {
                $errores[] = 'Debe agregar al menos un producto al pedido.';
            }

            $total = max($subtotal - $descuento, 0);

            if (empty($errores)) {
                $datosPedido = [
                    'ID_cli' => $idCliente,
                    'ID_us' => $_SESSION['usuario']['ID_us'] ?? null,
                    'Fecha_ped' => $fecha,
                    'Descuento' => $descuento,
                    'Total_ped' => $total,
                    'Estado' => $estado
                ];

                $resultado = $pedidoModel->insertar($datosPedido, $detalles);

                if ($resultado) {
                    header('Location: ' . URL . 'pedido/ver');
                    exit;
                }

                $errores[] = 'Ocurrió un error al guardar el pedido.';
            }

            $this->view('pedido/registrar', [
                'empresas' => $empresas,
                'clientes' => $clientesDisponibles,
                'productos' => $productos,
                'estados' => $this->estadosPermitidos,
                'errores' => $errores,
                'form' => [
                    'ID_emp' => $idEmpresa,
                    'ID_cli' => $idCliente,
                    'fecha' => $fecha,
                    'estado' => $estado,
                    'descuento' => $descuento,
                    'productos' => $productosSeleccionados,
                    'cantidades' => $cantidades,
                    'precios' => $precios
                ]
            ]);
        } else {
            $this->view('pedido/registrar', [
                'empresas' => $empresas,
                'clientes' => [],
                'productos' => $productos,
                'estados' => $this->estadosPermitidos,
                'form' => [
                    'ID_emp' => '',
                    'fecha' => date('Y-m-d'),
                    'estado' => 'pendiente',
                    'descuento' => 0
                ]
            ]);
        }
    }

    // DETALLE (solo lectura)
    public function detalle($id = null) {
        if (!$id) {
            header('Location: ' . URL . 'pedido/ver');
            exit;
        }

        $pedidoModel = $this->model('Pedido');
        $pedido = $pedidoModel->obtenerPedidoPorId($id);

        if (!$pedido) {
            header('Location: ' . URL . 'pedido/ver');
            exit;
        }

        $this->view('pedido/detalle', [
            'pedido' => $pedido,
            'estados' => $this->estadosPermitidos
        ]);
    }

    // ACTUALIZAR SOLO ESTADO (desde listado)
    public function actualizarEstado() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPedido = $_POST['pedido_id'] ?? null;
            $estado = $_POST['estado'] ?? null;

            if ($idPedido && $estado && in_array($estado, $this->estadosPermitidos, true)) {
                $pedidoModel = $this->model('Pedido');
                $pedidoModel->actualizarEstado($idPedido, $estado);
            }
        }

        header('Location: ' . URL . 'pedido/ver');
        exit;
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

    // ====== NUEVO: EDITAR (GET) ======
    public function editar($id = null) {
        if (!$id) {
            header('Location: ' . URL . 'pedido/ver');
            exit;
        }

        $pedidoModel   = $this->model('Pedido');
        $clienteModel  = $this->model('Cliente');
        $empresaModel  = $this->model('Empresa');
        $productoModel = $this->model('Producto');

        // Encabezado + detalles del pedido
        $pedido = $pedidoModel->obtenerPedidoPorId($id);
        if (!$pedido) {
            header('Location: ' . URL . 'pedido/ver');
            exit;
        }

        // Catálogos para los selects
        $empresas  = $empresaModel->ver();
        $idEmpresa = (int)($pedido['ID_emp'] ?? 0); // ajusta si tu SP no retorna ID_emp
        $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
        $productos = $productoModel->ver();

        $this->view('pedido/editar', [
            'pedido'    => $pedido,             // debe incluir 'detalles' como array
            'empresas'  => $empresas,
            'clientes'  => $clientes,
            'productos' => $productos,
            'estados'   => $this->estadosPermitidos,
            'errores'   => []
        ]);
    }

    // ====== NUEVO: ACTUALIZAR (POST) ======
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . 'pedido/ver');
            exit;
        }

        $errores       = [];
        $pedidoModel   = $this->model('Pedido');
        $clienteModel  = $this->model('Cliente');
        $empresaModel  = $this->model('Empresa');
        $productoModel = $this->model('Producto');

        // --- Encabezado recibido
        $idPedido  = (int)($_POST['ID_ped'] ?? 0);
        $idEmpresa = (int)($_POST['ID_emp'] ?? 0);
        $idCliente = (int)($_POST['ID_cli'] ?? 0);
        $fecha     = $_POST['fecha'] ?? ($_POST['Fecha_ped'] ?? date('Y-m-d'));
        $estado    = $_POST['estado'] ?? ($_POST['Estado'] ?? 'pendiente');
        $descuento = isset($_POST['descuento']) ? (float)$_POST['descuento'] : (float)($_POST['Descuento'] ?? 0);

        // --- Detalle recibido
        $productosSeleccionados = $_POST['producto_id'] ?? $_POST['prod_id'] ?? [];
        $cantidades             = $_POST['cantidad'] ?? [];
        $precios                = $_POST['precio'] ?? [];

        // Validaciones
        if ($idPedido <= 0)  $errores[] = 'ID de pedido inválido.';
        if ($idEmpresa <= 0) $errores[] = 'Debe seleccionar una empresa.';
        if ($idCliente <= 0) $errores[] = 'Debe seleccionar un cliente.';
        if (!in_array($estado, $this->estadosPermitidos, true)) $errores[] = 'El estado seleccionado no es válido.';
        if ($descuento < 0)  $errores[] = 'El descuento no puede ser negativo.';

        if ($idEmpresa && $idCliente) {
            $clienteSeleccionado = $clienteModel->obtenerClientePorId($idCliente);
            if (!$clienteSeleccionado || (int)($clienteSeleccionado['ID_emp'] ?? 0) !== $idEmpresa) {
                $errores[] = 'El cliente seleccionado no pertenece a la empresa elegida.';
            }
        }

        // Construir detalle
        $detalles = [];
        $subtotal = 0;
        $n = min(count($productosSeleccionados), count($cantidades));
        for ($i = 0; $i < $n; $i++) {
            $idPro  = (int)($productosSeleccionados[$i] ?? 0);
            $cant   = (float)($cantidades[$i] ?? 0);
            $precio = (float)($precios[$i] ?? 0);
            if ($idPro > 0 && $cant > 0) {
                $detalles[] = ['ID_pro' => $idPro, 'Cantidad' => $cant, 'Precio' => $precio];
                $subtotal  += $cant * $precio;
            }
        }
        if (empty($detalles)) $errores[] = 'Debe agregar al menos un producto al pedido.';

        $total = max($subtotal - $descuento, 0);

        if (!empty($errores)) {
            // Recargar combos para re-render
            $empresas  = $empresaModel->ver();
            $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
            $productos = $productoModel->ver();

            // Pedido original (para conservar datos no editables si los hubiera)
            $pedidoOriginal = $pedidoModel->obtenerPedidoPorId($idPedido) ?: [];

            // Mezclar para que la vista muestre lo recién enviado
            $pedidoView = array_merge($pedidoOriginal, [
                'ID_ped'    => $idPedido,
                'ID_emp'    => $idEmpresa,
                'ID_cli'    => $idCliente,
                'Fecha_ped' => $fecha,
                'Estado'    => $estado,
                'Descuento' => $descuento,
                'Total_ped' => $total,
                'detalles'  => array_map(function($i){
                    return [
                        'ID_pro'             => $i['ID_pro'],
                        'Cantidad_det'       => $i['Cantidad'],
                        'PrecioUnitario_det' => $i['Precio']
                    ];
                }, $detalles)
            ]);

            $this->view('pedido/editar', [
                'pedido'    => $pedidoView,
                'empresas'  => $empresas,
                'clientes'  => $clientes,
                'productos' => $productos,
                'estados'   => $this->estadosPermitidos,
                'errores'   => $errores
            ]);
            return;
        }

        // Datos para modelo (encabezado + detalle)
        $datosPedido = [
            'ID_ped'    => $idPedido,
            'ID_emp'    => $idEmpresa,
            'ID_cli'    => $idCliente,
            'ID_us'     => $_SESSION['usuario']['ID_us'] ?? null,
            'Fecha_ped' => $fecha,
            'Descuento' => $descuento,
            'Total_ped' => $total,
            'Estado'    => $estado
        ];

        // Llamada al modelo transaccional (ajusta el nombre si lo tienes diferente)
        $ok = false;
        if (method_exists($pedidoModel, 'actualizarPedidoCompleto')) {
            $ok = $pedidoModel->actualizarPedidoCompleto($datosPedido, $detalles);
        } else if (method_exists($pedidoModel, 'actualizar')) {
            $ok = $pedidoModel->actualizar($datosPedido, $detalles);
        }

        if ($ok) {
            header('Location: ' . URL . 'pedido/editar/' . $idPedido);
            exit;
        }

        // Si falla, recargar la vista con error
        $empresas  = $empresaModel->ver();
        $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
        $productos = $productoModel->ver();

        $this->view('pedido/editar', [
            'pedido'    => array_merge($datosPedido, [
                'detalles' => array_map(function($i){
                    return [
                        'ID_pro'             => $i['ID_pro'],
                        'Cantidad_det'       => $i['Cantidad'],
                        'PrecioUnitario_det' => $i['Precio']
                    ];
                }, $detalles)
            ]),
            'empresas'  => $empresas,
            'clientes'  => $clientes,
            'productos' => $productos,
            'estados'   => $this->estadosPermitidos,
            'errores'   => ['Ocurrió un error al actualizar el pedido.']
        ]);
    }
}
