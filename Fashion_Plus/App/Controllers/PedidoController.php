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
    public function registrar() {
        $clienteModel  = $this->model('Cliente');
        $empresaModel  = $this->model('Empresa');
        $productoModel = $this->model('Producto');
        $pedidoModel   = $this->model('Pedido');

        $empresas = $empresaModel->ver();
        $productos = $productoModel->ver();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                    $detalles[] = ['ID_pro' => $idPro, 'Cantidad' => $cant, 'Precio' => $precio];
                    $subtotal += $cant * $precio;
                }
            }
            $total = max($subtotal - $descuento, 0);

            $datosPedido = [
                'ID_cli'     => $idCliente,
                'ID_us'      => $_SESSION['usuario']['ID_us'] ?? null,
                'Fecha_ped'  => $fecha,
                'Descuento'  => $descuento,
                'Total_ped'  => $total,
                'Estado'     => $estado
            ];

            try {
                $exito = $pedidoModel->insertar($datosPedido, $detalles);
                if ($exito) {
                    header('Location: ' . URL . 'pedido/ver');
                    exit;
                } else {
                    $clientes = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
                    $this->view('pedido/registrar', [
                        'error' => 'Error al registrar pedido',
                        'empresas' => $empresas,
                        'clientes' => $clientes,
                        'productos' => $productos,
                        'estados' => $this->estadosPermitidos
                    ]);
                }
            } catch (Exception $e) {
                $clientes = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
                $this->view('pedido/registrar', [
                    'error' => 'Error al registrar pedido: ' . $e->getMessage(),
                    'empresas' => $empresas,
                    'clientes' => $clientes,
                    'productos' => $productos,
                    'estados' => $this->estadosPermitidos
                ]);
            }
        } else {
            $this->view('pedido/registrar', [
                'empresas' => $empresas,
                'clientes' => [],
                'productos' => $productos,
                'estados' => $this->estadosPermitidos
            ]);
        }
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
                $clientes = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
                $productos = $productoModel->ver();

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

}
