<?php
require_once '../config/database.php';
require_once '../includes/header.php';

$db = get_db_connection();
$sinav_id = $_GET['id'] ?? null;

if (!$sinav_id) {
    header("Location: sinav_yonetimi.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sinav_adi = $_POST['sinav_adi'];
    $aciklama = $_POST['aciklama'];
    $baslangic_tarihi = $_POST['baslangic_tarihi'];
    $bitis_tarihi = $_POST['bitis_tarihi'];
    $yayin_durumu = $_POST['yayin_durumu'];

    $stmt = $db->prepare("UPDATE sinavlar SET sinav_adi = ?, aciklama = ?, baslangic_tarihi = ?, bitis_tarihi = ?, yayin_durumu = ? WHERE id = ?");
    
    if ($stmt->execute([$sinav_adi, $aciklama, $baslangic_tarihi, $bitis_tarihi, $yayin_durumu, $sinav_id])) {
        header("Location: sinav_yonetimi.php");
        exit();
    } else {
        $error = "Sınav güncellenirken bir hata oluştu.";
    }
} else {
    $stmt = $db->prepare("SELECT * FROM sinavlar WHERE id = ?");
    $stmt->execute([$sinav_id]);
    $sinav = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sinav) {
        header("Location: sinav_yonetimi.php");
        exit();
    }
}
?>

<main>
<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col s12 m9 l10">
            <div class="container">
                <h1 class="mt-4">Sınavı Düzenle</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="sinav_yonetimi.php">Sınav Yönetimi</a></li>
                    <li class="breadcrumb-item active">Sınavı Düzenle</li>
                </ol>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-edit mr-1"></i>
                        Sınav Bilgilerini Düzenle
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form action="sinav_duzenle.php?id=<?= $sinav_id ?>" method="post">
                            <div class="form-group">
                                <label for="sinav_adi">Sınav Adı</label>
                                <input type="text" name="sinav_adi" id="sinav_adi" class="form-control" value="<?= htmlspecialchars($sinav['sinav_adi']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="aciklama">Açıklama</label>
                                <textarea name="aciklama" id="aciklama" class="form-control"><?= htmlspecialchars($sinav['aciklama']) ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="baslangic_tarihi">Başlangıç Tarihi</label>
                                <input type="datetime-local" name="baslangic_tarihi" id="baslangic_tarihi" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($sinav['baslangic_tarihi'])) ?>">
                            </div>
                            <div class="form-group">
                                <label for="bitis_tarihi">Bitiş Tarihi</label>
                                <input type="datetime-local" name="bitis_tarihi" id="bitis_tarihi" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($sinav['bitis_tarihi'])) ?>">
                            </div>
                            <div class="form-group">
                                <label for="yayin_durumu">Yayın Durumu</label>
                                <select name="yayin_durumu" id="yayin_durumu" class="form-control">
                                    <option value="0" <?= $sinav['yayin_durumu'] == 0 ? 'selected' : '' ?>>Taslak</option>
                                    <option value="1" <?= $sinav['yayin_durumu'] == 1 ? 'selected' : '' ?>>Yayınlandı</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>
