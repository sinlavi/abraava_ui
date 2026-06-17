<?php
require_once __DIR__ . '/BaseController.php';

class iTunesController extends BaseController {
    private const ITUNES_SEARCH_API = 'https://itunes.apple.com/search';
    private const ITUNES_LOOKUP_API = 'https://itunes.apple.com/lookup';

    public function search() {
        $this->checkAuth();
        $params = $this->getParams();
        if (empty($params['term'])) {
            $this->respond(['error' => 'Missing term'], 400);
        }

        $url = self::ITUNES_SEARCH_API . '?' . http_build_query($params);
        $response = $this->makeApiRequest($url);

        if ($response) {
            $this->saveEntities($response['results'] ?? []);
            $this->respond($response);
        } else {
            $this->respond(['error' => 'API request failed'], 500);
        }
    }

    public function lookup() {
        $this->checkAuth();
        $params = $this->getParams();
        if (empty($params['id'])) {
            $this->respond(['error' => 'Missing id'], 400);
        }

        $url = self::ITUNES_LOOKUP_API . '?' . http_build_query($params);
        $response = $this->makeApiRequest($url);

        if ($response) {
            $this->saveEntities($response['results'] ?? []);
            // Attach mirrors and lyrics for tracks
            foreach ($response['results'] as &$item) {
                if (($item['wrapperType'] ?? '') === 'track') {
                    $item['mirrorUrls'] = $this->getMirrors('track', $item['trackId']);
                    $item['lyrics'] = $this->getLyrics($item['trackId']);
                }
            }
            $this->respond($response);
        } else {
            $this->respond(['error' => 'API request failed'], 500);
        }
    }

    private function makeApiRequest($url) {
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
        return json_decode($response, true);
    }

    private function saveEntities($entities) {
        foreach ($entities as $entity) {
            $type = $entity['wrapperType'] ?? '';
            if ($type === 'artist') {
                $this->db->query("INSERT OR IGNORE INTO artists (artistId) VALUES (:id)", [':id' => $entity['artistId']]);
            } elseif ($type === 'collection') {
                $this->db->query("INSERT OR IGNORE INTO collections (collectionId) VALUES (:id)", [':id' => $entity['collectionId']]);
            } elseif ($type === 'track') {
                $this->db->query("INSERT OR IGNORE INTO tracks (trackId) VALUES (:id)", [':id' => $entity['trackId']]);
            }
        }
    }

    private function getMirrors($type, $id) {
        $res = $this->db->query("SELECT * FROM entityMirrors WHERE entityType = :t AND entityId = :id", [
            ':t' => $type,
            ':id' => $id
        ]);
        $mirrors = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $mirrors[$row['urlType']] = ['url' => $row['mirrorUrl'], 'quality' => $row['quality']];
        }
        return $mirrors;
    }

    private function getLyrics($id) {
        $res = $this->db->query("SELECT lyrics FROM tracks WHERE trackId = :id", [':id' => $id]);
        $row = $res->fetchArray(SQLITE3_ASSOC);
        return $row ? json_decode($row['lyrics'], true) : null;
    }
}
