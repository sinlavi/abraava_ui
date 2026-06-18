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
        $users = $this->userModel->getAll();
        $this->respond(['users' => $users]);
    }

    public function delete() {
        $this->checkAdmin();
        $params = $this->getParams();
        $id = $params['id'] ?? null;
        if (!$id) $this->respond(['error' => 'Missing ID'], 400);

        $this->userModel->delete($id);
        $this->respond(['success' => true]);
    }
}
