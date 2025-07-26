<?php
require_once '../config/database.php';
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sinav_adi = $_POST['sinav_adi'];
    $aciklama = $_POST['aciklama'];
    $baslangic_tarihi = $_POST['baslangic_tarihi'];
    $bitis_tarihi = $_POST['bitis_tarihi'];

    $db = get_db_connection();
    $stmt = $db->prepare("INSERT INTO sinavlar (sinav_adi, aciklama, baslangic_tarihi, bitis_tarihi) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$sinav_adi, $aciklama, $baslangic_tarihi, $bitis_tarihi])) {
        header("Location: sinav_yonetimi.php");
        exit();
    } else {
        $error = "Sınav oluşturulurken bir hata oluştu.";
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
                <h1 class="mt-4">Yeni Sınav Oluştur</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="sinav_yonetimi.php">Sınav Yönetimi</a></li>
                    <li class="breadcrumb-item active">Yeni Sınav Oluştur</li>
                </ol>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-plus-square mr-1"></i>
                        Yeni Sınav Bilgileri
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form action="sinav_olustur.php" method="post">
                            <div class="form-group">
                                <label for="sinav_adi">Sınav Adı</label>
                                <input type="text" name="sinav_adi" id="sinav_adi" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="aciklama">Açıklama</label>
                                <textarea name="aciklama" id="aciklama" class="form-control"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="baslangic_tarihi">Başlangıç Tarihi</label>
                                <input type="datetime-local" name="baslangic_tarihi" id="baslangic_tarihi" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="bitis_tarihi">Bitiş Tarihi</label>
                                <input type="datetime-local" name="bitis_tarihi" id="bitis_tarihi" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Sınavı Oluştur</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>
