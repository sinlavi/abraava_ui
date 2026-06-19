<?php
require_once __DIR__ . '/Database.php';

class BaseModel {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    protected function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }

    protected function fetchAll($sql, $params = []) {
        $res = $this->query($sql, $params);
        $rows = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    protected function fetchOne($sql, $params = []) {
        $res = $this->query($sql, $params);
        return $res->fetchArray(SQLITE3_ASSOC);
    }

    protected function lastInsertRowID() {
        return $this->db->getConnection()->lastInsertRowID();
    }

    protected function changes() {
        return $this->db->getConnection()->changes();
    }
}
