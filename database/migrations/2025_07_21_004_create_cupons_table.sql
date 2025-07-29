-- migration: 2025_07_21_004_create_cupons_table.sql
-- Cria a tabela 'cupons'

CREATE TABLE IF NOT EXISTS cupons (
                                      id_cupom INT AUTO_INCREMENT PRIMARY KEY,
                                      codigo VARCHAR(50) UNIQUE NOT NULL,
    tipo_desconto ENUM('PERCENTUAL', 'FIXO') NOT NULL, -- Usamos ENUM para tipos fixos
    valor_desconto DECIMAL(10, 2) NOT NULL,
    data_validade DATE NOT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );