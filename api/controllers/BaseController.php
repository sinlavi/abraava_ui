<?php

class BaseController {
    protected $db;

    public function __construct() {
        require_once __DIR__ . '/../core/Database.php';
        $this->db = Database::getInstance();
    }

    protected function respond($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Quality');
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function getParams() {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'GET') {
            return $_GET;
        }
        return json_decode(file_get_contents('php://input'), true) ?: $_POST;
    }

    protected function checkAuth() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->respond(['error' => 'Unauthorized'], 401);
        }
    }

    protected function checkAdmin() {
        session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->respond(['error' => 'Unauthorized'], 403);
        }
    }
}
