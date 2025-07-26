<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Sınav Merkezi</title>
    <!-- Materialize CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f4f4f4;
        }
        main {
            flex: 1 0 auto;
        }
        .login-card {
            margin-top: 2rem;
            transition: box-shadow .25s;
        }
        .login-card:hover {
            box-shadow: 0 8px 17px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
        }
        .card-content i {
            font-size: 4rem;
        }
        .card-title {
            font-weight: 300;
        }
        .nav-wrapper {
            background-color: #26a69a; /* Teal */
        }
        .page-footer {
            background-color: #26a69a; /* Teal */
        }
        .hero {
            background: url('https://images.unsplash.com/photo-1501504905252-473c47e087f8?q=80&w=1974&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .hero h1 {
            font-size: 4rem;
            font-weight: 500;
        }
        .correct-answer-options label {
            margin-right: 30px; /* İstediğiniz boşluk miktarını ayarlayabilirsiniz */
        }
    </style>
</head>
<body>

<nav  style="background-color: #26a69a;">
    <div class="nav-wrapper container">
        <a href="index.php" class="brand-logo">Sınav Merkezi</a>
        <a href="#" data-target="mobile-demo" class="sidenav-trigger"><i class="material-icons">menu</i></a>
        <ul class="right hide-on-med-and-down">
            <li><a href="index.php">Ana Sayfa</a></li>
            <li><a href="about.php">Hakkımızda</a></li>
            <li><a href="contact.php">İletişim</a></li>
            <!-- Dropdown Trigger -->
            <li><a class="dropdown-trigger" href="#!" data-target="dropdown1">Giriş<i class="material-icons right">arrow_drop_down</i></a></li>
        </ul>
        <!-- Dropdown Structure -->
        <ul id="dropdown1" class="dropdown-content">
            <li><a href="../admin/index.php"><i class="material-icons tiny">supervisor_account</i> Yönetici Girişi</a></li>
            <li><a href="../ogretmen/index.php"><i class="material-icons tiny">school</i> Öğretmen Girişi</a></li>
            <li><a href="../ogrenci/index.php"><i class="material-icons tiny">face</i> Öğrenci Girişi</a></li>
        </ul>
    </div>
</nav>

<!-- Mobile Navigation -->
<ul class="sidenav" id="mobile-demo">
    <li><a href="index.php">Ana Sayfa</a></li>
    <li><a href="about.php">Hakkımızda</a></li>
    <li><a href="contact.php">İletişim</a></li>
    <li class="divider"></li>
    <li><a class="subheader">Giriş Yap</a></li>
    <li><a href="admin_login.php"><i class="material-icons tiny">supervisor_account</i> Yönetici</a></li>
    <li><a href="teacher_login.php"><i class="material-icons tiny">school</i> Öğretmen</a></li>
    <li><a href="student_login.php"><i class="material-icons tiny">face</i> Öğrenci</a></li>
</ul>

<!-- Add this script at the end of the body or in a separate JS file -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var dropdowns = document.querySelectorAll('.dropdown-trigger');
        M.Dropdown.init(dropdowns, {
            coverTrigger: false,
            constrainWidth: false
        });
        
        var sidenav = document.querySelectorAll('.sidenav');
        M.Sidenav.init(sidenav);
    });
</script>
