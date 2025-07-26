<?php
require_once '../config/database.php';
require_once '../includes/header.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ogrenci') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: sinav_listesi.php');
    exit();
}

$db = get_db_connection();
$sinav_id = $_POST['sinav_id'] ?? null;
$cevaplar = $_POST['cevap'] ?? [];

if (!$sinav_id || empty($cevaplar)) {
    header('Location: sinav_listesi.php');
    exit();
}

// Sınavın doğru cevaplarını al
$stmt_sorular = $db->prepare("SELECT id, dogru_cevap FROM sinav_sorulari WHERE sinav_id = ?");
$stmt_sorular->execute([$sinav_id]);
$dogru_cevaplar = $stmt_sorular->fetchAll(PDO::FETCH_KEY_PAIR);

$dogru_sayisi = 0;
$yanlis_sayisi = 0;
$bos_sayisi = 0;
$toplam_soru = count($dogru_cevaplar);

foreach ($dogru_cevaplar as $soru_id => $dogru_cevap) {
    if (isset($cevaplar[$soru_id])) {
        if ($cevaplar[$soru_id] == $dogru_cevap) {
            $dogru_sayisi++;
        } else {
            $yanlis_sayisi++;
        }
    } else {
        $bos_sayisi++;
    }
}

// Sınav adını al
$stmt_sinav = $db->prepare("SELECT sinav_adi FROM sinavlar WHERE id = ?");
$stmt_sinav->execute([$sinav_id]);
$sinav_adi = $stmt_sinav->fetchColumn();

?>

<div class="container">
    <h1 class="mt-4">Sınav Sonucu: <?= htmlspecialchars($sinav_adi) ?></h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Sonuçlarınız</h5>
            <p>Toplam Soru: <strong><?= $toplam_soru ?></strong></p>
            <p class="text-success">Doğru Sayısı: <strong><?= $dogru_sayisi ?></strong></p>
            <p class="text-danger">Yanlış Sayısı: <strong><?= $yanlis_sayisi ?></strong></p>
            <p class="text-warning">Boş Sayısı: <strong><?= $bos_sayisi ?></strong></p>
            <hr>
            <a href="sinav_listesi.php" class="btn btn-primary">Diğer Sınavlara Göz At</a>
            <a href="dashboard.php" class="btn btn-secondary">Anasayfaya Dön</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
