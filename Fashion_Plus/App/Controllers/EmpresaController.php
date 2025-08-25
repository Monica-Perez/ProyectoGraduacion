<?php
class EmpresaController extends Controller {

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'];
            $nit = $_POST['nit'];
            $contacto = $_POST['contacto'];
            $telefono = $_POST['telefono'];
            $direccion = $_POST['direccion'];
            $correo = $_POST['correo'];

            $empresaModel = $this->model('Empresa');
            $exito = $empresaModel->insertar([
                'nombre' => $nombre,
                'nit' => $nit,
                'contacto' => $contacto,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'correo' => $correo
            ]);

            if ($exito) {
                header('Location: ' . URL . 'empresa/ver');
                exit;
            } else {
                $this->view('empresa/registrar', ['error' => 'Error al registrar empresa.']);
            }
        } else {
            $this->view('empresa/registrar');
        }
    }

    public function ver() {
        $empresaModel = $this->model('Empresa');
        $empresas = $empresaModel->ver();

        $this->view('empresa/ver', ['empresas' => $empresas]);
    }

    public function editar($id = null) {
        if (!isset($_SESSION)) session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }

        $empresaModel = $this->model('Empresa');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $datos = [
                'nombre'    => $_POST['nombre'],
                'nit'       => $_POST['nit'],
                'contacto'  => $_POST['contacto'],
                'telefono'  => $_POST['telefono'],
                'direccion' => $_POST['direccion'],
                'correo'    => $_POST['correo']
            ];

            try {
                $empresaModel->editar($id, $datos);
                header('Location: ' . URL . 'empresa/ver');
                exit;
            } catch (Exception $e) {
                $this->view('empresa/editar', [
                    'empresa' => $datos,
                    'error' => 'Error al editar la empresa: ' . $e->getMessage()
                ]);
            }
        } else {
            if (!$id) {
                header('Location: ' . URL . 'empresa/ver');
                exit;
            }

            $empresa = $empresaModel->obtenerEmpresaPorId($id);
            $this->view('empresa/editar', ['empresa' => $empresa]);
        }
    }
}
