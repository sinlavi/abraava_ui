<?php
require_once __DIR__ . '/../core/BaseModel.php';

class iTunesModel extends BaseModel {
    public function saveArtist($artistId) {
        return $this->query("INSERT OR IGNORE INTO artists (artistId) VALUES (:id)", [':id' => $artistId]);
    }

    public function saveCollection($collectionId) {
        return $this->query("INSERT OR IGNORE INTO collections (collectionId) VALUES (:id)", [':id' => $collectionId]);
    }

    public function saveTrack($trackId) {
        return $this->query("INSERT OR IGNORE INTO tracks (trackId) VALUES (:id)", [':id' => $trackId]);
    }

    public function getMirrors($type, $id) {
        $res = $this->query("SELECT * FROM entityMirrors WHERE entityType = :t AND entityId = :id", [
            ':t' => $type,
            ':id' => $id
        ]);
        $mirrors = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $mirrors[$row['urlType']] = ['url' => $row['mirrorUrl'], 'quality' => $row['quality']];
        }
        return $mirrors;
    }

    public function getLyrics($id) {
        $res = $this->query("SELECT lyrics FROM tracks WHERE trackId = :id", [':id' => $id]);
        $row = $res->fetchArray(SQLITE3_ASSOC);
        return $row ? json_decode($row['lyrics'], true) : null;
    }
}
