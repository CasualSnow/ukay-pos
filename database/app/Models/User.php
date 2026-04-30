<?php

class User extends Model {
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function updateTheme($userId, $theme) {
        $stmt = $this->db->prepare("UPDATE users SET theme = ? WHERE id = ?");
        $stmt->execute([$theme, $userId]);
    }
}
