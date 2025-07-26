
<!-- Sidebar -->
<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="col s12 m3 l2" style="padding: 0;">
    <div class="collection" style="height: calc(100vh - 64px); border-right: 1px solid #e0e0e0;">
        <a href="dashboard.php" class="collection-item waves-effect <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>"><i class="material-icons left">home</i>Ana Sayfa</a>
        <a href="ders_yonetimi.php" class="collection-item waves-effect <?php echo ($current_page == 'ders_yonetimi.php') ? 'active' : ''; ?>"><i class="material-icons left">book</i>Ders Yönetimi</a>
        <a href="konu_yonetimi.php" class="collection-item waves-effect <?php echo ($current_page == 'konu_yonetimi.php') ? 'active' : ''; ?>"><i class="material-icons left">folder</i>Konu Yönetimi</a>
        <a href="soru_yonetimi.php" class="collection-item waves-effect <?php echo ($current_page == 'soru_yonetimi.php') ? 'active' : ''; ?>"><i class="material-icons left">help_outline</i>Soru Yönetimi</a>
        <a href="yapay_zeka_soru_olustur.php" class="collection-item waves-effect <?php echo ($current_page == 'yapay_zeka_soru_olustur.php') ? 'active' : ''; ?>"><i class="material-icons left">smart_toy</i>Yapay Zeka İle Soru Oluştur</a>
        <a href="test_yonetimi.php" class="collection-item waves-effect <?php echo ($current_page == 'test_yonetimi.php') ? 'active' : ''; ?>"><i class="material-icons left">assignment</i>Test Oluşturma</a>
        <a href="sinav_yonetimi.php" class="collection-item waves-effect <?php echo ($current_page == 'sinav_yonetimi.php') ? 'active' : ''; ?>"><i class="material-icons left">create</i>Sınav Yönetimi</a>
        <a href="#" class="collection-item waves-effect"><i class="material-icons left
">people</i>Kullanıcı Yönetimi</a>
    </div>
</div>
    