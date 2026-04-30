<?php

class StaffController extends Controller {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('/');
        }
    }

    public function index() {
        $db = getDB();
        $staff = $db->query("SELECT id, username, fullname, status, created_at FROM users WHERE role = 'staff' ORDER BY created_at DESC")->fetchAll();
        $this->view('admin/staff', ['staff' => $staff]);
    }

    public function add() {
        $db = getDB();
        
        try {
            $username = trim($_POST['username'] ?? '');
            $fullname = trim($_POST['fullname'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            if (empty($username)) {
                throw new Exception('Username is required.');
            }
            if (strlen($username) < 3) {
                throw new Exception('Username must be at least 3 characters.');
            }
            if (empty($fullname)) {
                throw new Exception('Full name is required.');
            }
            if (empty($password)) {
                throw new Exception('Password is required.');
            }
            if (strlen($password) < 5) {
                throw new Exception('Password must be at least 5 characters.');
            }
            if ($password !== $passwordConfirm) {
                throw new Exception('Passwords do not match.');
            }

            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                throw new Exception('Username already exists.');
            }

            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO users (username, fullname, password, role, status) VALUES (?, ?, ?, 'staff', 'active')");
            $stmt->execute([$username, $fullname, password_hash($password, PASSWORD_DEFAULT)]);
            $newId = $db->lastInsertId();

            $db->commit();

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($isAjax) {
                $this->json(['success' => true, 'message' => 'Staff account created successfully!', 'id' => $newId, 'username' => $username, 'fullname' => $fullname]);
            } else {
                $this->redirect('/staff');
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()]);
            } else {
                die('Error: ' . $e->getMessage());
            }
        }
    }

    public function toggleStatus() {
        $db = getDB();
        
        try {
            $id = $_POST['id'] ?? null;

            if (!$id) {
                throw new Exception('Staff ID is required.');
            }

            if ($id == $_SESSION['user_id']) {
                throw new Exception('You cannot disable your own account.');
            }

            $stmt = $db->prepare("SELECT status FROM users WHERE id = ? AND role = 'staff'");
            $stmt->execute([$id]);
            $staff = $stmt->fetch();

            if (!$staff) {
                throw new Exception('Staff member not found.');
            }

            $newStatus = $staff['status'] === 'active' ? 'disabled' : 'active';

            $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $id]);

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($isAjax) {
                $this->json(['success' => true, 'message' => 'Staff status updated.', 'new_status' => $newStatus]);
            } else {
                $this->redirect('/staff');
            }
        } catch (Exception $e) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()]);
            } else {
                die('Error: ' . $e->getMessage());
            }
        }
    }

    public function delete() {
        $db = getDB();
        
        try {
            $id = $_POST['id'] ?? null;

            if (!$id) {
                throw new Exception('Staff ID is required.');
            }

            if ($id == $_SESSION['user_id']) {
                throw new Exception('You cannot delete your own account.');
            }

            $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if (!$user || $user['role'] !== 'staff') {
                throw new Exception('Staff member not found.');
            }

            $db->beginTransaction();
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $db->commit();

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($isAjax) {
                $this->json(['success' => true, 'message' => 'Staff account deleted.']);
            } else {
                $this->redirect('/staff');
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
            if ($isAjax) {
                $this->json(['success' => false, 'message' => $e->getMessage()]);
            } else {
                die('Error: ' . $e->getMessage());
            }
        }
    }
}
