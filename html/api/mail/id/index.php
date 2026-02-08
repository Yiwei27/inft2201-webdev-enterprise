<?php
declare(strict_types=1);

require __DIR__ . '/../../../vendor/autoload.php';

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

// /api/mail/123  -> get last piece
$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($uri, '/'));
$id = (int) end($parts);

if ($id <= 0) {
    $page->badRequest();
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $item = $mail->getMail($id);
    if (!$item) {
        $page->notFound();
        exit;
    }
    $page->item($item);
    exit;
}

if ($method === 'PUT') {
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

    $updated = $mail->updateMail($id, $subject, $body);
    if (!$updated) {
        $page->notFound();
        exit;
    }

    $page->item($updated);
    exit;
}

if ($method === 'DELETE') {
    $deleted = $mail->deleteMail($id);
    if (!$deleted) {
        $page->notFound();
        exit;
    }

    http_response_code(200);
    echo json_encode(["message" => "Deleted"]);
    exit;
}

$page->badRequest();
