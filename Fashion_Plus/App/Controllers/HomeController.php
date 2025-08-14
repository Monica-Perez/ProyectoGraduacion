<?php
class HomeController extends Controller {
    public function index() {
        $mensaje = "¡Bienvenida al sistema de Fashion Plus!";
        $this->view('home/index', ['mensaje' => $mensaje]);
    }
}
