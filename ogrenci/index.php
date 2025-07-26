<?php
session_start();
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'ogrenci') {
    header('Location: dashboard.php');
    exit;
}
require_once '../includes/header.php';
?>
<main>
<div class="container">
    <div class="row" style="margin-top: 50px;">
        <div class="col s12 m6 offset-m3">
            <div class="card">
                <div class="card-content">
                    <span class="card-title center-align">Öğrenci Girişi</span>
                    <?php if(isset($_GET['error'])): ?>
                        <div class="card-panel red lighten-2 white-text"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>
                    <form action="login.php" method="POST">
                        <div class="input-field">
                            <i class="material-icons prefix">person</i>
                            <input type="text" id="username" name="username" required>
                            <label for="username">Kullanıcı Adı</label>
                        </div>
                        <div class="input-field">
                            <i class="material-icons prefix">lock</i>
                            <input type="password" id="password" name="password" required>
                            <label for="password">Şifre</label>
                        </div>
                        <div class="input-field center-align">
                            <button type="submit" class="btn waves-effect waves-light teal">Giriş Yap</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
<?php require_once '../includes/footer.php'; ?>
