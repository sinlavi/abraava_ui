<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/DownloadModel.php';

class DownloadController extends BaseController {
    private $downloadModel;

    public function __construct() {
        parent::__construct();
        $this->downloadModel = new DownloadModel();
    }

    public function add() {
        $this->checkAuth();
        $params = $this->getParams();
        $trackIds = [];

        if (!empty($params['trackId'])) {
            $trackIds = is_array($params['trackId']) ? $params['trackId'] : explode(',', $params['trackId']);
        }

        if (empty($trackIds)) {
            $this->respond(['error' => 'No tracks provided'], 400);
        }

        $quality = $params['quality'] ?? '192';
        $addedCount = 0;

        foreach ($trackIds as $tid) {
            $this->downloadModel->addToQueue($tid, $quality);
            $addedCount++;
        }

        $this->respond(['success' => true, 'added_count' => $addedCount]);
    }

    public function queue() {
        $this->checkAuth();
        $params = $this->getParams();
        $limit = $params['limit'] ?? 500;
        $items = $this->downloadModel->getQueue($limit);
        $this->respond(['items' => $items]);
    }

    public function update() {
        $this->checkAuth();
        $params = $this->getParams();
        $ids = is_array($params['id']) ? $params['id'] : explode(',', $params['id']);
        $status = $params['status'] ?? 'pending';

        $updatedCount = $this->downloadModel->updateStatus($ids, $status);
        $this->respond(['success' => true, 'updated_count' => $updatedCount]);
    }

    public function delete() {
        $this->checkAuth();
        $params = $this->getParams();

        if (isset($params['status'])) {
            $this->downloadModel->deleteByStatus($params['status']);
            $this->respond(['success' => true]);
        }

        $ids = is_array($params['id'] ?? null) ? $params['id'] : (isset($params['id']) ? explode(',', $params['id']) : []);
        if (empty($ids)) {
             $this->respond(['error' => 'No IDs provided'], 400);
        }

        $deletedCount = $this->downloadModel->delete($ids);
        $this->respond(['success' => true, 'deleted_count' => $deletedCount]);
    }
}
