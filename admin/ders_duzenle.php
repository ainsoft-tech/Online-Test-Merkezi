<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ders_yonetimi.php?error=Geçersiz ID.');
    exit;
}

$id = $_GET['id'];
$db = get_db_connection();

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ders_adi = trim($_POST['ders_adi'] ?? '');
    $ders_grubu = trim($_POST['ders_grubu'] ?? '');
    $ders_kodu = trim($_POST['ders_kodu'] ?? '');

    if (empty($ders_adi) || empty($ders_grubu) || empty($ders_kodu)) {
        $error = "Tüm alanların doldurulması zorunludur.";
    } else {
        try {
            // Check if ders_kodu already exists for another lesson
            $stmt_check = $db->prepare("SELECT id FROM dersler WHERE ders_kodu = :ders_kodu AND id != :id");
            $stmt_check->bindParam(':ders_kodu', $ders_kodu, PDO::PARAM_STR);
            $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt_check->execute();

            if ($stmt_check->fetch()) {
                $error = "Bu ders kodu zaten başka bir derse ait. Lütfen farklı bir kod girin.";
            } else {
                $current_timestamp = date('Y-m-d H:i:s');
                $stmt = $db->prepare(
                    "UPDATE dersler SET ders_adi = :ders_adi, ders_grubu = :ders_grubu, ders_kodu = :ders_kodu WHERE id = :id"
                );
                $stmt->bindParam(':ders_adi', $ders_adi, PDO::PARAM_STR);
                $stmt->bindParam(':ders_grubu', $ders_grubu, PDO::PARAM_STR);
                $stmt->bindParam(':ders_kodu', $ders_kodu, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                header("Location: ders_yonetimi.php?success=Ders başarıyla güncellendi.");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}

// Fetch the lesson data to populate the form
try {
    $stmt = $db->prepare("SELECT * FROM dersler WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lesson) {
        header('Location: ders_yonetimi.php?error=Ders bulunamadı.');
        exit;
    }
} catch (PDOException $e) {
    $error = "Veritabanı hatası: " . $e->getMessage();
    $lesson = []; // Ensure $lesson is an array to avoid errors in the form
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
                                <span class="card-title">Dersi Düzenle</span>
                                <?php if (isset($error)): ?>
                                    <div class="card-panel red lighten-2 white-text"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>
                                <form action="ders_duzenle.php?id=<?php echo $lesson['id']; ?>" method="POST">
                                    <div class="input-field">
                                        <i class="material-icons prefix">book</i>
                                        <input type="text" id="ders_adi" name="ders_adi" required value="<?php echo htmlspecialchars($lesson['ders_adi'] ?? ''); ?>">
                                        <label for="ders_adi" class="active">Ders Adı</label>
                                    </div>
                                    <div class="input-field">
                                        <i class="material-icons prefix">group_work</i>
                                        <input type="text" id="ders_grubu" name="ders_grubu" required value="<?php echo htmlspecialchars($lesson['ders_grubu'] ?? ''); ?>">
                                        <label for="ders_grubu" class="active">Ders Grubu</label>
                                    </div>
                                    <div class="input-field">
                                        <i class="material-icons prefix">filter_9_plus</i>
                                        <input type="number" id="ders_kodu" name="ders_kodu" required value="<?php echo htmlspecialchars($lesson['ders_kodu'] ?? ''); ?>">
                                        <label for="ders_kodu" class="active">Ders Kodu</label>
                                    </div>
                                    <div class="input-field right-align">
                                        <a href="ders_yonetimi.php" class="btn waves-effect waves-light grey">İptal</a>
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
<?php require_once '../includes/footer.php'; ?>
