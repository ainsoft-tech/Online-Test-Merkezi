<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Bu sayfaya erişim yetkiniz yok.');
    exit;
}

// Fetch all exams
try {
    $db = get_db_connection();
    $stmt = $db->query("SELECT id, title, description, created_at FROM exams ORDER BY created_at DESC");
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Testler alınırken bir hata oluştu: " . $e->getMessage();
    $exams = [];
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
                                        <span class="card-title">Test Yönetimi</span>
                                    </div>
                                    <div class="col s2 right-align">
                                        <a href="test_olustur.php" class="btn-floating btn-large waves-effect waves-light teal"><i class="material-icons">add</i></a>
                                    </div>
                                </div>
                                
                                <?php if (isset($_GET['success'])): ?>
                                    <div class="card-panel teal lighten-2 white-text"><?php echo htmlspecialchars($_GET['success']); ?></div>
                                <?php endif; ?>
                                <?php if (isset($error)): ?>
                                    <div class="card-panel red lighten-2 white-text"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>

                                <table class="striped highlight responsive-table">
                                    <thead>
                                        <tr>
                                            <th>Test Adı</th>
                                            <th>Açıklama</th>
                                            <th>Oluşturulma Tarihi</th>
                                            <th class="center-align">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($exams)): ?>
                                            <tr>
                                                <td colspan="4" class="center-align">Henüz hiç test oluşturulmamış.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($exams as $exam): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($exam['title']); ?></td>
                                                    <td><?php echo htmlspecialchars(substr($exam['description'], 0, 50)) . (strlen($exam['description']) > 50 ? '...' : ''); ?></td>
                                                    <td><?php echo date('d.m.Y H:i', strtotime($exam['created_at'])); ?></td>
                                                    <td class="center-align">
                                                        <a href="test_detay.php?id=<?php echo $exam['id']; ?>" class="btn-small waves-effect waves-light blue">Detay</a>
                                                        <a href="test_duzenle.php?id=<?php echo $exam['id']; ?>" class="btn-small waves-effect waves-light orange">Düzenle</a>
                                                        <a href="test_sil.php?id=<?php echo $exam['id']; ?>" class="btn-small waves-effect waves-light red" onclick="return confirm('Bu testi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');">Sil</a>
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
