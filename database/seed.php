<?php
// database/seed.php

//Com o seu contêiner PHP rodando, você pode executar este script PHP via linha de comando dentro do contêiner:
//docker-compose exec php php database/seed.php

// Inclui o autoloader do Composer para que possamos usar App\Config\Database
require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

// Carrega as variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo "Iniciando seeders...\n";

try {
    $pdo = Database::getConnection();
    echo "Conexão com o banco de dados estabelecida.\n";

    $seedersDir = __DIR__ . '/seeders/';
    $seederFiles = scandir($seedersDir);
    sort($seederFiles); // Garante que os arquivos sejam processados em ordem

    foreach ($seederFiles as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $filePath = $seedersDir . $file;
            $sql = file_get_contents($filePath);

            if ($sql === false) {
                echo "Erro ao ler o arquivo de seeder: $file\n";
                continue;
            }

            echo "Executando seeder: $file\n";
            $pdo->exec($sql);
            echo "Seeder $file executado com sucesso.\n";
        }
    }

    echo "Todos os seeders concluídos com sucesso!\n";

} catch (PDOException $e) {
    echo "Erro durante o seeding: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Um erro inesperado ocorreu: " . $e->getMessage() . "\n";
    exit(1);
}