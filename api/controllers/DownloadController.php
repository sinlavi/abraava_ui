<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Download.php';

class DownloadController extends BaseController {
    private $downloadModel;
    private const ITUNES_LOOKUP_API = 'https://itunes.apple.com/lookup';

    public function __construct() {
        parent::__construct();
        $this->downloadModel = new Download();
    }

    public function add() {
        $this->checkAuth();
        $params = $this->getParams();
        $quality = $params['quality'] ?? '192';
        $addedCount = 0;
        $skippedCount = 0;

        $trackIds = [];

        // Single track
        if (!empty($params['trackId'])) {
            $ids = is_array($params['trackId']) ? $params['trackId'] : explode(',', $params['trackId']);
            $trackIds = array_merge($trackIds, $ids);
        }

        // Expand Album
        if (!empty($params['albumId'])) {
            $albumIds = is_array($params['albumId']) ? $params['albumId'] : explode(',', $params['albumId']);
            foreach ($albumIds as $aid) {
                $tracks = $this->fetchChildTracks($aid, 'album');
                $trackIds = array_merge($trackIds, $tracks);
            }
        }

        // Expand Artist
        if (!empty($params['artistId'])) {
            $artistIds = is_array($params['artistId']) ? $params['artistId'] : explode(',', $params['artistId']);
            foreach ($artistIds as $aid) {
                $tracks = $this->fetchChildTracks($aid, 'artist');
                $trackIds = array_merge($trackIds, $tracks);
            }
        }

        $trackIds = array_unique($trackIds);

        foreach ($trackIds as $tid) {
            try {
                $this->downloadModel->addToQueue($tid, $quality);
                $addedCount++;
            } catch (Exception $e) {
                $skippedCount++;
            }
        }

        $this->respond([
            'success' => true,
            'added_count' => $addedCount,
            'skipped_count' => $skippedCount
        ]);
    }

    public function queue() {
        $this->checkAuth();
        $items = $this->downloadModel->getQueue();
        foreach ($items as &$item) {
            $item['download_id'] = $item['id'];
            $item['download_status'] = $item['status'];
        }
        $this->respond(['items' => $items]);
    }

    public function update() {
        $this->checkAuth();
        $params = $this->getParams();
        $ids = is_array($params['id']) ? $params['id'] : explode(',', $params['id']);
        $status = $params['status'] ?? 'pending';

        $this->downloadModel->updateStatus($ids, $status);
        $this->respond(['success' => true, 'updated_count' => count($ids)]);
    }

    public function delete() {
        $this->checkAuth();
        $params = $this->getParams();
        if (!empty($params['status'])) {
            $this->downloadModel->deleteByStatus($params['status']);
            $this->respond(['success' => true]);
        } else {
            $ids = is_array($params['id']) ? $params['id'] : explode(',', $params['id']);
            $this->downloadModel->delete($ids);
            $this->respond(['success' => true, 'deleted_count' => count($ids)]);
        }
    }

    private function fetchChildTracks($id, $type) {
        $entity = ($type === 'artist') ? 'song' : 'song'; // In both cases we want songs
        $url = self::ITUNES_LOOKUP_API . "?id=$id&entity=$entity&limit=200";
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        $trackIds = [];
        if (!empty($data['results'])) {
            foreach ($data['results'] as $res) {
                if (($res['wrapperType'] ?? '') === 'track') {
                    $trackIds[] = $res['trackId'];
                }
            }
        }
        return $trackIds;
    }
}
