<?php

class BaseModel {
    protected $db;

    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }

    protected function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
}
