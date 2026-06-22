<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/UserModel.php';

class UserController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function list() {
        $this->checkAdmin();
        $users = $this->userModel->getAllUsers();
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

        try {
            $this->userModel->create($username, $password, $role);
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

        // Prevent deleting oneself
        if ($id == $_SESSION['user_id']) {
            $this->respond(['error' => 'You cannot delete your own account'], 400);
        }

        $this->userModel->delete($id);
        $this->respond(['success' => true]);
    }
}
