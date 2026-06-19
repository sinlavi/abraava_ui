<?php
require_once __DIR__ . '/../core/BaseModel.php';

class Download extends BaseModel {
    public function addToQueue($trackId, $quality = '192') {
        return $this->query("INSERT INTO download_queue (trackId, status, quality) VALUES (:tid, 'pending', :qual)", [
            ':tid' => $trackId,
            ':qual' => $quality
        ]);
    }

    public function getQueue($limit = 500) {
        // In a real scenario, we'd join with tracks table to get more info
        // For this mock, we just return the queue
        return $this->fetchAll("SELECT * FROM download_queue ORDER BY addedAt DESC LIMIT :limit", [':limit' => $limit]);
    }

    public function updateStatus($ids, $status) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE download_queue SET status = ? WHERE id IN ($placeholders)";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(1, $status);
        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 2, $id, SQLITE3_INTEGER);
        }
        return $stmt->execute();
    }

    public function delete($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM download_queue WHERE id IN ($placeholders)";
        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($ids as $i => $id) {
            $stmt->bindValue($i + 1, $id, SQLITE3_INTEGER);
        }
        return $stmt->execute();
    }

    public function deleteByStatus($status) {
        return $this->query("DELETE FROM download_queue WHERE status = :status", [':status' => $status]);
    }
}
