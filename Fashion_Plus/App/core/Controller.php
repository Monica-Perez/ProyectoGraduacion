<?php
class Controller {
    public function view($vista, $datos = []) {
        require_once '../app/views/' . $vista . '.php';
    }

    public function model($modelo) {
        require_once '../app/models/' . $modelo . '.php';
        return new $modelo();
    }
}
