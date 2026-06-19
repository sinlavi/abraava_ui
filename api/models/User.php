<?php
require_once __DIR__ . '/../core/BaseModel.php';

class User extends BaseModel {
    public function create($username, $password, $role = 'user') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $this->query("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)", [
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);
    }

    public function findByUsername($username) {
        return $this->fetchOne("SELECT * FROM users WHERE username = :username", [':username' => $username]);
    }

    public function findById($id) {
        return $this->fetchOne("SELECT * FROM users WHERE id = :id", [':id' => $id]);
    }

    public function getAll() {
        return $this->fetchAll("SELECT id, username, role, created_at FROM users");
    }

    public function delete($id) {
        return $this->query("DELETE FROM users WHERE id = :id", [':id' => $id]);
    }

    public function count() {
        return $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM users");
    }
}
