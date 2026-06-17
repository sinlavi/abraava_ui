<?php
require_once __DIR__ . '/BaseController.php';

class PlaylistController extends BaseController {
    public function create() {
        $this->checkAuth();
        $params = $this->getParams();
        $name = $params['name'] ?? '';

        if (empty($name)) $this->respond(['error' => 'Name is required'], 400);

        $this->db->query("INSERT INTO playlists (user_id, name) VALUES (:uid, :name)", [
            ':uid' => $_SESSION['user_id'],
            ':name' => $name
        ]);

        $this->respond(['success' => true, 'id' => $this->db->getConnection()->lastInsertRowID()]);
    }

    public function list() {
        $this->checkAuth();
        $res = $this->db->query("SELECT * FROM playlists WHERE user_id = :uid", [':uid' => $_SESSION['user_id']]);
        $playlists = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $playlists[] = $row;
        }
        $this->respond(['playlists' => $playlists]);
    }

    public function addTrack() {
        $this->checkAuth();
        $params = $this->getParams();
        $playlistId = $params['playlist_id'] ?? null;
        $trackId = $params['track_id'] ?? null;

        if (!$playlistId || !$trackId) $this->respond(['error' => 'Missing parameters'], 400);

        // Verify ownership
        $res = $this->db->query("SELECT 1 FROM playlists WHERE id = :pid AND user_id = :uid", [
            ':pid' => $playlistId,
            ':uid' => $_SESSION['user_id']
        ]);
        if (!$res->fetchArray()) $this->respond(['error' => 'Unauthorized'], 403);

        $this->db->query("INSERT OR IGNORE INTO playlist_tracks (playlist_id, track_id) VALUES (:pid, :tid)", [
            ':pid' => $playlistId,
            ':tid' => $trackId
        ]);

        $this->respond(['success' => true]);
    }

    public function getTracks() {
        $this->checkAuth();
        $params = $this->getParams();
        $playlistId = $params['playlist_id'] ?? null;

        if (!$playlistId) $this->respond(['error' => 'Missing ID'], 400);

        $res = $this->db->query("SELECT track_id FROM playlist_tracks WHERE playlist_id = :pid", [':pid' => $playlistId]);
        $tracks = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $tracks[] = $row['track_id'];
        }
        $this->respond(['tracks' => $tracks]);
    }

    public function delete() {
        $this->checkAuth();
        $params = $this->getParams();
        $id = $params['id'] ?? null;
        if (!$id) $this->respond(['error' => 'Missing ID'], 400);

        $this->db->query("DELETE FROM playlists WHERE id = :id AND user_id = :uid", [
            ':id' => $id,
            ':uid' => $_SESSION['user_id']
        ]);
        $this->respond(['success' => true]);
    }
}
