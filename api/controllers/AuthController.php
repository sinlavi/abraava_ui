<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function signup() {
        $params = $this->getParams();
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->respond(['error' => 'Username and password are required'], 400);
        }

        try {
            $this->userModel->create($username, $password);
            $this->respond(['success' => true, 'message' => 'User created successfully']);
        } catch (Exception $e) {
            $this->respond(['error' => 'Username already exists'], 400);
        }
    }

    public function login() {
        $params = $this->getParams();
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';

        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $this->respond([
                'success' => true,
                'user' => [
                    'username' => $user['username'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            $this->respond(['error' => 'Invalid credentials'], 401);
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        $this->respond(['success' => true]);
    }

    public function status() {
        session_start();
        if (isset($_SESSION['user_id'])) {
            $this->respond([
                'logged_in' => true,
                'user' => [
                    'username' => $_SESSION['username'],
                    'role' => $_SESSION['role']
                ]
            ]);
        } else {
            $this->respond(['logged_in' => false]);
        }
    }
}
