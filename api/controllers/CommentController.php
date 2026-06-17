<?php
require_once __DIR__ . '/BaseController.php';

class CommentController extends BaseController {
    public function add() {
        $this->checkAuth();
        $params = $this->getParams();
        $trackId = $params['track_id'] ?? '';
        $content = $params['content'] ?? '';

        if (empty($trackId) || empty($content)) $this->respond(['error' => 'Missing parameters'], 400);

        $this->db->query("INSERT INTO comments (user_id, track_id, content) VALUES (:uid, :tid, :content)", [
            ':uid' => $_SESSION['user_id'],
            ':tid' => $trackId,
            ':content' => $content
        ]);

        $this->respond(['success' => true]);
    }

    public function list() {
        $params = $this->getParams();
        $trackId = $params['track_id'] ?? '';

        if (empty($trackId)) $this->respond(['error' => 'Missing track ID'], 400);

        $res = $this->db->query("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.track_id = :tid ORDER BY c.created_at DESC", [
            ':tid' => $trackId
        ]);
        $comments = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $comments[] = $row;
        }
        $this->respond(['comments' => $comments]);
    }
}
