<?php
require_once __DIR__ . '/../core/BaseModel.php';

class iTunesEntity extends BaseModel {
    public function saveArtist($id) {
        return $this->query("INSERT OR IGNORE INTO artists (artistId) VALUES (:id)", [':id' => $id]);
    }

    public function saveCollection($id) {
        return $this->query("INSERT OR IGNORE INTO collections (collectionId) VALUES (:id)", [':id' => $id]);
    }

    public function saveTrack($id, $lyrics = null) {
        if ($lyrics !== null) {
            return $this->query("INSERT INTO tracks (trackId, lyrics) VALUES (:id, :lyrics) ON CONFLICT(trackId) DO UPDATE SET lyrics = :lyrics", [
                ':id' => $id,
                ':lyrics' => json_encode($lyrics)
            ]);
        }
        return $this->query("INSERT OR IGNORE INTO tracks (trackId) VALUES (:id)", [':id' => $id]);
    }

    public function getMirrors($type, $id) {
        return $this->fetchAll("SELECT * FROM entityMirrors WHERE entityType = :t AND entityId = :id", [
            ':t' => $type,
            ':id' => $id
        ]);
    }

    public function getLyrics($id) {
        $row = $this->fetchOne("SELECT lyrics FROM tracks WHERE trackId = :id", [':id' => $id]);
        return $row ? json_decode($row['lyrics'], true) : null;
    }

    public function getStats() {
        return [
            'track_count' => $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM tracks"),
            'artist_count' => $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM artists"),
            'album_count' => $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM collections"),
            'cache_entries' => $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM requestCache")
        ];
    }
}
