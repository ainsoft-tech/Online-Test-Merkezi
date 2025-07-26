<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Yetkisiz erişim. Lütfen giriş yapın.</div>';
    exit();
}

require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once '../config/exam_vt.php';
require_once '../config/gemini_api_key.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<div class="alert alert-danger">Geçersiz istek yöntemi.</div>';
    exit();
}

$ders_id = $_POST['ders_id'] ?? null;
$konu_id = $_POST['konu_id'] ?? null;
$zorluk = $_POST['zorluk'] ?? 'orta';
$soru_sayisi = (int)($_POST['soru_sayisi'] ?? 1);
$secenek_sayisi = (int)($_POST['secenek_sayisi'] ?? 4);

if (!$ders_id || !$konu_id || $soru_sayisi < 1) {
    echo '<div class="alert alert-danger">Eksik veya geçersiz parametreler.</div>';
    exit();
}

$exam = new Exam();
$ders = $exam->getDersById($ders_id);
$konu = $exam->getKonuById($konu_id);

if (!$ders || !$konu) {
    echo '<div class="alert alert-danger">Ders veya konu bulunamadı.</div>';
    exit();
}

try {
    $client = Gemini::client(GEMINI_API_KEY);

    $secenek_harfleri = ['a', 'b', 'c', 'd', 'e'];
    $istenen_secenekler = array_slice($secenek_harfleri, 0, $secenek_sayisi);
    
    $json_example_secenekler = [];
    foreach($istenen_secenekler as $harf) {
        $json_example_secenekler[$harf] = strtoupper($harf) . " seçeneği";
    }

    $json_example = json_encode([
        "sorular" => [
            [
                "soru_metni" => "Buraya soru metni gelecek",
                "secenekler" => $json_example_secenekler,
                "dogru_cevap" => "a"
            ]
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);


    $prompt = "
        Lütfen bana Türkiye'deki lise müfredatına uygun, '" . htmlspecialchars($ders['ders_adi']) . "' dersinin '" . htmlspecialchars($konu['konu_adi']) . "' konusuyla ilgili, zorluk seviyesi '" . htmlspecialchars($zorluk) . "' olan " . $soru_sayisi . " adet çoktan seçmeli soru hazırla.
        Her soru için " . $secenek_sayisi . " adet seçenek sunmalısın.
        Cevabı aşağıdaki gibi birebir JSON formatında, başka hiçbir ek metin olmadan ver:
        $json_example
        Eğer " . $soru_sayisi . " adetten fazla soru üreteceksen, her birini \"sorular\" dizisi içinde ayrı birer nesne olarak ekle.
    ";

    $result = $client->generativeModel('gemini-1.5-flash')->generateContent($prompt);
    $responseText = $result->text();
    
    // API'den gelen cevabı temizle
    $jsonString = str_replace(['```json', '```'], '', $responseText);
    $data = json_decode(trim($jsonString), true);

    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['sorular'])) {
        echo '<div class="alert alert-warning">Yapay zekadan geçerli bir formatta cevap alınamadı. Lütfen tekrar deneyin. Gelen Ham Cevap: <pre>' . htmlspecialchars($responseText) . '</pre></div>';
        exit();
    }

    // Soruları adminin onayı için form içinde göster
    echo '<h3>Oluşturulan Sorular</h3>';
    echo '<p>Lütfen aşağıdaki yapay zeka tarafından oluşturulmuş soruları kontrol edip veritabanına kaydedin.</p>';

    foreach ($data['sorular'] as $index => $soruData) {
        ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Soru <?php echo $index + 1; ?></h5>
                <form action="yapay_zeka_soru_kaydet.php" method="POST" class="soru-kaydet-form">
                    <input type="hidden" name="ders_id" value="<?php echo htmlspecialchars($ders_id); ?>">
                    <input type="hidden" name="konu_id" value="<?php echo htmlspecialchars($konu_id); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Soru Metni</label>
                        <textarea class="form-control" name="soru_metni" rows="4" required><?php echo htmlspecialchars($soruData['soru_metni']); ?></textarea>
                    </div>
                    
                    <?php 
                    $secenekler = $soruData['secenekler'];
                    $dogru_cevap_key = strtolower($soruData['dogru_cevap']);
                    ?>

                    <div class="row">
                        <?php foreach ($secenekler as $key => $value): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Seçenek <?php echo strtoupper($key); ?></label>
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="dogru_cevap" value="<?php echo $key; ?>" <?php echo ($key === $dogru_cevap_key) ? 'checked' : ''; ?> required>
                                </div>
                                <input type="text" class="form-control" name="secenek[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($value); ?>" required>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" name="soru_kaydet" class="btn btn-success">
                        <i class="fas fa-save"></i> Bu Soruyu Kaydet
                    </button>
                </form>
            </div>
        </div>
        <?php
    }

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Yapay zeka servisine bağlanırken bir hata oluştu: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
