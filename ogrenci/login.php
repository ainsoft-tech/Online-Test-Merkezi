<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header('Location: index.php?error=Kullanıcı adı ve şifre gerekli.');
        exit;
    }

    try {
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = :username AND role = 'ogrenci'");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: dashboard.php');
            exit;
        } else {
            header('Location: index.php?error=Geçersiz kullanıcı adı veya şifre.');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: index.php?error=Veritabanı hatası oluştu.');
        exit;
    }
}
?>