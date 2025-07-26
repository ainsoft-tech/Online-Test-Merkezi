<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Bu sayfaya erişim yetkiniz yok.');
    exit;
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
                                <span class="card-title">Admin Paneli</span>
                                <p>Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                                <p>Bu alanda adminlere özel içerikler ve yönetim araçları yer alacaktır.</p>
                            </div>
                            <div class="card-action">
                                <a href="logout.php" class="btn red waves-effect waves-light">Çıkış Yap</a>
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
