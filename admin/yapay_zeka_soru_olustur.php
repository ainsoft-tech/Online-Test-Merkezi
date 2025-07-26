<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once '../config/exam_vt.php';

$exam = new Exam();
$dersler = $exam->getAllDersler();

// Bu sayfa Bootstrap kullandığı için özel bir header çağırıyoruz.
include '../includes/header_bootstrap.php'; 
?>

<h2 class="mb-4">Yapay Zeka ile Soru Oluştur</h2>

<div class="card">
    <div class="card-body">
        <form id="soruOlusturForm" method="POST">
            <div class="mb-3">
                <label for="ders" class="form-label">Ders Seçin</label>
                <select class="form-select" id="ders" name="ders_id" required>
                    <option value="" selected disabled>Lütfen bir ders seçin</option>
                    <?php if (!empty($dersler)): ?>
                        <?php foreach ($dersler as $ders): ?>
                            <option value="<?php echo $ders['id']; ?>"><?php echo htmlspecialchars($ders['ders_adi']); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>Kayıtlı ders bulunamadı. Lütfen önce ders ekleyin.</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="konu" class="form-label">Konu Seçin</label>
                <select class="form-select" id="konu" name="konu_id" required disabled>
                    <option value="">Önce bir ders seçin</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="zorluk" class="form-label">Zorluk Seviyesi</label>
                <select class="form-select" id="zorluk" name="zorluk" required>
                    <option value="kolay">Kolay</option>
                    <option value="orta" selected>Orta</option>
                    <option value="zor">Zor</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="secenek_sayisi" class="form-label">Seçenek Sayısı</label>
                <select class="form-select" id="secenek_sayisi" name="secenek_sayisi" required>
                    <option value="4" selected>4 Seçenek</option>
                    <option value="5">5 Seçenek</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="soru_sayisi" class="form-label">Soru Sayısı</label>
                <input type="number" class="form-control" id="soru_sayisi" name="soru_sayisi" value="1" min="1" max="5" required>
            </div>

            <button type="submit" class="btn btn-primary" <?php if (empty($dersler)) echo 'disabled'; ?>>
                <i class="fas fa-robot"></i> Soru Oluştur
            </button>
        </form>
    </div>
</div>

<div id="sonucAlani" class="mt-4">
    <!-- Yapay zeka tarafından oluşturulan sorular burada gösterilecek -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('ders').addEventListener('change', function() {
        var dersId = this.value;
        var konuSelect = document.getElementById('konu');
        konuSelect.innerHTML = '<option value="">Yükleniyor...</option>';
        konuSelect.disabled = true;

        if (dersId) {
            fetch('get_konular.php?ders_id=' + dersId)
                .then(response => response.json())
                .then(data => {
                    konuSelect.innerHTML = '<option value="" selected disabled>Lütfen bir konu seçin</option>';
                    if(data.length > 0) {
                        data.forEach(function(konu) {
                            var option = document.createElement('option');
                            option.value = konu.id;
                            option.textContent = konu.konu_adi;
                            konuSelect.appendChild(option);
                        });
                        konuSelect.disabled = false;
                    } else {
                         konuSelect.innerHTML = '<option value="" disabled>Bu derse ait konu bulunamadı.</option>';
                    }
                })
                .catch(error => {
                    console.error('Konular yüklenirken hata oluştu:', error);
                    konuSelect.innerHTML = '<option value="">Konu yüklenemedi</option>';
                });
        } else {
            konuSelect.innerHTML = '<option value="">Önce bir ders seçin</option>';
        }
    });

    document.getElementById('soruOlusturForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var submitButton = this.querySelector('button[type="submit"]');
        var sonucAlani = document.getElementById('sonucAlani');

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Oluşturuluyor...';
        sonucAlani.innerHTML = '<div class="alert alert-info">Yapay zeka sizin için soru hazırlıyor... Lütfen bekleyin.</div>';

        fetch('yapay_zeka_soru_getir.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            sonucAlani.innerHTML = data;
        })
        .catch(error => {
            console.error('Hata:', error);
            sonucAlani.innerHTML = '<div class="alert alert-danger">Bir hata oluştu. Lütfen tekrar deneyin.</div>';
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-robot"></i> Soru Oluştur';
        });
    });
});
</script>

<?php 
// Bu sayfa Bootstrap kullandığı için özel bir footer çağırıyoruz.
include '../includes/footer_bootstrap.php'; 
?>
