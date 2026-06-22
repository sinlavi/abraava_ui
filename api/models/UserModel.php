<?php
require_once __DIR__ . '/../core/BaseModel.php';

class UserModel extends BaseModel {
    protected $table = 'users';

    public function findByUsername($username) {
        $res = $this->query("SELECT * FROM users WHERE username = :username", [':username' => $username]);
        return $res->fetchArray(SQLITE3_ASSOC);
    }

    public function create($username, $password, $role = 'user') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        return $this->query("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)", [
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);
    }

    public function getAllUsers() {
        $res = $this->query("SELECT id, username, role, created_at FROM users");
        $users = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $users[] = $row;
        }
        return $users;
    }
}
