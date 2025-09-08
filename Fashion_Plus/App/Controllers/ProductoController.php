<?php
class ProductoController extends Controller {
    public function __construct() {
        session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }
    }

    public function ver() {
        $productoModel = $this->model('Producto');
        $productos = $productoModel->ver();

        $this->view('producto/ver', ['productos' => $productos]);
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'precio' => $_POST['precio']
            ];

            $productoModel = $this->model('Producto');
            $exito = $productoModel->insertar($datos);

            if ($exito) {
                header('Location: ' . URL . 'producto/ver');
                exit;
            } else {
                $this->view('producto/registrar', ['error' => 'Error al registrar producto']);
            }
        } else {
            $this->view('producto/registrar');
        }
    }

    public function editar($id = null) {
        $productoModel = $this->model('Producto');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $datos = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'precio' => $_POST['precio']
            ];

            try {
                $productoModel->editar($id, $datos);
                header('Location: ' . URL . 'producto/ver');
                exit;
            } catch (Exception $e) {
                $this->view('producto/editar', [
                    'producto' => $datos,
                    'error' => 'Error al editar producto: ' . $e->getMessage()
                ]);
            }
        } else {
            if (!$id) {
                header('Location: ' . URL . 'producto/ver');
                exit;
            }

            $producto = $productoModel->obtenerProductoPorId($id);
            $this->view('producto/editar', ['producto' => $producto]);
        }
    }

    public function eliminar($id = null) {
        if ($id) {
            $productoModel = $this->model('Producto');
            $productoModel->eliminar($id);
        }

        header('Location: ' . URL . 'producto/ver');
        exit;
    }
}
?>
