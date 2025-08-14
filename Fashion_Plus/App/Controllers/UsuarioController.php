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
                header('Location: ' . URL . 'dashboard');
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
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usuario = $_POST['usuario'];
            $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
            $rol = $_POST['rol'];
            $estado = $_POST['estado'];

            $usuarioModel = $this->model('Usuario');
            $exito = $usuarioModel->insertar($usuario, $pass, $rol, $estado);

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

    public function editar($id) {
        $usuarioModel = $this->model('Usuario');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $usuario = $_POST['usuario'];
            $rol = $_POST['rol'];
            $estado = $_POST['estado'];

            $usuarioModel->editar($id, [
                'usuario' => $usuario,
                'rol' => $rol,
                'estado' => $estado
            ]);

            header('Location: ' . URL . 'usuario/ver');
            exit;
        } else {
            $usuario = $usuarioModel->obtenerUsuarioPorId($id);
            $this->view('usuario/editar', ['usuario' => $usuario]);
        }
    }



}
        