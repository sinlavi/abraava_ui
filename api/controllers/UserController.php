<?php
require_once __DIR__ . '/BaseController.php';

class UserController extends BaseController {
    public function list() {
        $this->checkAdmin();
        $res = $this->db->query("SELECT id, username, role, created_at FROM users");
        $users = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $users[] = $row;
        }
        $this->respond(['users' => $users]);
    }

    public function create() {
        $this->checkAdmin();
        $params = $this->getParams();
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';
        $role = $params['role'] ?? 'user';

        if (empty($username) || empty($password)) {
            $this->respond(['error' => 'Username and password are required'], 400);
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $this->db->query("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)", [
                ':username' => $username,
                ':password' => $hashedPassword,
                ':role' => $role
            ]);
            $this->respond(['success' => true, 'message' => 'User created successfully']);
        } catch (Exception $e) {
            $this->respond(['error' => 'Username already exists'], 400);
        }
    }

    public function delete() {
        $this->checkAdmin();
        $params = $this->getParams();
        $id = $params['id'] ?? null;
        if (!$id) $this->respond(['error' => 'Missing ID'], 400);

        // Prevent self-deletion
        if ($id == $_SESSION['user_id']) {
            $this->respond(['error' => 'You cannot delete your own account'], 400);
        }

        $this->db->query("DELETE FROM users WHERE id = :id", [':id' => $id]);
        $this->respond(['success' => true]);
    }

}
