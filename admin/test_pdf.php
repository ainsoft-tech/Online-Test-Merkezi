<?php
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php'; // Composer autoload

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Yetkisiz erişim.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Geçersiz test ID.");
}

$exam_id = $_GET['id'];

try {
    $db = get_db_connection();

    // Fetch exam details
    $stmt_exam = $db->prepare("SELECT id, title, description FROM exams WHERE id = :id");
    $stmt_exam->bindParam(':id', $exam_id, PDO::PARAM_INT);
    $stmt_exam->execute();
    $exam = $stmt_exam->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        die("Test bulunamadı.");
    }

    // Fetch questions for this exam
    $stmt_questions = $db->prepare(
        "SELECT q.id, q.image_path, q.option_count, eq.question_order " .
        "FROM exam_questions eq " .
        "JOIN questions q ON eq.question_id = q.id " .
        "WHERE eq.exam_id = :exam_id ORDER BY eq.question_order"
    );
    $stmt_questions->bindParam(':exam_id', $exam_id, PDO::PARAM_INT);
    $stmt_questions->execute();
    $exam_questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// --- PDF Creation with Dompdf ---

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isPhpEnabled', true); // IMPORTANT: Enable PHP for page numbers
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

// Split questions into two columns
$question_count = count($exam_questions);
$midpoint = ceil($question_count / 2);
$column1_questions = array_slice($exam_questions, 0, $midpoint);
$column2_questions = array_slice($exam_questions, $midpoint);

// Function to generate HTML for a set of questions
function generate_question_html($questions) {
    $html = '';
    foreach ($questions as $question) {
        $html .= '<div class="question">';
        $html .= '<h4>' . htmlspecialchars($question['question_order']) . '.</h4>';

        $image_path = '../uploads/questions/' . $question['image_path'];
        if (file_exists($image_path)) {
            $type = pathinfo($image_path, PATHINFO_EXTENSION);
            $data = file_get_contents($image_path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $html .= '<img src="' . $base64 . '" class="question-image">';
        } else {
            $html .= '<p>[Görsel Yok]</p>';
        }
        $html .= '</div>';
    }
    return $html;
}

// Build HTML content for the PDF
$html = '<html><head><style>';
$html .= '@page { margin: 20px 25px; }'; // Add some horizontal margin
$html .= 'body { font-family: DejaVu Sans, sans-serif; }';
$html .= 'h1 { text-align: center; font-size: 16px; margin-bottom: 5px; color: #377D22}';
$html .= 'h4 { font-size: 14px; margin-bottom: 5px; }';
$html .= '.header-table { width: 100%; margin-bottom: 10px; }';
$html .= '.header-table td { vertical-align: middle; }';
$html .= '.description { font-size: 12px; text-align: right; color: #3282F6; }';
$html .= '.title { font-size: 16px; font-weight: bold; text-align: left; color: #EB3324; }';
$html .= '.question-table { width: 100%; border-collapse: collapse; }';
$html .= '.question-col { width: 50%; vertical-align: top; }';
$html .= '.col-left { padding-right: 10px; border-right: 1px solid #ccc; }';
$html .= '.col-right { padding-left: 10px; }';
$html .= '.question { page-break-inside: avoid; margin-bottom: 15px; }';
$html .= '.question-image { max-width: 100%; height: auto; margin-bottom: 5px; }';
$html .= 'hr { border: 0; border-top: 1px solid #ccc; margin: 5px 0; }';
$html .= '</style></head><body>';

// Page number script - using the correct method name
$html .= '<script type="text/php"> if (isset($pdf)) { $pdf->page_script(function($pageNumber, $pageCount, $canvas, $fontMetrics) { $text = "Sayfa $pageNumber / $pageCount"; $font = $fontMetrics->get_font("DejaVu Sans"); $size = 10; $y = $canvas->get_height() - 20; $x = $canvas->get_width() / 2 - ($fontMetrics->get_text_width($text, $font, $size) / 2); $canvas->text($x, $y, $text, $font, $size); }); } </script>';

// Header section
$html .= '<h1>Online Test Merkezi</h1>';
$html .= '<hr>';
$html .= '<table class="header-table"><tr>';
$html .= '<td class="title">' . htmlspecialchars($exam['title']) . '</td>';
$html .= '<td class="description">' . htmlspecialchars($exam['description']) . '</td>';
$html .= '</tr></table>';
$html .= '<hr>';

// Questions section
$html .= '<table class="question-table"><tr>';
$html .= '<td class="question-col col-left">';
$html .= generate_question_html($column1_questions);
$html .= '</td>';
$html .= '<td class="question-col col-right">';
$html .= generate_question_html($column2_questions);
$html .= '</td>';
$html .= '</tr></table>';

$html .= '</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream(htmlspecialchars($exam['title']) . '.pdf', ["Attachment" => 0]);

?>