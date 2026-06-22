<?php
require_once __DIR__ . '/Database.php';

class BaseModel {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function all() {
        $res = $this->db->query("SELECT * FROM {$this->table}");
        $results = [];
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    public function find($id) {
        $res = $this->db->query("SELECT * FROM {$this->table} WHERE id = :id", [':id' => $id]);
        return $res->fetchArray(SQLITE3_ASSOC);
    }

    public function delete($id) {
        return $this->db->query("DELETE FROM {$this->table} WHERE id = :id", [':id' => $id]);
    }

    public function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }
}
