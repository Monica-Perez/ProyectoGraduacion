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

    public function ver() {
        $pedidoModel = $this->model('Pedido');
        $pedidos = $pedidoModel->ver();

        $this->view('pedido/ver', [
            'pedidos' => $pedidos,
            'estados' => $this->estadosPermitidos
        ]);
    }

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

            if (!$idEmpresa) {
                $errores[] = 'Debe seleccionar una empresa.';
            }

            if (!$idCliente) {
                $errores[] = 'Debe seleccionar un cliente.';
            }

            if (!in_array($estado, $this->estadosPermitidos, true)) {
                $errores[] = 'El estado seleccionado no es válido.';
            }

            if ($descuento < 0) {
                $errores[] = 'El descuento no puede ser negativo.';
            }

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
}
