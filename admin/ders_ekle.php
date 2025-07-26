<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Yetkisiz erişim.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ders_adi = trim($_POST['ders_adi'] ?? '');
    $ders_grubu = trim($_POST['ders_grubu'] ?? '');
    $ders_kodu = trim($_POST['ders_kodu'] ?? '');

    if (empty($ders_adi) || empty($ders_grubu) || empty($ders_kodu)) {
        $error = "Tüm alanların doldurulması zorunludur.";
    } else {
        try {
            $db = get_db_connection();

            // Check if ders_kodu already exists
            $stmt_check = $db->prepare("SELECT id FROM dersler WHERE ders_kodu = :ders_kodu");
            $stmt_check->bindParam(':ders_kodu', $ders_kodu, PDO::PARAM_STR);
            $stmt_check->execute();

            if ($stmt_check->fetch()) {
                $error = "Bu ders kodu zaten mevcut. Lütfen farklı bir kod girin.";
            } else {
                $current_timestamp = date('Y-m-d H:i:s');

                $stmt = $db->prepare(
                    "INSERT INTO dersler (ders_adi, ders_grubu, ders_kodu, created_at, updated_at) " . 
                    "VALUES (:ders_adi, :ders_grubu, :ders_kodu, :created_at, :updated_at)"
                );
                
                $stmt->bindParam(':ders_adi', $ders_adi, PDO::PARAM_STR);
                $stmt->bindParam(':ders_grubu', $ders_grubu, PDO::PARAM_STR);
                $stmt->bindParam(':ders_kodu', $ders_kodu, PDO::PARAM_STR);
                $stmt->bindParam(':created_at', $current_timestamp, PDO::PARAM_STR);
                $stmt->bindParam(':updated_at', $current_timestamp, PDO::PARAM_STR);
                
                $stmt->execute();

                header("Location: ders_yonetimi.php?success=Ders başarıyla eklendi.");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Veritabanı hatası: " . $e->getMessage();
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
                                <span class="card-title">Yeni Ders Ekle</span>
                                <?php if (isset($error)): ?>
                                    <div class="card-panel red lighten-2 white-text"><?php echo $error; ?></div>
                                <?php endif; ?>
                                <form action="ders_ekle.php" method="POST">
                                    <div class="input-field">
                                        <i class="material-icons prefix">book</i>
                                        <input type="text" id="ders_adi" name="ders_adi" required value="<?php echo isset($_POST['ders_adi']) ? htmlspecialchars($_POST['ders_adi']) : ''; ?>">
                                        <label for="ders_adi">Ders Adı</label>
                                    </div>
                                    <div class="input-field">
                                        <i class="material-icons prefix">group_work</i>
                                        <input type="text" id="ders_grubu" name="ders_grubu" required value="<?php echo isset($_POST['ders_grubu']) ? htmlspecialchars($_POST['ders_grubu']) : ''; ?>">
                                        <label for="ders_grubu">Ders Grubu</label>
                                    </div>
                                    <div class="input-field">
                                        <i class="material-icons prefix">filter_9_plus</i>
                                        <input type="number" id="ders_kodu" name="ders_kodu" required value="<?php echo isset($_POST['ders_kodu']) ? htmlspecialchars($_POST['ders_kodu']) : ''; ?>">
                                        <label for="ders_kodu">Ders Kodu</label>
                                    </div>
                                    <div class="input-field right-align">
                                        <a href="ders_yonetimi.php" class="btn waves-effect waves-light grey">İptal</a>
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
<?php require_once '../includes/footer.php'; ?>
