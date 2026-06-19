<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/iTunesEntity.php';

class iTunesController extends BaseController {
    private const ITUNES_SEARCH_API = 'https://itunes.apple.com/search';
    private const ITUNES_LOOKUP_API = 'https://itunes.apple.com/lookup';
    private $itunesModel;

    public function __construct() {
        parent::__construct();
        $this->itunesModel = new iTunesEntity();
    }

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
            foreach ($response['results'] as &$item) {
                if (($item['wrapperType'] ?? '') === 'track') {
                    $item['mirrorUrls'] = $this->itunesModel->getMirrors('track', $item['trackId']);
                    $item['lyrics'] = $this->itunesModel->getLyrics($item['trackId']);
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
                $this->itunesModel->saveArtist($entity['artistId']);
            } elseif ($type === 'collection') {
                $this->itunesModel->saveCollection($entity['collectionId']);
            } elseif ($type === 'track') {
                $this->itunesModel->saveTrack($entity['trackId']);
            }
        }
    }
}
