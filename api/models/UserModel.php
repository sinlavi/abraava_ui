<?php
require_once __DIR__ . '/../core/BaseModel.php';

class UserModel extends BaseModel {
    public function create($username, $password, $role = 'user') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $this->query("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)", [
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);
    }

    public function findByUsername($username) {
        $res = $this->query("SELECT * FROM users WHERE username = :username", [':username' => $username]);
        return $res->fetchArray(SQLITE3_ASSOC);
    }

    public function findById($id) {
        $res = $this->query("SELECT * FROM users WHERE id = :id", [':id' => $id]);
        return $res->fetchArray(SQLITE3_ASSOC);
    }

    public function getAll() {
        $res = $this->query("SELECT id, username, role, created_at FROM users");
        $users = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $users[] = $row;
        }
        return $users;
    }

    public function delete($id) {
        return $this->query("DELETE FROM users WHERE id = :id", [':id' => $id]);
    }
}
