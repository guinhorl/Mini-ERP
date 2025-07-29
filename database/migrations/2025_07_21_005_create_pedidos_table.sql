-- migration: 2025_07_21_005_create_pedidos_table.sql
-- Cria a tabela 'pedidos'
-- Nota: Se você adicionar uma tabela 'clientes', aqui seria o lugar da FK para ela.

CREATE TABLE IF NOT EXISTS pedidos (
                                       id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    -- id_cliente INT, -- Descomente se você adicionar uma tabela de clientes e referenciar
                                       data_pedido DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                       status VARCHAR(50) NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

    -- FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE -- Ex. de FK para clientes
    );