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
                'nombre' => $_POST['nombre'],
                'apellido' => $_POST['apellido'],
                'telefono' => $_POST['telefono'],
                'correo' => $_POST['correo'],
                'direccion' => $_POST['direccion']
            ];

            $clienteModel = $this->model('Cliente');
            $exito = $clienteModel->insertar($datos);

            if ($exito) {
                header('Location: ' . URL . 'cliente/ver');
                exit;
            } else {
                $this->view('cliente/registrar', ['error' => 'Error al registrar cliente']);
            }
        } else {
            $this->view('cliente/registrar');
        }
    }

    public function editar($id) {
        $clienteModel = $this->model('Cliente');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre' => $_POST['nombre'],
                'apellido' => $_POST['apellido'],
                'telefono' => $_POST['telefono'],
                'correo' => $_POST['correo'],
                'direccion' => $_POST['direccion']
            ];

            $clienteModel->editar($id, $datos);
            header('Location: ' . URL . 'cliente/ver');
            exit;
        } else {
            $cliente = $clienteModel->obtenerClientePorId($id);
            $this->view('cliente/editar', ['cliente' => $cliente]);
        }
    }
}
