-- migration: 2025_07_21_007_create_pedidos_cupons_table.sql
-- Cria a tabela associativa 'pedidos_cupons' para o relacionamento muitos-para-muitos

CREATE TABLE IF NOT EXISTS pedidos_cupons (
                                              id_pedido INT NOT NULL,
                                              id_cupom INT NOT NULL,
                                              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                                              PRIMARY KEY (id_pedido, id_cupom), -- Chave primária composta
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_cupom) REFERENCES cupons(id_cupom) ON DELETE CASCADE ON UPDATE CASCADE
    );