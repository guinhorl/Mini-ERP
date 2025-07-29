-- migration: 2025_07_21_006_create_itens_pedidos_table.sql
-- Cria a tabela 'itens_pedido' com as chaves estrangeiras para 'pedidos' e 'produtos'

CREATE TABLE IF NOT EXISTS itens_pedido (
                                            id_item_pedido INT AUTO_INCREMENT PRIMARY KEY,
                                            id_pedido INT NOT NULL,
                                            id_produto INT NOT NULL,
                                            quantidade INT NOT NULL,
                                            preco_unitario DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY idx_pedido_produto_unique (id_pedido, id_produto) -- Garante que um produto só aparece uma vez por pedido
    );