<?php
class DashboardController extends Controller {
    public function index() {
        // Generic dashboard redirects to the appropriate role dashboard
        if (isset($_SESSION['user_id'])) {
            $roleUrl = str_replace('_', '-', $_SESSION['role']);
            $this->redirect('/' . $roleUrl . '/dashboard');
        } else {
            $this->redirect('/auth');
        }
    }
}
