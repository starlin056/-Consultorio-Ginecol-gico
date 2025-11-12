<?php
// app/controllers/Controller.php
class Controller {
    protected function render($view, $data = []) {
        extract($data);
        require_once "app/views/$view.php";
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }

    protected function isLoggedIn() {
        return isset($_SESSION['usuario']);
    }

    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    protected function hasPermission($requiredRole) {
        if (!$this->isLoggedIn() || $_SESSION['usuario']['rol'] != $requiredRole) {
            return false;
        }
        return true;
    }
}
?>