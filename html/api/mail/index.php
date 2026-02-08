<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Application\Mail;
use Application\Page;

header('Content-Type: application/json; charset=utf-8');

$dsn = "pgsql:host=" . getenv('DB_PROD_HOST') . ";dbname=" . getenv('DB_PROD_NAME');

try {
    $pdo = new \PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$mail = new Mail($pdo);
$page = new Page();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $page->list($mail->getAllMail());
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data) || !isset($data['subject'], $data['body'])) {
        $page->badRequest();
        exit;
    }

    $subject = trim((string)$data['subject']);
    $body = trim((string)$data['body']);

    if ($subject === '' || $body === '') {
        $page->badRequest();
        exit;
    }

    $id = $mail->createMail($subject, $body);
    $page->item($mail->getMail((int)$id));
    exit;
}

$page->badRequest();
