<?php
require_once '../config/database.php';
require_once '../includes/header.php'; // Öğrenci header'ı kullanılmalı

$db = get_db_connection();

// Yayınlanmış ve aktif olan sınavları al
$stmt = $db->prepare("SELECT * FROM sinavlar WHERE yayin_durumu = 1 AND datetime('now') BETWEEN baslangic_tarihi AND bitis_tarihi ORDER BY baslangic_tarihi DESC");
$stmt->execute();
$sinavlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container">
    <h1 class="mt-4">Aktif Sınavlar</h1>
    <div class="row">
        <?php if (empty($sinavlar)): ?>
            <div class="col-12">
                <p>Şu anda aktif bir sınav bulunmamaktadır.</p>
            </div>
        <?php else: ?>
            <?php foreach ($sinavlar as $sinav): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($sinav['sinav_adi']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($sinav['aciklama']) ?></p>
                            <p class="card-text"><small class="text-muted">Bitiş Tarihi: <?= htmlspecialchars($sinav['bitis_tarihi']) ?></small></p>
                            <a href="sinav_basla.php?id=<?= $sinav['id'] ?>" class="btn btn-primary">Sınava Başla</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; // Öğrenci footer'ı kullanılmalı ?>
