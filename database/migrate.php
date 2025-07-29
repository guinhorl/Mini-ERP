<?php
// database/migrate.php

//Com o seu contêiner PHP rodando, você pode executar este script PHP via linha de comando dentro do contêiner:
//docker-compose exec php php database/migrate.php



// Inclui o autoloader do Composer para que possamos usar App\Config\Database
require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

// Carrega as variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "Iniciando migrações...\n";

try {
    $pdo = Database::getConnection();
    echo "Conexão com o banco de dados estabelecida.\n";

    $migrationsDir = __DIR__ . '/migrations/';
    $migrationFiles = scandir($migrationsDir);
    sort($migrationFiles); // Garante que os arquivos sejam processados em ordem alfabética/numérica

    foreach ($migrationFiles as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filePath = $migrationsDir . $file;
            $sql = file_get_contents($filePath);

            if ($sql === false) {
                echo "Erro ao ler o arquivo de migração: $file\n";
                continue;
            }

            echo "Executando migração: $file\n";
            $pdo->exec($sql); // exec() é usado para múltiplas queries ou queries que não retornam resultados
            echo "Migração $file executada com sucesso.\n";
        }
    }

    echo "Todas as migrações concluídas com sucesso!\n";

} catch (PDOException $e) {
    echo "Erro durante a migração: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Um erro inesperado ocorreu: " . $e->getMessage() . "\n";
    exit(1);
}