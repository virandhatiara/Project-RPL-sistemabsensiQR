<?php
class App {
    protected $controller = 'Auth';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseURL();

        // Check if controller exists
        if (isset($url[0])) {
            // Convert hyphenated string to CamelCase (e.g. kepala-sekolah -> KepalaSekolah)
            $controllerName = str_replace(' ', '', ucwords(str_replace('-', ' ', $url[0])));
            if (file_exists('../controllers/' . $controllerName . 'Controller.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                $this->controller = 'Error404';
                unset($url[0]);
            }
        }

        require_once '../controllers/' . $this->controller . 'Controller.php';
        $controllerClass = $this->controller . 'Controller';
        $this->controller = new $controllerClass;

        // Check method
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // Params
        if (!empty($url)) {
            $this->params = array_values($url);
        }

        // Run controller and method, and send params
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseURL() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
