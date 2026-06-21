<?php
require_once __DIR__ . '/BaseController.php';

class DatabaseController extends BaseController {
    public function stats() {
        $this->checkAdmin();
        $trackCount = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM tracks");
        $artistCount = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM artists");
        $albumCount = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM collections");
        $userCount = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM users");

        $this->respond([
            'track_count' => $trackCount,
            'artist_count' => $artistCount,
            'album_count' => $albumCount,
            'user_count' => $userCount,
            'db_size_bytes' => filesize(__DIR__ . '/../../itunes.db')
        ]);
    }

    public function clearCache() {
        $this->checkAdmin();
        $this->db->query("DELETE FROM requestCache");
        $this->respond(['success' => true, 'message' => 'Request cache cleared']);
    }
}
