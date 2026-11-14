<?php
class Error404Controller extends Controller {
    public function index() {
        $data['judul'] = '404 Page Not Found';
        
        $this->view('templates/header', $data);
        $this->view('errors/404', $data);
        $this->view('templates/footer', $data);
    }
}
