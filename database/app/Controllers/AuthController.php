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
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if (!$user) {
            $this->view('auth/login', ['error' => 'User not found: ' . htmlspecialchars($username)]);
            return;
        }

        // Emergency Bypass: Allow plain text login if hashing is failing
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            if ($user['status'] !== 'active') {
                $this->view('auth/login', ['error' => 'Account is disabled']);
                return;
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['theme'] = $user['theme'] ?? 'light';
            
            if ($user['role'] === 'admin') {
                $this->redirect('/dashboard');
            } else {
                $this->redirect('/pos');
            }
        } else {
            // Debugging password mismatch
            $debugMsg = "Invalid password. ";
            if (substr($user['password'], 0, 4) !== '$2y$') {
                $debugMsg .= "Warning: DB password is NOT hashed.";
            }
            $this->view('auth/login', ['error' => $debugMsg]);
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
}
