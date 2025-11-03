<?php
class UsuarioController extends Controller {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usuario = $_POST['usuario'] ?? '';
            $pass = $_POST['pass'] ?? '';

            $usuarioModel = $this->model('Usuario');
            $usuario = $usuarioModel->verificar($usuario, $pass);

            if ($usuario) {
                session_start();
                $_SESSION['usuario'] = $usuario;
                header('Location: ' . URL . 'index');
            } else {
                $this->view('usuario/login', ['error' => 'Credenciales incorrectas']);
            }
        } else {
            $this->view('usuario/login');
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: ' . URL . 'usuario/login');
        exit;
    }

    public function registrar() {
        if (!isset($_SESSION)) session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'usuario' => $_POST['usuario'],
                'pass' => password_hash($_POST['pass'], PASSWORD_DEFAULT),
                'rol' => $_POST['rol'],
                'estado' => $_POST['estado']
            ];

            $usuarioModel = $this->model('Usuario');
            $exito = $usuarioModel->insertar($datos);

            if ($exito) {
                header('Location: ' . URL . 'usuario/ver');
            } else {
                $this->view('usuario/registrar', ['error' => 'Error al registrar']);
            }
        } else {
            $this->view('usuario/registrar');
        }
    }

    public function ver() {
        $usuarioModel = $this->model('Usuario');
        $usuarios = $usuarioModel->ver();

        $this->view('usuario/ver', ['usuarios' => $usuarios]);
    }

public function editar($id = null) {
        if (!isset($_SESSION)) session_start();
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL . 'usuario/login');
            exit;
        }

        $usuarioModel = $this->model('Usuario');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $datos = [
                'usuario' => $_POST['usuario'],
                'rol'     => $_POST['rol'],
                'estado'  => $_POST['estado']
            ];

            try {
                $usuarioModel->editar($id, $datos);
                header('Location: ' . URL . 'usuario/ver');
                exit;
            } catch (Exception $e) {
                $this->view('usuario/editar', [
                    'usuario' => $datos,
                    'error' => 'Error al editar el usuario: ' . $e->getMessage()
                ]);
            }
        } else {
            if (!$id) {
                header('Location: ' . URL . 'usuario/ver');
                exit;
            }

            $usuario = $usuarioModel->obtenerUsuarioPorId($id);
            $this->view('usuario/editar', ['usuario' => $usuario]);
        }
    }



}
        