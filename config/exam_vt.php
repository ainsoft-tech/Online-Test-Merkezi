<?php
require_once 'database.php';

class Exam {
    public $db;

    public function __construct() {
        $this->db = get_db_connection();
        if ($this->db === null) {
            die("Veritabanı bağlantısı kurulamadı.");
        }
    }

    public function getAllDersler() {
        $stmt = $this->db->prepare("SELECT * FROM dersler ORDER BY ders_adi ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDersById($id) {
        $stmt = $this->db->prepare("SELECT * FROM dersler WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getKonularByDersId($ders_id) {
        $stmt = $this->db->prepare("SELECT * FROM konular WHERE ders_id = :ders_id ORDER BY konu_adi ASC");
        $stmt->execute([':ders_id' => $ders_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getKonuById($id) {
        $stmt = $this->db->prepare("SELECT * FROM konular WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>