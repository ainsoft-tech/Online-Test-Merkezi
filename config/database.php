<?php
define('DB_PATH', __DIR__ . '/../exam_vt/online_exam.db');

function get_db_connection() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // --- ORİJİNAL SİSTEM TABLOLARI (TAM VE EKSİKSİZ) ---
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            role TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS dersler (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ders_adi TEXT NOT NULL,
            ders_grubu TEXT,
            ders_kodu TEXT UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Bu, resimli sorular için kullanılan ORİJİNAL tablodur.
        $db->exec("CREATE TABLE IF NOT EXISTS questions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            image_path TEXT NOT NULL,
            option_count INTEGER NOT NULL,
            correct_answer INTEGER NOT NULL,
            ders_id INTEGER,
            konu_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ders_id) REFERENCES dersler(id)
        )");

        // Bu, resimli soruların seçenekleri için kullanılan ORİJİNAL tablodur.
        $db->exec("CREATE TABLE IF NOT EXISTS options (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            question_id INTEGER NOT NULL,
            option_text TEXT NOT NULL,
            option_number INTEGER NOT NULL,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
        )");


        // --- YAPAY ZEKA İÇİN YENİ TABLOLAR ---
        $db->exec("CREATE TABLE IF NOT EXISTS konular (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ders_id INTEGER NOT NULL,
            konu_adi TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ders_id) REFERENCES dersler(id) ON DELETE CASCADE
        )");

        // Bu, metin tabanlı (Yapay Zeka) sorular için YENİ tablodur.
        $db->exec("CREATE TABLE IF NOT EXISTS sorular (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ders_id INTEGER NOT NULL,
            konu_id INTEGER NOT NULL,
            soru_metni TEXT NULL,
            soru_resmi TEXT NULL,
            dogru_cevap TEXT NOT NULL,
            created_by INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (ders_id) REFERENCES dersler(id) ON DELETE CASCADE,
            FOREIGN KEY (konu_id) REFERENCES konular(id) ON DELETE CASCADE,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )");

        // Bu, metin tabanlı (Yapay Zeka) soruların seçenekleri için YENİ tablodur.
        $db->exec("CREATE TABLE IF NOT EXISTS secenekler (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            soru_id INTEGER NOT NULL,
            secenek_key TEXT NOT NULL,
            secenek_metni TEXT NOT NULL,
            FOREIGN KEY (soru_id) REFERENCES sorular(id) ON DELETE CASCADE
        )");

        return $db;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
