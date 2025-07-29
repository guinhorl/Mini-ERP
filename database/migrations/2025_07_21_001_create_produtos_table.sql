-- migration: 2025_07_21_001_create_produtos_table.sql
-- Cria a tabela 'produtos'

CREATE TABLE IF NOT EXISTS produtos (
                                        id_produto INT AUTO_INCREMENT PRIMARY KEY,
                                        nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco_venda DECIMAL(10, 2) NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );