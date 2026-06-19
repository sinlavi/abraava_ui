<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/iTunesEntity.php';
require_once __DIR__ . '/../models/User.php';

class DatabaseController extends BaseController {
    private $itunesModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->itunesModel = new iTunesEntity();
        $this->userModel = new User();
    }

    public function stats() {
        $this->checkAdmin();
        $stats = $this->itunesModel->getStats();
        $stats['user_count'] = $this->userModel->count();
        $stats['db_size_bytes'] = filesize(__DIR__ . '/../../itunes.db');
        $this->respond($stats);
    }

    public function manageData() {
        $this->checkAdmin();
        $this->db->query("DELETE FROM requestCache");
        $this->respond(['message' => 'Request cache cleared successfully']);
    }
}
