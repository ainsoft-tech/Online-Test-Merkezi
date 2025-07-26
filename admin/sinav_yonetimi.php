<?php
require_once '../config/database.php';
require_once '../includes/header.php';

$db = get_db_connection();

// Sınavları veritabanından al
$stmt = $db->query("SELECT * FROM sinavlar ORDER BY olusturma_tarihi DESC");
$sinavlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main>
<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col s12 m9 l10">
            <div class="container">
                <h1 class="mt-4">Sınav Yönetimi</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Sınav Yönetimi</li>
                </ol>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table mr-1"></i>
                        Sınav Listesi
                        <a href="sinav_olustur.php" class="btn btn-primary btn-sm right">Yeni Sınav Oluştur</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Sınav Adı</th>
                                        <th>Açıklama</th>
                                        <th>Başlangıç Tarihi</th>
                                        <th>Bitiş Tarihi</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sinavlar as $sinav): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($sinav['sinav_adi']) ?></td>
                                            <td><?= htmlspecialchars($sinav['aciklama']) ?></td>
                                            <td><?= htmlspecialchars($sinav['baslangic_tarihi']) ?></td>
                                            <td><?= htmlspecialchars($sinav['bitis_tarihi']) ?></td>
                                            <td><?= $sinav['yayin_durumu'] == 1 ? '<span class="badge badge-success">Yayınlandı</span>' : '<span class="badge badge-warning">Taslak</span>' ?></td>
                                            <td>
                                                <a href="sinav_detay.php?id=<?= $sinav['id'] ?>" class="btn btn-info btn-sm">Detay</a>
                                                <a href="sinav_duzenle.php?id=<?= $sinav['id'] ?>" class="btn btn-warning btn-sm">Düzenle</a>
                                                <a href="sinav_sil.php?id=<?= $sinav['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu sınavı silmek istediğinizden emin misiniz?')">Sil</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>
