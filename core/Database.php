<?php

declare(strict_types=1);

namespace Core;

use PDO;
use RuntimeException;

/**
 * Yapilandirilmis veritabani surucusu icin PDO baglantisi olusturur.
 */
final class Database
{
    private PDO $pdo;

    /**
     * @param array $config Veritabani baglanti ayarlari.
     */
    public function __construct(
        private readonly array $config
    ) {
        $driver = (string) ($this->config['driver'] ?? 'sqlite');
        $username = (string) ($this->config['username'] ?? '');
        $password = (string) ($this->config['password'] ?? '');
        $options = $this->config['options'] ?? [];

        $this->pdo = new PDO($this->dsn($driver), $username !== '' ? $username : null, $password !== '' ? $password : null, is_array($options) ? $options : []);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Hazirlanmis PDO nesnesini dondurur.
     *
     * @return PDO
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Yapilandirilmis surucu icin dogru DSN metnini olusturur.
     *
     * @param string $driver Veritabani surucu adi.
     * @return string
     */
    private function dsn(string $driver): string
    {
        return match ($driver) {
            'sqlite' => $this->sqliteDsn(),
            'mysql' => $this->mysqlDsn(),
            'pgsql', 'postgresql' => $this->pgsqlDsn(),
            default => throw new RuntimeException(sprintf('Unsupported database driver: %s', $driver)),
        };
    }

    /**
     * SQLite DSN'ini olusturur.
     *
     * @return string
     */
    private function sqliteDsn(): string
    {
        $databasePath = (string) (($this->config['sqlite_database'] ?? $this->config['database'] ?? ''));

        if ($databasePath === '') {
            throw new RuntimeException('SQLite database path is missing.');
        }

        return 'sqlite:' . $databasePath;
    }

    /**
     * MySQL DSN'ini olusturur.
     *
     * @return string
     */
    private function mysqlDsn(): string
    {
        $host = (string) ($this->config['host'] ?? '127.0.0.1');
        $port = (int) ($this->config['port'] ?? 3306);
        $database = (string) ($this->config['database'] ?? '');
        $charset = (string) ($this->config['charset'] ?? 'utf8mb4');

        if ($database === '') {
            throw new RuntimeException('MySQL database name is missing.');
        }

        return sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);
    }

    /**
     * PostgreSQL DSN'ini olusturur.
     *
     * @return string
     */
    private function pgsqlDsn(): string
    {
        $host = (string) ($this->config['host'] ?? '127.0.0.1');
        $port = (int) ($this->config['port'] ?? 5432);
        $database = (string) ($this->config['database'] ?? '');
        $schema = (string) ($this->config['schema'] ?? 'public');

        if ($database === '') {
            throw new RuntimeException('PostgreSQL database name is missing.');
        }

        return sprintf('pgsql:host=%s;port=%d;dbname=%s;options=\'--search_path=%s\'', $host, $port, $database, $schema);
    }
}
