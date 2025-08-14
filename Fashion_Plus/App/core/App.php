<?php
class App {
    protected $controlador = 'HomeController';  // controlador por defecto
    protected $metodo = 'index';                // método por defecto
    protected $parametros = [];

    public function __construct() {
        $url = $this->getUrl(); // obtiene la URL descompuesta

        // Si existe un archivo de controlador con el nombre de la URL
        if (isset($url[0]) && file_exists('../app/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controlador = ucfirst($url[0]) . 'Controller'; // ejemplo: clientes → ClientesController
            unset($url[0]); // quita esa parte
        }

        require_once '../app/controllers/' . $this->controlador . '.php';
        $this->controlador = new $this->controlador;

        // Si hay un método especificado y existe
        if (isset($url[1]) && method_exists($this->controlador, $url[1])) {
            $this->metodo = $url[1];
            unset($url[1]);
        }

        // Parámetros que vienen después del método
        $this->parametros = $url ? array_values($url) : [];

        // Ejecuta: $controlador->$metodo($parametros)
        call_user_func_array([$this->controlador, $this->metodo], $this->parametros);
    }

    private function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');               // elimina barra final
            $url = filter_var($url, FILTER_SANITIZE_URL);  // limpia la URL
            return explode('/', $url);                     // separa por /
        }
    }
}
