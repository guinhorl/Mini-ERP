-- migration: 2025_07_21_002_create_estoques_table.sql
-- Cria a tabela 'estoques'

CREATE TABLE IF NOT EXISTS estoques (
                                        id_estoque INT AUTO_INCREMENT PRIMARY KEY,
                                        localizacao VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );