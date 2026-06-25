<?php
require_once __DIR__ . '/BaseController.php';

class DownloadController extends BaseController {
    public function add() {
        $this->checkAuth();
        $params = $this->getParams();
        $trackIds = [];

        if (!empty($params['trackId'])) {
            $trackIds = is_array($params['trackId']) ? $params['trackId'] : explode(',', $params['trackId']);
        } elseif (!empty($params['albumId'])) {
            $albumId = $params['albumId'];
            $tracks = $this->fetchFromItunes('lookup', ['id' => $albumId, 'entity' => 'song']);
            foreach ($tracks as $t) {
                if (($t['wrapperType'] ?? '') === 'track') $trackIds[] = $t['trackId'];
            }
        } elseif (!empty($params['artistId'])) {
            $artistId = $params['artistId'];
            $albums = $this->fetchFromItunes('lookup', ['id' => $artistId, 'entity' => 'album']);
            foreach ($albums as $alb) {
                if (($alb['wrapperType'] ?? '') === 'collection') {
                    $tracks = $this->fetchFromItunes('lookup', ['id' => $alb['collectionId'], 'entity' => 'song']);
                    foreach ($tracks as $t) {
                        if (($t['wrapperType'] ?? '') === 'track') $trackIds[] = $t['trackId'];
                    }
                }
            }
        }

        if (empty($trackIds)) {
            $this->respond(['error' => 'No tracks found for the provided criteria'], 400);
        }

        $quality = $params['quality'] ?? '192';
        $skipExisting = isset($params['skipExisting']) ? (bool)$params['skipExisting'] : false;
        $addedCount = 0;
        $skippedCount = 0;

        foreach ($trackIds as $tid) {
            if ($skipExisting) {
                $exists = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM download_queue WHERE trackId = '" . $this->db->escapeString($tid) . "' AND status != 'failed'");
                if ($exists > 0) {
                    $skippedCount++;
                    continue;
                }
            }
            $this->db->query("INSERT INTO download_queue (trackId, status, quality) VALUES (:tid, 'pending', :qual)", [
                ':tid' => $tid,
                ':qual' => $quality
            ]);
            $addedCount++;
        }

        $this->respond([
            'success' => true,
            'added_count' => $addedCount,
            'skipped_count' => $skippedCount
        ]);
    }

    private function fetchFromItunes($endpoint, $params) {
        $url = "https://itunes.apple.com/{$endpoint}?" . http_build_query($params);
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
        return $data['results'] ?? [];
    }

    public function queue() {
        $this->checkAuth();
        $res = $this->db->query("SELECT * FROM download_queue ORDER BY addedAt DESC");
        $items = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            // In a real scenario, we'd join with tracks table to get more info
            $items[] = array_merge($row, [
                'download_id' => $row['id'],
                'download_status' => $row['status']
            ]);
        }
        $this->respond(['items' => $items]);
    }

    public function update() {
        $this->checkAuth();
        $params = $this->getParams();
        $ids = is_array($params['id']) ? $params['id'] : explode(',', $params['id']);
        $status = $params['status'] ?? 'pending';

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE download_queue SET status = ? WHERE id IN ($placeholders)";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(1, $status);
        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 2, $id, SQLITE3_INTEGER);
        }
        $stmt->execute();

        $this->respond(['success' => true, 'updated_count' => count($ids)]);
    }

    public function delete() {
        $this->checkAuth();
        $params = $this->getParams();
        $ids = is_array($params['id']) ? $params['id'] : explode(',', $params['id']);

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM download_queue WHERE id IN ($placeholders)";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 1, $id, SQLITE3_INTEGER);
        }
        $stmt->execute();

        $this->respond(['success' => true, 'deleted_count' => count($ids)]);
    }
}
