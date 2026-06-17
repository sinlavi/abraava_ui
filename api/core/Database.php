<?php

class Database {
    private static $instance = null;
    private $db;

    private function __construct() {
        $dbPath = __DIR__ . '/../../itunes.db';
        $this->db = new SQLite3($dbPath);
        $this->db->enableExceptions(true);
        $this->db->busyTimeout(5000);
        $this->db->exec('PRAGMA journal_mode=WAL');
        $this->db->exec('PRAGMA synchronous=NORMAL');
        $this->initSchema();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->db;
    }

    private function initSchema() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS artists (artistId TEXT PRIMARY KEY)");
        $this->db->exec("CREATE TABLE IF NOT EXISTS collections (collectionId TEXT PRIMARY KEY)");
        $this->db->exec("CREATE TABLE IF NOT EXISTS tracks (trackId TEXT PRIMARY KEY, lyrics TEXT)");

        $this->db->exec("CREATE TABLE IF NOT EXISTS entityMirrors (
            entityType TEXT NOT NULL, entityId TEXT NOT NULL, urlType TEXT NOT NULL,
            mirrorUrl TEXT NOT NULL, quality TEXT, platform TEXT NOT NULL DEFAULT 'bale',
            updatedAt TEXT, PRIMARY KEY (entityType, entityId, urlType, quality, platform)
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS download_queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            trackId TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            filePath TEXT,
            quality TEXT,
            platform TEXT DEFAULT 'bale',
            addedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            startedAt DATETIME,
            completedAt DATETIME,
            errorMessage TEXT,
            retryCount INTEGER DEFAULT 0,
            priority INTEGER DEFAULT 0
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS requestCache (
            id INTEGER PRIMARY KEY AUTOINCREMENT, endpoint TEXT NOT NULL, params TEXT NOT NULL,
            resultIds TEXT NOT NULL, expiresAt DATETIME NOT NULL, lastAccessed DATETIME,
            accessCount INTEGER DEFAULT 0, UNIQUE(endpoint, params)
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS playlists (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS playlist_tracks (
            playlist_id INTEGER NOT NULL,
            track_id TEXT NOT NULL,
            added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (playlist_id, track_id),
            FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE
        )");

        $this->db->exec("CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            track_id TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    }

    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $type = is_int($val) ? SQLITE3_INTEGER : (is_float($val) ? SQLITE3_FLOAT : SQLITE3_TEXT);
            $stmt->bindValue($key, $val, $type);
        }
        return $stmt->execute();
    }

    public function exec($sql) {
        return $this->db->exec($sql);
    }

    public function escapeString($string) {
        return $this->db->escapeString($string);
    }
}
