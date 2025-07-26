<?php
require_once '../config/database.php';
require_once '../includes/header.php'; // Öğrenci header'ı kullanılmalı

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ogrenci') {
    header('Location: login.php');
    exit();
}

$db = get_db_connection();
$sinav_id = $_GET['id'] ?? null;

if (!$sinav_id) {
    header("Location: sinav_listesi.php");
    exit();
}

// Sınav bilgilerini ve soruları al
$stmt_sinav = $db->prepare("SELECT * FROM sinavlar WHERE id = ? AND yayin_durumu = 1 AND datetime('now') BETWEEN baslangic_tarihi AND bitis_tarihi");
$stmt_sinav->execute([$sinav_id]);
$sinav = $stmt_sinav->fetch(PDO::FETCH_ASSOC);

if (!$sinav) {
    echo "<div class='container'><p>Bu sınava şu anda erişilemiyor veya sınav mevcut değil.</p></div>";
    require_once '../includes/footer.php';
    exit();
}

$stmt_sorular = $db->prepare("SELECT * FROM sinav_sorulari WHERE sinav_id = ?");
$stmt_sorular->execute([$sinav_id]);
$sorular = $stmt_sorular->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container">
    <h1 class="mt-4"><?= htmlspecialchars($sinav['sinav_adi']) ?></h1>
    <p><?= htmlspecialchars($sinav['aciklama']) ?></p>
    <hr>

    <form action="sinav_sonuc.php" method="post">
        <input type="hidden" name="sinav_id" value="<?= $sinav_id ?>">
        <?php foreach ($sorular as $index => $soru): ?>
            <div class="card mb-4">
                <div class="card-header">
                    Soru <?= $index + 1 ?>
                </div>
                <div class="card-body">
                    <?php if ($soru['soru_gorseli']): ?>
                        <img src="../uploads/questions/<?= htmlspecialchars($soru['soru_gorseli']) ?>" alt="Soru Görseli" class="img-fluid mb-3">
                    <?php endif; ?>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="cevap[<?= $soru['id'] ?>]" id="cevap_a_<?= $soru['id'] ?>" value="A" required>
                        <label class="form-check-label" for="cevap_a_<?= $soru['id'] ?>">A</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="cevap[<?= $soru['id'] ?>]" id="cevap_b_<?= $soru['id'] ?>" value="B">
                        <label class="form-check-label" for="cevap_b_<?= $soru['id'] ?>">B</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="cevap[<?= $soru['id'] ?>]" id="cevap_c_<?= $soru['id'] ?>" value="C">
                        <label class="form-check-label" for="cevap_c_<?= $soru['id'] ?>">C</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="cevap[<?= $soru['id'] ?>]" id="cevap_d_<?= $soru['id'] ?>" value="D">
                        <label class="form-check-label" for="cevap_d_<?= $soru['id'] ?>">D</label>
                    </div>
                    <?php if ($soru['secenek_sayisi'] == 5): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="cevap[<?= $soru['id'] ?>]" id="cevap_e_<?= $soru['id'] ?>" value="E">
                        <label class="form-check-label" for="cevap_e_<?= $soru['id'] ?>">E</label>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-success">Sınavı Bitir</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
