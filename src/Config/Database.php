<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Construtor privado para implementar o padrão Singleton,
        // garantindo que haja apenas uma instância de conexão.
    }

    /**
     * Retorna uma instância da conexão PDO.
     * Implementa o padrão Singleton para reutilizar a mesma conexão.
     *
     * @return PDO
     * @throws PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // As credenciais devem vir de variáveis de ambiente para segurança
            $host = $_ENV['DB_HOST'] ?? 'db';
            $dbname = $_ENV['DB_NAME'] ?? 'mini_erp_db';
            $user = $_ENV['DB_USER'] ?? 'user_erp';
            $password = $_ENV['DB_PASSWORD'] ?? 'password_erp';
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lançar exceções em caso de erro
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retornar resultados como arrays associativos
                PDO::ATTR_EMULATE_PREPARES   => false,                  // Desabilitar emulação de prepared statements (para segurança e performance)
            ];

            try {
                self::$instance = new PDO($dsn, $user, $password, $options);
            } catch (PDOException $e) {
                die('Erro de conexão com o banco de dados: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }
}