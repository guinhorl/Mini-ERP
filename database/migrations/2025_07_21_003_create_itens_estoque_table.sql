-- migration: 2025_07_21_003_create_itens_estoque_table.sql
-- Cria a tabela 'itens_estoque' com as chaves estrangeiras para 'produtos' e 'estoques'

CREATE TABLE IF NOT EXISTS itens_estoque (
                                             id_item_estoque INT AUTO_INCREMENT PRIMARY KEY,
                                             id_produto INT NOT NULL,
                                             id_estoque INT NOT NULL,
                                             quantidade_atual INT NOT NULL DEFAULT 0,
                                             data_ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                             created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                             updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                                             FOREIGN KEY (id_produto) REFERENCES produtos(id_produto) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_estoque) REFERENCES estoques(id_estoque) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY idx_produto_estoque_unique (id_produto, id_estoque) -- Garante que um produto só aparece uma vez em um estoque específico
    );