<?php
session_start();
require_once '../config/database.php';
require_once '../config/exam_vt.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Bu sayfaya erişim yetkiniz yok.');
    exit;
}

$exam = new Exam();
$db = $exam->db;

try {
    // Bu sorgu, eski ve yeni sistemdeki soruları ayrı ayrı sorgulayıp birleştirir.
    // Bu yöntem, JOIN hatalarını engeller.
    $query = "
        SELECT id, soru_metni, soru_resmi, ders_adi, konu_adi, created_at, tip FROM (
            SELECT 
                s.id, 
                s.soru_metni, 
                s.soru_resmi, 
                (SELECT ders_adi FROM dersler WHERE id = s.ders_id) as ders_adi,
                (SELECT konu_adi FROM konular WHERE id = s.konu_id) as konu_adi,
                s.created_at,
                'yeni' as tip
            FROM sorular s
        )
        UNION ALL
        SELECT id, soru_metni, soru_resmi, ders_adi, konu_adi, created_at, tip FROM (
            SELECT 
                q.id,
                NULL as soru_metni,
                q.image_path as soru_resmi,
                (SELECT ders_adi FROM dersler WHERE id = q.ders_id) as ders_adi,
                (SELECT konu_adi FROM konular WHERE id = q.konu_id) as konu_adi,
                q.created_at,
                'eski' as tip
            FROM questions q
        )
        ORDER BY created_at DESC
    ";
    $stmt = $db->query($query);
    $sorular = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Sorular alınırken bir hata oluştu: " . $e->getMessage();
    $sorular = [];
}

require_once '../includes/header.php';
?>
<main>
<div class="container-fluid">
    <div class="row">
        
        <?php include_once '../includes/sidebar.php'; ?>

        <div class="col s12 m9 l10">
            <div class="container">
                <div class="row" style="margin-top: 20px;">
                    <div class="col s12">
                        <div class="card">
                            <div class="card-content">
                                <div class="row valign-wrapper">
                                    <div class="col s8 m9">
                                        <span class="card-title">Soru Yönetimi (Tümü)</span>
                                    </div>
                                    <div class="col s4 m3 right-align">                                        
                                        <a href="soru_ekle.php" class="btn-floating btn-large waves-effect waves-light blue" title="Resimli Soru Ekle"><i class="material-icons left">add</i></a>
                                        <a href="yapay_zeka_soru_olustur.php" class="btn-floating btn-large waves-effect waves-light orange" title="Yapay Zeka İle Soru Ekle"><i class="material-icons">smart_toy</i></a>
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
                                            <th>Soru İçeriği</th>
                                            <th>Ders</th>
                                            <th>Konu</th>
                                            <th>Eklenme Tarihi</th>
                                            <th class="center-align">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($sorular)): ?>
                                            <tr>
                                                <td colspan="5" class="center-align">Henüz hiç soru eklenmemiş.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($sorular as $soru): ?>
                                                <tr>
                                                    <td>
                                                        <?php if (!empty($soru['soru_metni'])): ?>
                                                            <span title="<?php echo htmlspecialchars($soru['soru_metni']); ?>">
                                                                <?php echo htmlspecialchars(mb_substr($soru['soru_metni'], 0, 50)) . (mb_strlen($soru['soru_metni']) > 50 ? '...' : ''); ?>
                                                            </span>
                                                        <?php elseif (!empty($soru['soru_resmi'])): ?>
                                                            <img src="../uploads/questions/<?php echo htmlspecialchars($soru['soru_resmi']); ?>" alt="Soru Görseli" width="100" class="materialboxed">
                                                        <?php else: ?>
                                                            (İçerik Yok)
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($soru['ders_adi'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($soru['konu_adi'] ?? 'N/A'); ?></td>
                                                    <td><?php echo $soru['created_at'] ? date('d.m.Y H:i', strtotime($soru['created_at'])) : 'Bilinmiyor'; ?></td>
                                                    <td class="center-align">
                                                        <a href="soru_detay.php?id=<?php echo $soru['id']; ?>&tip=<?php echo $soru['tip']; ?>" class="btn-small waves-effect btn waves-light blue" title="Detay">Detay</a>
                                                        <a href="soru_duzenle.php?id=<?php echo $soru['id']; ?>&tip=<?php echo $soru['tip']; ?>" class="btn-small waves-effect btn waves-light orange" title="Düzenle">Edit</a>
                                                        <a href="soru_sil.php?id=<?php echo $soru['id']; ?>&tip=<?php echo $soru['tip']; ?>" class="btn-small waves-effect btn waves-light red" onclick="return confirm('Bu soruyu ve bağlı tüm verileri silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.');" title="Sil">Sil</a>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.materialboxed');
    M.Materialbox.init(elems);
});
</script>
<?php require_once '../includes/footer.php'; ?>
