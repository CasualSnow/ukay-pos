<?php

class UserController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        $users = $db->query("SELECT * FROM users ORDER BY role ASC, username ASC")->fetchAll();
        $this->view('admin/users', ['users' => $users]);
    }

    public function add() {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['username'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['role']
        ]);
        $this->redirect('/users');
    }

    public function update() {
        $db = getDB();
        if (!empty($_POST['password'])) {
            $stmt = $db->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->execute([
                $_POST['username'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['role'],
                $_POST['id']
            ]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
            $stmt->execute([
                $_POST['username'],
                $_POST['role'],
                $_POST['id']
            ]);
        }
        $this->redirect('/users');
    }

    public function delete() {
        $db = getDB();
        // Prevent deleting self
        if ($_POST['id'] != $_SESSION['user_id']) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$_POST['id']]);
        }
        $this->redirect('/users');
    }

    public function updateTheme() {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Not logged in']);
            return;
        }

        $theme = $_POST['theme'] ?? 'light';
        if (!in_array($theme, ['light', 'dark'])) {
            $theme = 'light';
        }

        $userModel = new User();
        $userModel->updateTheme($_SESSION['user_id'], $theme);
        $_SESSION['theme'] = $theme;

        $this->json(['success' => true]);
    }
}
