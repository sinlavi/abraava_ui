<?php
require_once __DIR__ . '/BaseController.php';

class DatabaseController extends BaseController {
    public function stats() {
        $this->checkAdmin();
        $trackCount = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM tracks");
        $artistCount = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM artists");
        $albumCount = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM collections");
        $userCount = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM users");
        $cacheCount = $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM requestCache");

        $this->respond([
            'track_count' => $trackCount,
            'artist_count' => $artistCount,
            'album_count' => $albumCount,
            'user_count' => $userCount,
            'cache_entries' => $cacheCount,
            'db_size_bytes' => filesize(__DIR__ . '/../../itunes.db'),
            'uptime_seconds' => 0 // Mock or calculate if possible
        ]);
    }

    public function clearCache() {
        $this->checkAdmin();
        $this->db->exec("DELETE FROM requestCache");
        $this->respond(['success' => true, 'message' => 'Request cache cleared successfully']);
    }

    public function manageData() {
        $this->respond(['message' => 'Data management feature to be implemented']);
    }
}
