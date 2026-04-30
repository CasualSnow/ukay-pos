<?php
require_once __DIR__ . '/../Models/User.php';

class AuthController extends Controller {
    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['role'] === 'admin') {
                $this->redirect('/dashboard');
            } else {
                $this->redirect('/pos');
            }
        }
        $this->view('auth/login');
    }

    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $this->view('auth/login', ['error' => 'Account is disabled']);
                return;
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['theme'] = $user['theme'];
            
            if ($user['role'] === 'admin') {
                $this->redirect('/dashboard');
            } else {
                $this->redirect('/pos');
            }
        } else {
            $this->view('auth/login', ['error' => 'Invalid username or password']);
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
}
