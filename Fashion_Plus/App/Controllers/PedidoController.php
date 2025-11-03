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
            $id        = (int)($_POST['ID_ped'] ?? 0);
            $idEmpresa = $_POST['ID_emp'] ?? '';
            $idCliente = $_POST['ID_cli'] ?? '';
            $fecha     = $_POST['fecha'] ?? date('Y-m-d');
            $estado    = $_POST['estado'] ?? 'pendiente';
            $descuento = isset($_POST['descuento']) ? (float)$_POST['descuento'] : 0;

            $productosSeleccionados = $_POST['producto_id'] ?? [];
            $cantidades             = $_POST['cantidad'] ?? [];
            $precios                = $_POST['precio'] ?? [];

            // ---------- Detalle ----------
            $detalles = [];
            $subtotal = 0.0;
            for ($i = 0; $i < count($productosSeleccionados); $i++) {
                $idPro  = (int)$productosSeleccionados[$i];
                $cant   = (float)($cantidades[$i] ?? 0);
                $precio = (float)($precios[$i] ?? 0);
                if ($idPro > 0 && $cant > 0) {
                    $detalles[] = ['ID_pro' => $idPro, 'cantidad' => $cant, 'precio' => $precio];
                    $subtotal  += $cant * $precio;
                }
            }
            $total = max($subtotal - $descuento, 0);

            // ---------- Abonos enviados desde la vista (nuevos + existentes) del POST ----------
            $fechasAbono = $_POST['fecha_abono'] ?? [];
            $montosAbono = $_POST['monto_abono'] ?? [];
            $abonosPost  = [];
            $nAb         = min(count($fechasAbono), count($montosAbono));
            for ($i = 0; $i < $nAb; $i++) {
                $monto = (float)$montosAbono[$i];
                $f     = $fechasAbono[$i] ?: $fecha;
                if ($monto > 0) {
                    // normaliza a 2 decimales para comparar
                    $abonosPost[] = [
                        'fecha' => substr($f, 0, 10),
                        'monto' => (float)number_format($monto, 2, '.', '')
                    ];
                }
            }

            $estado = strtolower(trim($estado));
            if (!in_array($estado, $this->estadosPermitidos, true)) {
                $estado = 'pendiente';
            }

            // CALCULOS Y REGLAS 
            $totalAbonosPost = array_sum(array_column($abonosPost, 'monto'));
            $saldo = round($total - $totalAbonosPost, 2);

            // REGLA 1: no permitir que abonos superen el total
            if ($totalAbonosPost > $total) {
                $pedido    = $pedidoModel->obtenerPedidoPorId($id);
                $empresas  = $empresaModel->ver();
                $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa((int)$idEmpresa) : [];
                $productos = $productoModel->ver();

                if (method_exists($pedidoModel, 'obtenerAbonos')) {
                    $pedido['abonos'] = $pedidoModel->obtenerAbonos((int)$id);
                }

                $this->view('pedido/editar', [
                    'pedido'   => $pedido,
                    'empresas' => $empresas,
                    'clientes' => $clientes,
                    'productos'=> $productos,
                    'estados'  => $this->estadosPermitidos,
                    'errores'  => ['La suma de abonos no puede exceder el total del pedido.']
                ]);
                return;
            }

            // REGLA: para pasar a "en proceso" se requiere >= 50% abonado */
            if ($estado === 'en proceso' && $totalAbonosPost < ($total * 0.5)) {
                $pedido    = $pedidoModel->obtenerPedidoPorId($id);
                $empresas  = $empresaModel->ver();
                $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa((int)$idEmpresa) : [];
                $productos = $productoModel->ver();

                if (method_exists($pedidoModel, 'obtenerAbonos')) {
                    $pedido['abonos'] = $pedidoModel->obtenerAbonos((int)$id);
                }

                $this->view('pedido/editar', [
                    'pedido'    => $pedido,
                    'empresas'  => $empresas,
                    'clientes'  => $clientes,
                    'productos' => $productos,
                    'estados'   => $this->estadosPermitidos,
                    'errores'   => ['Para marcar el pedido como "En proceso", debe estar abonado al menos el 50% (abonado: Q' . number_format($totalAbonosPost, 2) . ' de Q' . number_format($total, 2) . ').']
                ]);
                return;
            }

            // REGLA 3: si quiere marcar COMPLETADO, el saldo debe ser 0
            if ($estado === 'completado' && $saldo > 0) {
                $pedido    = $pedidoModel->obtenerPedidoPorId($id);
                $empresas  = $empresaModel->ver();
                $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa((int)$idEmpresa) : [];
                $productos = $productoModel->ver();

                if (method_exists($pedidoModel, 'obtenerAbonos')) {
                    $pedido['abonos'] = $pedidoModel->obtenerAbonos((int)$id);
                }

                $this->view('pedido/editar', [
                    'pedido'    => $pedido,
                    'empresas'  => $empresas,
                    'clientes'  => $clientes,
                    'productos' => $productos,
                    'estados'   => $this->estadosPermitidos,
                    'errores'   => ['Para marcar el pedido como "Completado", el saldo debe ser Q0.00 (saldo actual: Q' . number_format($saldo, 2) . ').']
                ]);
                return;
            }

            // ---------- Guarda encabezado + detalle ----------
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

                // ---------- Sincroniza abonos (diff entre POST y BD) ----------
                if (method_exists($pedidoModel, 'obtenerAbonos') &&
                    method_exists($pedidoModel, 'registrarAbono') &&
                    method_exists($pedidoModel, 'eliminarAbono')) {

                    // Abonos en BD (con IDs)
                    $abonosDB = $pedidoModel->obtenerAbonos((int)$id) ?: [];

                    $idxDB = []; 
                    foreach ($abonosDB as $ab) {
                        $f = substr($ab['Fecha_abono'] ?? '', 0, 10);
                        $m = (float)number_format((float)($ab['Monto_abono'] ?? 0), 2, '.', '');
                        $k = $f . '|' . $m;
                        $idxDB[$k] = $idxDB[$k] ?? [];
                        $idxDB[$k][] = (int)$ab['ID_abono'];
                    }

                    // Conteo de POST por clave
                    $countPost = []; // clave => cantidad
                    foreach ($abonosPost as $ab) {
                        $k = $ab['fecha'] . '|' . number_format($ab['monto'], 2, '.', '');
                        $countPost[$k] = ($countPost[$k] ?? 0) + 1;
                    }

                    // Conteo de BD por clave
                    $countDB = [];
                    foreach ($idxDB as $k => $ids) {
                        $countDB[$k] = count($ids);
                    }

                    // Inserciones: si POST tiene más ocurrencias que BD
                    foreach ($countPost as $k => $cPost) {
                        $cDB = $countDB[$k] ?? 0;
                        $faltan = $cPost - $cDB;
                        if ($faltan > 0) {
                            // descompone la clave
                            [$f,$m] = explode('|', $k, 2);
                            $montoF = (float)$m;
                            for ($i = 0; $i < $faltan; $i++) {
                                // valida no exceder total al insertar (por seguridad)
                                // (recalcula saldo dinámico, aunque ya validamos antes)
                                $pedidoModel->registrarAbono($id, $f, $montoF);
                            }
                        }
                    }

                    // Eliminaciones: si BD tiene más ocurrencias que POST
                    foreach ($countDB as $k => $cDB) {
                        $cPost = $countPost[$k] ?? 0;
                        $sobran = $cDB - $cPost;
                        if ($sobran > 0) {
                            // elimina tantos IDs como sobren
                            $ids = $idxDB[$k];
                            for ($i = 0; $i < $sobran && !empty($ids); $i++) {
                                $idAbono = array_pop($ids);
                                $pedidoModel->eliminarAbono((int)$idAbono);
                            }
                        }
                    }
                }

                header('Location: ' . URL . 'pedido/ver');
                exit;

            } catch (Exception $e) {
                // Rehidrata datos para la vista de edición con error
                $pedido    = $pedidoModel->obtenerPedidoPorId($id);
                $empresas  = $empresaModel->ver();
                $idEmpSel  = (int)($pedido['ID_emp'] ?? 0);
                $clientes  = $idEmpSel ? $clienteModel->obtenerClientesPorEmpresa($idEmpSel) : [];
                $productos = $productoModel->ver();

                if (method_exists($pedidoModel, 'obtenerAbonos')) {
                    $pedido['abonos'] = $pedidoModel->obtenerAbonos((int)$id);
                }

                $this->view('pedido/editar', [
                    'pedido'   => $pedido,
                    'empresas' => $empresas,
                    'clientes' => $clientes,
                    'productos'=> $productos,
                    'estados'  => $this->estadosPermitidos,
                    'errores'  => ['Error al editar pedido: ' . $e->getMessage()]
                ]);
            }

        } else {
            if (!$id) { header('Location: ' . URL . 'pedido/ver'); exit; }

            $pedido    = $pedidoModel->obtenerPedidoPorId($id);
            $empresas  = $empresaModel->ver();
            $idEmpresa = (int)($pedido['ID_emp'] ?? 0);
            $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
            $productos = $productoModel->ver();

            // 🔹 Asegura abonos en GET
            if (method_exists($pedidoModel, 'obtenerAbonos')) {
                $pedido['abonos'] = $pedidoModel->obtenerAbonos((int)$id);
            }

            $this->view('pedido/editar', [
                'pedido'    => $pedido,
                'empresas'  => $empresas,
                'clientes'  => $clientes,
                'productos' => $productos,
                'estados'   => $this->estadosPermitidos
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
                header('Location: ' . URL . 'pedido/editar/' . $idPedido);
                exit;
            } else {
                $pedido    = $pedidoModel->obtenerPedidoPorId($idPedido);
                $empresas  = $empresaModel->ver();
                $idEmpresa = (int)($pedido['ID_emp'] ?? 0);
                $clientes  = $idEmpresa ? $clienteModel->obtenerClientesPorEmpresa($idEmpresa) : [];
                $productos = $productoModel->ver();

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
