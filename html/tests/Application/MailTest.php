<?php
declare(strict_types=1);

namespace Tests\Application;

use PHPUnit\Framework\TestCase;
use Application\Mail;
use PDO;

final class MailTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $dsn = "pgsql:host=" . getenv('DB_TEST_HOST') . ";dbname=" . getenv('DB_TEST_NAME');

        $this->pdo = new PDO(
            $dsn,
            getenv('DB_USER'),
            getenv('DB_PASS'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $this->pdo->exec("DROP TABLE IF EXISTS mail;");
        $this->pdo->exec("
            CREATE TABLE mail (
                id SERIAL PRIMARY KEY,
                subject TEXT NOT NULL,
                body TEXT NOT NULL
            );
        ");
    }

    public function testCreateMail(): void
    {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Alice", "Hello world");

        $this->assertIsInt($id);
        $this->assertSame(1, $id);
    }

    public function testGetMailReturnsRow(): void
    {
        $mail = new Mail($this->pdo);

        $id = $mail->createMail("Test Subject", "Test Body");
        $row = $mail->getMail($id);

        $this->assertIsArray($row);
        $this->assertSame($id, (int)$row['id']);
        $this->assertSame("Test Subject", $row['subject']);
        $this->assertSame("Test Body", $row['body']);
    }

    public function testGetMailReturnsFalseWhenMissing(): void
    {
        $mail = new Mail($this->pdo);

        $row = $mail->getMail(999);

        $this->assertFalse($row);
    }

    public function testGetAllMailReturnsAllRows(): void
    {
        $mail = new Mail($this->pdo);

        $mail->createMail("S1", "B1");
        $mail->createMail("S2", "B2");

        $rows = $mail->getAllMail();

        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertSame("S1", $rows[0]['subject']);
        $this->assertSame("S2", $rows[1]['subject']);
    }

    public function testUpdateMailUpdatesRow(): void
    {
        $mail = new Mail($this->pdo);

        $id = $mail->createMail("Old Subject", "Old Body");
        $updated = $mail->updateMail($id, "New Subject", "New Body");

        $this->assertIsArray($updated);
        $this->assertSame($id, (int)$updated['id']);
        $this->assertSame("New Subject", $updated['subject']);
        $this->assertSame("New Body", $updated['body']);
    }

    public function testUpdateMailReturnsFalseWhenMissing(): void
    {
        $mail = new Mail($this->pdo);

        $updated = $mail->updateMail(999, "X", "Y");

        $this->assertFalse($updated);
    }
    
    public function testDeleteMailDeletesRow(): void
    {
        $mail = new Mail($this->pdo);

        $id = $mail->createMail("S", "B");
        $ok = $mail->deleteMail($id);

        $this->assertTrue($ok);
        $this->assertFalse($mail->getMail($id));
    }

    public function testDeleteMailReturnsFalseWhenMissing(): void
    {
        $mail = new Mail($this->pdo);

        $ok = $mail->deleteMail(999);

        $this->assertFalse($ok);
    }




}
