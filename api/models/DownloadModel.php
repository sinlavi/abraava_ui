<?php
require_once __DIR__ . '/../core/BaseModel.php';

class DownloadModel extends BaseModel {
    public function addToQueue($trackId, $quality) {
        return $this->query("INSERT INTO download_queue (trackId, status, quality) VALUES (:tid, 'pending', :qual)", [
            ':tid' => $trackId,
            ':qual' => $quality
        ]);
    }

    public function getQueue($limit = 500) {
        $res = $this->query("SELECT * FROM download_queue ORDER BY addedAt DESC LIMIT :limit", [':limit' => $limit]);
        $items = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $items[] = array_merge($row, [
                'download_id' => $row['id'],
                'download_status' => $row['status']
            ]);
        }
        return $items;
    }

    public function updateStatus($ids, $status) {
        if (empty($ids)) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE download_queue SET status = ? WHERE id IN ($placeholders)";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(1, $status);
        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 2, $id, SQLITE3_INTEGER);
        }
        $stmt->execute();
        return count($ids);
    }

    public function delete($ids) {
        if (empty($ids)) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM download_queue WHERE id IN ($placeholders)";

        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 1, $id, SQLITE3_INTEGER);
        }
        $stmt->execute();
        return count($ids);
    }

    public function deleteByStatus($status) {
        return $this->query("DELETE FROM download_queue WHERE status = :status", [':status' => $status]);
    }
}
