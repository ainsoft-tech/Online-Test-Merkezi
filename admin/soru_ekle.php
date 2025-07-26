<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

$db = get_db_connection();

// Fetch categories for the dropdown
try {
    $stmt = $db->query("SELECT id, ders_adi FROM dersler ORDER BY ders_adi");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Dersler alınamadı: " . $e->getMessage();
    $categories = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ders_id = $_POST['ders_id'] ?? null;
    $konu_id = $_POST['konu_id'] ?? null;
    $option_count = $_POST['option_count'] ?? 0;
    $correct_answer = $_POST['correct_answer'] ?? null;
    $created_by = $_SESSION['user_id'];
    $pasted_image_data = $_POST['pasted_image_data'] ?? '';
    $question_image_name = '';

    // Basic validation
    if (empty($ders_id) || empty($konu_id) || empty($option_count) || empty($correct_answer)) {
        $error = "Lütfen ders, konu ve doğru cevap gibi tüm zorunlu alanları doldurun.";
    } elseif (empty($_FILES['question_image']['name']) && empty($pasted_image_data)) {
        $error = "Lütfen bir soru görseli seçin veya panodan yapıştırın.";
    } else {
        $db->beginTransaction();
        try {
            // 1. Handle Image Upload (Pasted or File)
            $target_dir_q = "../uploads/questions/";

            if (!empty($pasted_image_data)) {
                // Handle pasted image
                if (preg_match('/^data:image\/(\w+);base64,/', $pasted_image_data, $type)) {
                    $pasted_image_data = substr($pasted_image_data, strpos($pasted_image_data, ',') + 1);
                    $type = strtolower($type[1]); // jpg, png, gif

                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                        throw new Exception('Geçersiz resim formatı. Sadece JPG, PNG, GIF kabul edilir.');
                    }
                    $pasted_image_data = base64_decode($pasted_image_data);
                    if ($pasted_image_data === false) {
                        throw new Exception('Base64 kod çözme hatası.');
                    }
                } else {
                    throw new Exception('Geçersiz Base64 veri formatı.');
                }
                
                $question_image_name = time() . '.' . $type;
                $target_file_q = $target_dir_q . $question_image_name;
                if (!file_put_contents($target_file_q, $pasted_image_data)) {
                    throw new Exception("Panodan yapıştırılan görsel kaydedilemedi.");
                }
            } else {
                // Handle file upload
                $question_image_name = time() . '_' . basename($_FILES["question_image"]["name"]);
                $target_file_q = $target_dir_q . $question_image_name;
                if (!move_uploaded_file($_FILES["question_image"]["tmp_name"], $target_file_q)) {
                    throw new Exception("Soru görseli yüklenemedi. Hata kodu: " . $_FILES["question_image"]['error']);
                }
            }

            // 2. Insert into questions table
            $stmt_q = $db->prepare(
                "INSERT INTO questions (image_path, option_count, correct_answer, ders_id, konu_id, created_by, created_at) " .
                "VALUES (:image_path, :option_count, :correct_answer, :ders_id, :konu_id, :created_by, :created_at)"
            );
            $stmt_q->execute([
                ':image_path' => $question_image_name,
                ':option_count' => $option_count,
                ':correct_answer' => $correct_answer,
                ':ders_id' => $ders_id,
                ':konu_id' => $konu_id,
                ':created_by' => $created_by,
                ':created_at' => date('Y-m-d H:i:s')
            ]);
            $question_id = $db->lastInsertId();

            // 3. Handle Options Insertion
            $option_letters = ['A', 'B', 'C', 'D', 'E'];
            for ($i = 1; $i <= $option_count; $i++) {
                $option_text = $option_letters[$i - 1];
                $stmt_o = $db->prepare(
                    "INSERT INTO options (question_id, option_number, option_text) VALUES (:question_id, :option_number, :option_text)"
                );
                $stmt_o->execute([
                    ':question_id' => $question_id,
                    ':option_number' => $i,
                    ':option_text' => $option_text
                ]);
            }

            $db->commit();
            header("Location: soru_yonetimi.php?success=Soru başarıyla eklendi.");
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $error = "Bir hata oluştu: " . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
?>
<main>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <?php include_once '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="col s12 m9 l10">
            <div class="container">
                <div class="row" style="margin-top: 20px;">
                    <div class="col s12">
                        <div class="card">
                            <div class="card-content">
                                <span class="card-title">Yeni Soru Ekle</span>
                                <?php if (isset($error)): ?>
                                    <div class="card-panel red lighten-2 white-text"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                                <form action="soru_ekle.php" method="POST" enctype="multipart/form-data">
                                    <!-- Category and Subject -->
                                    <div class="row">
                                        <div class="input-field col s12 m6">
                                            <select id="category_id" name="ders_id" required>
                                                <option value="" disabled selected>Ders Seçiniz</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['ders_adi']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label>Ders</label>
                                        </div>
                                        <div class="input-field col s12 m6">
                                            <select id="konu_id" name="konu_id" required>
                                                <option value="" disabled selected>Önce Ders Seçiniz</option>
                                            </select>
                                            <label>Konu</label>
                                        </div>
                                    </div>
                                    
                                    <!-- File Upload -->
                                    <div class="row">
                                        <div class="file-field input-field col s12">
                                            <div class="btn teal">
                                                <span>Soru Görseli Yükle</span>
                                                <input type="file" name="question_image" accept="image/*">
                                            </div>
                                            <div class="file-path-wrapper">
                                                <input class="file-path validate" type="text">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="center-align" style="margin: 10px 0;">
                                        <p><strong>VEYA</strong></p>
                                    </div>

                                    <!-- Paste Area -->
                                    <div id="paste_area" style="border: 2px dashed #ccc; padding: 20px; text-align: center; cursor: pointer; border-radius: 5px;">
                                        <i class="material-icons large grey-text">content_paste</i>
                                        <p>Soru görselini buraya yapıştırın (Ctrl+V)</p>
                                    </div>
                                    <div class="center-align" style="margin-top:15px;">
                                         <img id="pasted_image_preview" src="" alt="Pano Önizleme" style="max-width: 100%; max-height: 250px; display: none; border: 1px solid #ddd; padding: 5px;">
                                    </div>
                                    <input type="hidden" name="pasted_image_data" id="pasted_image_data">


                                    <!-- Option Count Selector -->
                                    <div class="row" style="margin-top: 20px;">
                                        <div class="input-field col s12">
                                            <select id="option_count_selector" name="option_count">
                                                <option value="4" selected>4 Seçenek</option>
                                                <option value="5">5 Seçenek</option>
                                            </select>
                                            <label>Seçenek Sayısı</label>
                                        </div>
                                    </div>

                                    <!-- Dynamic Options Area -->
                                    <div id="options_area"></div>

                                    <div class="input-field right-align" style="margin-top: 20px;">
                                        <a href="soru_yonetimi.php" class="btn waves-effect waves-light grey">İptal</a>
                                        <button type="submit" class="btn waves-effect waves-light teal">Kaydet</button>
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
    
    // Paste functionality
    const pasteArea = document.getElementById('paste_area');
    const preview = document.getElementById('pasted_image_preview');
    const hiddenDataInput = document.getElementById('pasted_image_data');
    const fileInput = document.querySelector('input[type="file"][name="question_image"]');
    const filePathInput = document.querySelector('.file-path');

    pasteArea.addEventListener('paste', function(e) {
        e.preventDefault();
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (const item of items) {
            if (item.type.indexOf('image') === 0) {
                const blob = item.getAsFile();
                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                    hiddenDataInput.value = event.target.result;
                    
                    // Clear file input to prioritize pasted image
                    fileInput.value = '';
                    filePathInput.value = 'Panodan resim yapıştırıldı.';
                    pasteArea.style.borderColor = '#4CAF50'; // Green border on success
                };
                reader.readAsDataURL(blob);
            }
        }
    });

    // If user selects a file, clear the pasted image
    fileInput.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            preview.src = '';
            preview.style.display = 'none';
            hiddenDataInput.value = '';
            pasteArea.style.borderColor = '#ccc'; // Reset border
        }
    });

    // Fetch subjects based on category
    dersSelect.addEventListener('change', function() {
        const dersId = this.value;
        konuSelect.innerHTML = '<option value="" disabled selected>Yükleniyor...</option>';
        M.FormSelect.init(konuSelect);

        if (dersId) {
            fetch(`get_konular.php?ders_id=${dersId}`)
                .then(response => response.json())
                .then(data => {
                    konuSelect.innerHTML = '<option value="" disabled selected>Konu Seçiniz</option>';
                    data.forEach(konu => {
                        konuSelect.innerHTML += `<option value="${konu.id}">${konu.konu_adi}</option>`;
                    });
                    M.FormSelect.init(konuSelect); // Re-initialize select
                })
                .catch(error => {
                    console.error('Konular alınırken hata oluştu:', error);
                    konuSelect.innerHTML = '<option value="" disabled selected>Konu alınamadı</option>';
                    M.FormSelect.init(konuSelect);
                });
        }
    });

    // Generate radio buttons for correct answer
    function generateOptions(count) {
        optionsArea.innerHTML = ''; // Clear previous options
        let content = '<div class="row"><p style="margin-bottom: 15px;"><strong>Doğru Cevap</strong> (Lütfen doğru seçeneği işaretleyin)</p></div>';
        content += '<div class="row">';
        for (let i = 1; i <= count; i++) {
            content += `
                <div class="col s6 m3 l2">
                    <label>
                        <input name="correct_answer" type="radio" value="${i}" required />
                        <span>${String.fromCharCode(64 + i)})</span>
                    </label>
                </div>
            `;
        }
        content += '</div>';
        optionsArea.innerHTML = content;
    }

    // Initial generation and update on change
    generateOptions(selector.value);
    selector.addEventListener('change', function() {
        generateOptions(this.value);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
