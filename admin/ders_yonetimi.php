<?php
session_start();
require_once '../config/database.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Bu sayfaya erişim yetkiniz yok.');
    exit;
}

// Fetch all categories (lessons)
try {
    $db = get_db_connection();
    $stmt = $db->query("SELECT id, ders_adi, ders_grubu, ders_kodu FROM dersler ORDER BY ders_kodu");
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In a real app, you'd log this and show a user-friendly error page
    die("Veritabanından dersler alınırken bir hata oluştu: " . $e->getMessage());
}

require_once '../includes/header.php';
?>
<main>
<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col s12 m9 l10">
            <div class="container">
                <div class="row" style="margin-top: 20px;">
                    <div class="col s12">
                        <div class="card">
                            <div class="card-content">
                                <div class="row valign-wrapper">
                                    <div class="col s10">
                                        <span class="card-title">Ders Yönetimi</span>
                                    </div>
                                    <div class="col s2 right-align">
                                        <a href="ders_ekle.php" class="btn-floating btn-large waves-effect waves-light teal"><i class="material-icons">add</i></a>
                                    </div>
                                </div>
                                
                                <?php if (isset($_GET['success'])): ?>
                                    <div class="card-panel teal lighten-2 white-text"><?php echo htmlspecialchars($_GET['success']); ?></div>
                                <?php endif; ?>
                                <?php if (isset($_GET['error'])): ?>
                                    <div class="card-panel red lighten-2 white-text"><?php echo htmlspecialchars($_GET['error']); ?></div>
                                <?php endif; ?>

                                <table class="striped highlight responsive-table">
                                    <thead>
                                        <tr>
                                            <th>Ders Kodu</th>
                                            <th>Ders Grubu</th>
                                            <th>Ders Adı</th>
                                            <th class="center-align">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($lessons)): ?>
                                            <tr>
                                                <td colspan="3" class="center-align">Henüz hiç ders eklenmemiş.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($lessons as $lesson): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($lesson['ders_kodu']); ?></td>
                                                    <td><?php echo htmlspecialchars($lesson['ders_grubu']); ?></td>
                                                    <td><?php echo htmlspecialchars($lesson['ders_adi']); ?></td>
                                                    <td class="center-align">
                                                        <a href="ders_detay.php?id=<?php echo $lesson['id']; ?>" class="btn-small waves-effect waves-light blue">Detay</a>
                                                        <a href="ders_duzenle.php?id=<?php echo $lesson['id']; ?>" class="btn-small waves-effect waves-light orange">Düzenle</a>
                                                        <a href="ders_sil.php?id=<?php echo $lesson['id']; ?>" class="btn-small waves-effect waves-light red" onclick="return confirm('Bu dersi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');">Sil</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
<?php require_once '../includes/footer.php'; ?>
