<?php
class ClienteController extends Controller {

    public function __construct() {
        session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }
    }

    public function ver() {
        $clienteModel = $this->model('Cliente');
        $clientes = $clienteModel->ver();

        $this->view('cliente/ver', ['clientes' => $clientes]);
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'ID_emp'    => $_POST['ID_emp'],
                'nombre'    => $_POST['nombre'],
                'apellido'  => $_POST['apellido'],
                'telefono'  => $_POST['telefono'],
                'direccion' => $_POST['direccion'],
                'correo'    => $_POST['correo']
            ];

            $clienteModel = $this->model('Cliente');
            $exito = $clienteModel->insertar($datos);

            if ($exito) {
                header('Location: ' . URL . 'cliente/ver');
                exit;
            } else {
                $empresaModel = $this->model('Empresa');
                $empresas = $empresaModel->ver();

                $this->view('cliente/registrar', [
                    'error' => 'Error al registrar cliente',
                    'empresas' => $empresas
                ]);
            }
        } else {
            $empresaModel = $this->model('Empresa');
            $empresas = $empresaModel->ver();

            $this->view('cliente/registrar', ['empresas' => $empresas]);
        }
    }

    public function editar($id = null) {
        $clienteModel = $this->model('Cliente');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $datos = [
                'ID_emp'    => $_POST['ID_emp'],
                'nombre'    => $_POST['nombre'],
                'apellido'  => $_POST['apellido'],
                'telefono'  => $_POST['telefono'],
                'direccion' => $_POST['direccion'],
                'correo'    => $_POST['correo']
            ];

            try {
                $clienteModel->editar($id, $datos);
                header('Location: ' . URL . 'cliente/ver');
                exit;
            } catch (Exception $e) {
                $empresaModel = $this->model('Empresa');
                $empresas = $empresaModel->ver();

                $this->view('cliente/editar', [
                    'cliente' => $datos,
                    'empresas' => $empresas,
                    'error' => 'Error al editar cliente: ' . $e->getMessage()
                ]);
            }
        } else {
            if (!$id) {
                header('Location: ' . URL . 'cliente/ver');
                exit;
            }

            $cliente = $clienteModel->obtenerClientePorId($id);
            $empresaModel = $this->model('Empresa');
            $empresas = $empresaModel->ver();

            $this->view('cliente/editar', [
                'cliente' => $cliente,
                'empresas' => $empresas
            ]);
        }
    }

    public function eliminar($id = null) {
        if (!isset($_SESSION)) session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }

        if ($id) {
            $clienteModel = $this->model('Cliente');
            $clienteModel->eliminar($id);
        }

        header('Location: ' . URL . 'cliente/ver');
        exit;
    }

}
    