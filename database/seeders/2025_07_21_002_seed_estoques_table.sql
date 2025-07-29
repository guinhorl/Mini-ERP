-- seeder: 2025_07_21_002_seed_estoques_table.sql
-- Insere dados iniciais na tabela 'estoques'
-- E popula 'itens_estoque' com base nos produtos e estoques criados

INSERT INTO estoques (localizacao) VALUES
                                       ('Armazém Principal - SP'),
                                       ('Loja Centro - RJ'),
                                       ('Depósito Norte - MG');

-- Popula itens_estoque com base nos produtos e estoques existentes.
-- Você precisará saber os IDs dos produtos e estoques.
-- Como os IDs são AUTO_INCREMENT, vamos assumir que os primeiros produtos e estoques
-- terão IDs 1, 2, 3, etc. (o que é o padrão).

-- Exemplo: Adicionar produtos ao Armazém Principal (id_estoque = 1)
INSERT INTO itens_estoque (id_produto, id_estoque, quantidade_atual) VALUES
                                                                         ((SELECT id_produto FROM produtos WHERE sku = 'SMARTX001'), (SELECT id_estoque FROM estoques WHERE localizacao = 'Armazém Principal - SP'), 50),
                                                                         ((SELECT id_produto FROM produtos WHERE sku = 'NOTEGZ002'), (SELECT id_estoque FROM estoques WHERE localizacao = 'Armazém Principal - SP'), 20),
                                                                         ((SELECT id_produto FROM produtos WHERE sku = 'FONEBT003'), (SELECT id_estoque FROM estoques WHERE localizacao = 'Armazém Principal - SP'), 100);

-- Exemplo: Adicionar produtos à Loja Centro (id_estoque = 2)
INSERT INTO itens_estoque (id_produto, id_estoque, quantidade_atual) VALUES
                                                                         ((SELECT id_produto FROM produtos WHERE sku = 'SMARTX001'), (SELECT id_estoque FROM estoques WHERE localizacao = 'Loja Centro - RJ'), 10),
                                                                         ((SELECT id_produto FROM produtos WHERE sku = 'FONEBT003'), (SELECT id_estoque FROM estoques WHERE localizacao = 'Loja Centro - RJ'), 30),
                                                                         ((SELECT id_produto FROM produtos WHERE sku = 'TECMEC005'), (SELECT id_estoque FROM estoques WHERE localizacao = 'Loja Centro - RJ'), 15);