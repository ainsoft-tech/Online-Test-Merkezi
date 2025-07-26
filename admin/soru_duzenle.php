<?php
session_start();
require_once '../config/database.php';
require_once '../config/exam_vt.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['tip'])) {
    header('Location: soru_yonetimi.php?error=Geçersiz parametre.');
    exit;
}

$id = $_GET['id'];
$tip = $_GET['tip'];
$exam = new Exam();
$db = $exam->db;

// Soru tipine göre farklı işlem yap
if ($tip === 'yeni') {
    // YENİ SİSTEM: Metin tabanlı soruyu ve seçeneklerini düzenle
    $stmt_soru = $db->prepare("SELECT * FROM sorular WHERE id = :id");
    $stmt_soru->execute([':id' => $id]);
    $soru = $stmt_soru->fetch(PDO::FETCH_ASSOC);

    if (!$soru) {
        header('Location: soru_yonetimi.php?error=Soru bulunamadı.');
        exit;
    }

    $stmt_secenekler = $db->prepare("SELECT secenek_key, secenek_metni FROM secenekler WHERE soru_id = :soru_id ORDER BY secenek_key ASC");
    $stmt_secenekler->execute([':soru_id' => $id]);
    $secenekler = $stmt_secenekler->fetchAll(PDO::FETCH_KEY_PAIR);

    include '../includes/header_bootstrap.php';
    ?>
    <h2 class="mb-4">Metin Tabanlı Soruyu Düzenle</h2>
    <div class="card">
        <div class="card-body">
            <form action="soru_guncelle.php" method="POST">
                <input type="hidden" name="soru_id" value="<?php echo $soru['id']; ?>">
                <div class="mb-3">
                    <label class="form-label">Soru Metni</label>
                    <textarea class="form-control" name="soru_metni" rows="5" required><?php echo htmlspecialchars($soru['soru_metni']); ?></textarea>
                </div>
                <hr>
                <h6>Seçenekler ve Doğru Cevap</h6>
                <div class="row">
                    <?php foreach ($secenekler as $key => $value): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Seçenek <?php echo strtoupper($key); ?></label>
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" name="dogru_cevap" value="<?php echo $key; ?>" <?php echo ($key === $soru['dogru_cevap']) ? 'checked' : ''; ?> required>
                            </div>
                            <input type="text" class="form-control" name="secenek[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($value); ?>" required>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="soru_yonetimi.php" class="btn btn-secondary">İptal</a>
                <button type="submit" name="soru_guncelle" class="btn btn-primary">Güncelle</button>
            </form>
        </div>
    </div>
    <?php
    include '../includes/footer_bootstrap.php';

} elseif ($tip === 'eski') {
    // ESKİ SİSTEM: Resimli soruyu düzenle (Tamamen restore edildi)
    $stmt_q = $db->prepare("SELECT * FROM questions WHERE id = :id");
    $stmt_q->execute([':id' => $id]);
    $question = $stmt_q->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        header('Location: soru_yonetimi.php?error=Soru bulunamadı.');
        exit;
    }
    
    $stmt_cat = $db->query("SELECT id, ders_adi FROM dersler ORDER BY ders_adi");
    $categories = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

    include '../includes/header.php';
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
                                    <span class="card-title">Resimli Soruyu Düzenle</span>
                                     <?php if (isset($_GET['error'])): ?>
                                        <div class="card-panel red lighten-2 white-text"><?php echo htmlspecialchars($_GET['error']); ?></div>
                                    <?php endif; ?>
                                    <form action="soru_guncelle_resimli.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                        <div class="row">
                                            <div class="input-field col s12 m6">
                                                <select id="category_id" name="ders_id" required>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?php echo $category['id']; ?>" <?php echo ($question['ders_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['ders_adi']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label>Ders</label>
                                            </div>
                                            <div class="input-field col s12 m6">
                                                <select id="konu_id" name="konu_id" required>
                                                    <!-- Konular JS ile yüklenecek -->
                                                </select>
                                                <label>Konu</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="file-field input-field col s12">
                                                <div class="btn teal">
                                                    <span>Yeni Soru Görseli (İsteğe Bağlı)</span>
                                                    <input type="file" name="question_image" accept="image/*">
                                                </div>
                                                <div class="file-path-wrapper">
                                                    <input class="file-path validate" type="text">
                                                </div>
                                                <?php if ($question['image_path']): ?>
                                                    <p>Mevcut Görsel: <img src="../uploads/questions/<?php echo htmlspecialchars($question['image_path']); ?>" alt="Mevcut Soru Görseli" width="100"></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                         <div class="row">
                                            <div class="input-field col s12">
                                                <select id="option_count_selector" name="option_count">
                                                    <option value="4" <?php echo ($question['option_count'] == 4) ? 'selected' : ''; ?>>4 Seçenek</option>
                                                    <option value="5" <?php echo ($question['option_count'] == 5) ? 'selected' : ''; ?>>5 Seçenek</option>
                                                </select>
                                                <label>Seçenek Sayısı</label>
                                            </div>
                                        </div>
                                        <div id="options_area"></div>
                                        <div class="input-field right-align">
                                            <a href="soru_yonetimi.php" class="btn waves-effect waves-light grey">İptal</a>
                                            <button type="submit" class="btn waves-effect waves-light teal">Güncelle</button>
                                        </div>
                                    </form>
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
            var elems = document.querySelectorAll('select');
            M.FormSelect.init(elems);

            const dersSelect = document.getElementById('category_id');
            const konuSelect = document.getElementById('konu_id');
            const optionsArea = document.getElementById('options_area');
            const selector = document.getElementById('option_count_selector');
            const currentCorrectAnswer = <?php echo json_encode($question['correct_answer']); ?>;
            const currentKonuId = <?php echo json_encode($question['konu_id']); ?>;

            function loadKonular(dersId, selectedKonuId) {
                konuSelect.innerHTML = '<option value="" disabled>Yükleniyor...</option>';
                M.FormSelect.init(konuSelect);

                if (dersId) {
                    fetch(`get_konular.php?ders_id=${dersId}`)
                        .then(response => response.json())
                        .then(data => {
                            konuSelect.innerHTML = '<option value="" disabled>Konu Seçiniz</option>';
                            data.forEach(konu => {
                                const selected = (konu.id == selectedKonuId) ? 'selected' : '';
                                konuSelect.innerHTML += `<option value="${konu.id}" ${selected}>${konu.konu_adi}</option>`;
                            });
                            M.FormSelect.init(konuSelect);
                        })
                        .catch(error => {
                            console.error('Konular alınırken hata oluştu:', error);
                            konuSelect.innerHTML = '<option value="" disabled>Konu alınamadı</option>';
                            M.FormSelect.init(konuSelect);
                        });
                }
            }

            if (dersSelect.value) {
                loadKonular(dersSelect.value, currentKonuId);
            }

            dersSelect.addEventListener('change', function() {
                loadKonular(this.value, null);
            });

            function generateOptions(count) {
                optionsArea.innerHTML = '';
                let content = '<div class="row"><p><strong>Doğru Cevap</strong></p></div><div class="row">';
                for (let i = 1; i <= count; i++) {
                    const isChecked = (i == currentCorrectAnswer) ? 'checked' : '';
                    content += `<div class="col s6 m4 l2"><label><input name="correct_answer" type="radio" value="${i}" ${isChecked} required /><span>${String.fromCharCode(64 + i)})</span></label></div>`;
                }
                content += '</div>';
                optionsArea.innerHTML = content;
            }

            generateOptions(selector.value);
            selector.addEventListener('change', function() {
                generateOptions(this.value);
            });
        });
    </script>
    <?php
    include '../includes/footer.php';
}
?>