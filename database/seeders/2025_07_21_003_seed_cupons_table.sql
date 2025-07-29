-- seeder: 2025_07_21_003_seed_cupons_table.sql
-- Insere dados iniciais na tabela 'cupons'

INSERT INTO cupons (codigo, tipo_desconto, valor_desconto, data_validade, ativo) VALUES
                                                                                     ('PRIMEIRACOMPRA10', 'PERCENTUAL', 10.00, '2025-12-31', TRUE),
                                                                                     ('FRETEGRATIS', 'FIXO', 0.00, '2025-09-30', TRUE), -- Valor 0.00 para indicar frete grátis (lógica na aplicação)
                                                                                     ('DESCONTO50REAIS', 'FIXO', 50.00, '2025-11-15', TRUE),
                                                                                     ('VERAO2025', 'PERCENTUAL', 15.00, '2025-08-31', TRUE),
                                                                                     ('CUPOMEXPIRADO', 'FIXO', 20.00, '2024-01-01', FALSE);