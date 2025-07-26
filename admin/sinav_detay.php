<?php
require_once '../config/database.php';
require_once '../includes/header.php';

$db = get_db_connection();
$sinav_id = $_GET['id'] ?? null;

if (!$sinav_id) {
    header("Location: sinav_yonetimi.php");
    exit();
}

// Soru Ekleme İşlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['soru_ekle'])) {
    $secenek_sayisi = $_POST['secenek_sayisi'];
    $dogru_cevap = $_POST['dogru_cevap'];
    $soru_gorseli = null;

    if (!empty($_POST['pasted_soru_gorseli_base64'])) {
        $base64_image = $_POST['pasted_soru_gorseli_base64'];
        // Data URI'den sadece base64 kısmını al
        list($type, $base64_image) = explode(';base64,', $base64_image);
        $image_data = base64_decode($base64_image);
        
        $target_dir = "../uploads/questions/";
        $file_extension = '.png'; // Varsayılan olarak png, isterseniz type'dan alabilirsiniz
        if (strpos($type, 'image/jpeg') !== false) $file_extension = '.jpg';
        else if (strpos($type, 'image/gif') !== false) $file_extension = '.gif';

        $soru_gorseli = time() . $file_extension;
        $target_file = $target_dir . $soru_gorseli;
        file_put_contents($target_file, $image_data);

    } else if (isset($_FILES['soru_gorseli']) && $_FILES['soru_gorseli']['error'] == 0) {
        $target_dir = "../uploads/questions/";
        $soru_gorseli = time() . '_' . basename($_FILES["soru_gorseli"]["name"]);
        $target_file = $target_dir . $soru_gorseli;
        move_uploaded_file($_FILES["soru_gorseli"]["tmp_name"], $target_file);
    } else {
        // Ne dosya yüklendi ne de panodan yapıştırıldıysa hata ver
        // Bu durum, required attribute kaldırıldığı için oluşabilir
        // Şimdilik boş bırakıyorum, isterseniz hata mesajı ekleyebilirsiniz.
    }

    $stmt = $db->prepare("INSERT INTO sinav_sorulari (sinav_id, soru_gorseli, secenek_sayisi, dogru_cevap) VALUES (?, ?, ?, ?)");
    $stmt->execute([$sinav_id, $soru_gorseli, $secenek_sayisi, $dogru_cevap]);
    header("Location: sinav_detay.php?id=" . $sinav_id);
    exit();
}


// Sınav bilgilerini al
$stmt_sinav = $db->prepare("SELECT * FROM sinavlar WHERE id = ?");
$stmt_sinav->execute([$sinav_id]);
$sinav = $stmt_sinav->fetch(PDO::FETCH_ASSOC);

if (!$sinav) {
    header("Location: sinav_yonetimi.php");
    exit();
}

// Sınava ait soruları al
$stmt_sorular = $db->prepare("SELECT * FROM sinav_sorulari WHERE sinav_id = ? ORDER BY id DESC");
$stmt_sorular->execute([$sinav_id]);
$sorular = $stmt_sorular->fetchAll(PDO::FETCH_ASSOC);

?>

<main>
<div class="container-fluid">
    <div class="row">
        <?php include_once '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col s12 m9 l10">
            <div class="container">
                <h1 class="mt-4">Sınav Detayları</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="sinav_yonetimi.php">Sınav Yönetimi</a></li>
                    <li class="breadcrumb-item active">Sınav Detayları</li>
                </ol>

                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-info-circle mr-1"></i>
                        Sınav Bilgileri
                    </div>
                    <div class="card-body">
                        <h5>Sınav Adı: <?= htmlspecialchars($sinav['sinav_adi']) ?></h5>
                        <p>Açıklama: <?= htmlspecialchars($sinav['aciklama']) ?></p>
                        <p>Başlangıç: <?= htmlspecialchars($sinav['baslangic_tarihi']) ?></p>
                        <p>Bitiş: <?= htmlspecialchars($sinav['bitis_tarihi']) ?></p>
                        <p>Durum: <?= $sinav['yayin_durumu'] == 1 ? '<span class="badge badge-success">Yayınlandı</span>' : '<span class="badge badge-warning">Taslak</span>' ?></p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-question-circle mr-1"></i>
                        Sınav Soruları
                        <a href="#yeni-soru-ekle" class="btn btn-primary btn-sm right">Yeni Soru Ekle</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Görsel</th>
                                        <th>Doğru Cevap</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sorular as $soru): ?>
                                        <tr>
                                            <td>
                                    <?php if ($soru['soru_gorseli']): ?>
                                        <img src="../uploads/questions/<?= htmlspecialchars($soru['soru_gorseli']) ?>" alt="Soru Görseli" width="150">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($soru['dogru_cevap']) ?></td>
                                            <td>
                                                <a href="#" class="btn btn-warning btn-sm">Düzenle</a>
                                                <a href="#" class="btn btn-danger btn-sm">Sil</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="yeni-soru-form-container" class="card mb-4" style="display: none;">
                    <div class="card-header">
                        <i class="fas fa-plus-square mr-1"></i>
                        Yeni Soru Ekle
                    </div>
                    <div class="card-body">
                        <form action="sinav_detay.php?id=<?= $sinav_id ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="soru_ekle" value="1">
                <div class="form-group">
                    <label for="soru_gorseli">Soru Görseli Yükle (Dosya Seç)</label>
                    <input type="file" name="soru_gorseli" id="soru_gorseli" class="form-control-file">
                    <small class="form-text text-muted">Veya aşağıdaki kutuya görseli yapıştırın.</small>
                    <div id="paste_area" style="border: 2px dashed #ccc; padding: 20px; text-align: center; margin-top: 10px; min-height: 100px; cursor: text;">
                        Görseli buraya yapıştırın (Ctrl+V)
                    </div>
                    <input type="hidden" name="pasted_soru_gorseli_base64" id="pasted_soru_gorseli_base64">
                    <div id="pasted_image_container" style="margin-top: 10px; display: none;">
                        <p>Yapıştırılan Görsel Önizlemesi:</p>
                        <img id="pasted_image_preview" src="" alt="Yapıştırılan Görsel" style="max-width: 200px; border: 1px solid #ddd; padding: 5px;">
                        <button type="button" class="btn btn-sm btn-danger" id="clear_pasted_image" style="margin-left: 10px;">Temizle</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="secenek_sayisi">Seçenek Sayısı</label>
                    <select name="secenek_sayisi" id="secenek_sayisi" class="form-control" onchange="toggleDogruCevapE()">
                        <option value="4">4 Seçenekli</option>
                        <option value="5">5 Seçenekli</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Doğru Cevap</label>
                    <div class="correct-answer-options">
                        <label><input name="dogru_cevap" type="radio" value="A" required/><span>A</span></label>
                        <label><input name="dogru_cevap" type="radio" value="B" /><span>B</span></label>
                        <label><input name="dogru_cevap" type="radio" value="C" /><span>C</span></label>
                        <label><input name="dogru_cevap" type="radio" value="D" /><span>D</span></label>
                        <label id="dogru-cevap-e-label" style="display: none;"><input name="dogru_cevap" type="radio" value="E" /><span>E</span></label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Soruyu Kaydet</button>
            </form>
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
    var instances = M.FormSelect.init(elems);

    document.querySelector('a[href="#yeni-soru-ekle"]').addEventListener('click', function(e) {
        e.preventDefault();
        var formContainer = document.getElementById('yeni-soru-form-container');
        if (formContainer.style.display === 'none') {
            formContainer.style.display = 'block';
        } else {
            formContainer.style.display = 'none';
        }
    });

    // Initial call to set the correct state for E option
    toggleDogruCevapE();
});

function toggleDogruCevapE() {
    var secenekSayisi = document.getElementById('secenek_sayisi').value;
    var dogruCevapELabel = document.getElementById('dogru-cevap-e-label');

    if (secenekSayisi == 5) {
        dogruCevapELabel.style.display = 'inline-block';
    } else {
        dogruCevapELabel.style.display = 'none';
    }
}

document.getElementById('paste_area').addEventListener('paste', function(e) {
    var items = e.clipboardData.items;
    for (var i = 0; i < items.length; i++) {
        if (items[i].type.indexOf("image") !== -1) {
            var blob = items[i].getAsFile();
            var reader = new FileReader();
            reader.onload = function(event) {
                var base64data = event.target.result;
                document.getElementById('pasted_soru_gorseli_base64').value = base64data;
                document.getElementById('pasted_image_preview').src = base64data;
                document.getElementById('pasted_image_container').style.display = 'block';
                document.getElementById('soru_gorseli').value = ''; // Dosya inputunu temizle
            };
            reader.readAsDataURL(blob);
            e.preventDefault(); // Varsayılan yapıştırma davranışını engelle
            break;
        }
    }
});

document.getElementById('clear_pasted_image').addEventListener('click', function() {
    document.getElementById('pasted_soru_gorseli_base64').value = '';
    document.getElementById('pasted_image_preview').src = '';
    document.getElementById('pasted_image_container').style.display = 'none';
});

document.getElementById('soru_gorseli').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('pasted_soru_gorseli_base64').value = '';
        document.getElementById('pasted_image_preview').src = '';
        document.getElementById('pasted_image_container').style.display = 'none';
    }
});

// Form gönderilmeden önce görsel kontrolü
document.querySelector('form').addEventListener('submit', function(e) {
    var fileInput = document.getElementById('soru_gorseli');
    var pastedInput = document.getElementById('pasted_soru_gorseli_base64');

    if (!fileInput.value && !pastedInput.value) {
        alert('Lütfen bir soru görseli yükleyin veya yapıştırın.');
        e.preventDefault(); // Formu göndermeyi engelle
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
