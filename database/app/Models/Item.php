<?php

class Item extends Model {
    public function getAll($category = null, $search = null) {
        $query = "SELECT * FROM items WHERE is_deleted = 0";
        $params = [];

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        if ($search) {
            $query .= " AND name LIKE ?";
            $params[] = "%$search%";
        }

        $query .= " ORDER BY CASE WHEN category = 'T-Shirts' THEN 0 ELSE 1 END, created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM items WHERE id = ? AND is_deleted = 0");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM items WHERE is_deleted = 0");
        $dbCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $defaultCategories = ['T-Shirts', 'Pants', 'Jackets', 'Shoes', 'Accessories', 'Others'];
        
        return array_unique(array_merge($defaultCategories, $dbCategories));
    }
}
