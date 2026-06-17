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

    public function delete() {
        $this->checkAdmin();
        $params = $this->getParams();
        $id = $params['id'] ?? null;
        if (!$id) $this->respond(['error' => 'Missing ID'], 400);

        $this->db->query("DELETE FROM users WHERE id = :id", [':id' => $id]);
        $this->respond(['success' => true]);
    }

}
