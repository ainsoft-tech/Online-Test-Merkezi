<?php
require_once 'includes/header.php';
require_once 'includes/hero.php';
?>
<!-- Login Buttons -->
<div class="container">
    <div class="section">
        <div class="row center-align">
            <div class="col s12 m4">
                <a href="admin/" class="btn-large waves-effect waves-light teal darken-2" style="width: 100%; margin-bottom: 10px;"><i class="material-icons left">person</i>Admin Girişi</a>
            </div>
            <div class="col s12 m4">
                <a href="ogretmen/" class="btn-large waves-effect waves-light teal darken-1" style="width: 100%; margin-bottom: 10px;"><i class="material-icons left">school</i>Öğretmen Girişi</a>
            </div>
            <div class="col s12 m4">
                <a href="ogrenci/" class="btn-large waves-effect waves-light teal" style="width: 100%; margin-bottom: 10px;"><i class="material-icons left">face</i>Öğrenci Girişi</a>
            </div>
        </div>
    </div>
</div>
<?php

// These would typically come from your database
$subjectStats = [
    ['name' => 'Matematik', 'count' => 245, 'icon' => 'calculate'],
    ['name' => 'Türkçe', 'count' => 187, 'icon' => 'menu_book'],
    ['name' => 'Fen Bilimleri', 'count' => 156, 'icon' => 'science'],
    ['name' => 'Sosyal Bilgiler', 'count' => 132, 'icon' => 'public'],
    ['name' => 'İngilizce', 'count' => 98, 'icon' => 'translate'],
];

$totalQuestions = array_sum(array_column($subjectStats, 'count'));
$totalTests = 47;
$totalStudents = 328;
?>

<!-- Main Content -->
<main class="container">
    <div class="section">
        <!-- Stats Row -->
        <div class="row">
            <!-- Total Questions Card -->
            <div class="col s12 m4">
                <div class="card hoverable">
                    <div class="card-content center-align">
                        <i class="material-icons large teal-text text-darken-1">help_outline</i>
                        <h4 class="card-title">Toplam Soru</h4>
                        <h2 class="teal-text"><?php echo number_format($totalQuestions); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Total Tests Card -->
            <div class="col s12 m4">
                <div class="card hoverable">
                    <div class="card-content center-align">
                        <i class="material-icons large teal-text text-darken-1">assignment</i>
                        <h4 class="card-title">Toplam Test</h4>
                        <h2 class="teal-text"><?php echo number_format($totalTests); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Total Students Card -->
            <div class="col s12 m4">
                <div class="card hoverable">
                    <div class="card-content center-align">
                        <i class="material-icons large teal-text text-darken-1">people</i>
                        <h4 class="card-title">Toplam Öğrenci</h4>
                        <h2 class="teal-text"><?php echo number_format($totalStudents); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions by Subject -->
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Derslere Göre Soru Dağılımı</span>
                        <div class="progress" style="display: none;">
                            <div class="indeterminate"></div>
                        </div>
                        <div id="subject-stats">
                            <?php foreach ($subjectStats as $subject): ?>
                            <div class="row valign-wrapper" style="margin-bottom: 10px;">
                                <div class="col s2">
                                    <i class="material-icons teal-text"><?php echo $subject['icon']; ?></i>
                                    <span><?php echo $subject['name']; ?></span>
                                </div>
                                <div class="col s8">
                                    <div class="progress" style="margin: 0.5rem 0;">
                                        <?php 
                                        $percentage = ($subject['count'] / $totalQuestions) * 100;
                                        $percentage = round($percentage, 1);
                                        ?>
                                        <div class="determinate teal" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </div>
                                <div class="col s2 right-align">
                                    <span class="teal-text"><strong><?php echo $subject['count']; ?></strong></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>