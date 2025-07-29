<?php

namespace App\Controller;

use App\Model\Estoque;
use App\Model\Produto;
use App\Model\ItemEstoque;

class EstoqueController
{
    /**
     * Exibe a visão geral do estoque, mostrando produtos e suas quantidades em cada localização.
     * Corresponde à rota GET /estoque
     *
     * @return void
     */
    public function index(): array
    {
        $produtos = Produto::todos();
        $estoqueDetalhado = [];

        foreach ($produtos as $produto) {
            $itensEstoqueProduto = ItemEstoque::encontrarPorIDDoProduto($produto->getIdProduto());

            $produtoData = [
                'id_produto' => $produto->getIdProduto(),
                'nome' => $produto->getNome(),
                'sku' => $produto->getSku(),
                'estoque_total' => 0,
                'locais' => []
            ];

            foreach ($itensEstoqueProduto as $itemEstoque) {
                $localizacaoEstoque = Estoque::buscar($itemEstoque->getIdEstoque());
                if ($localizacaoEstoque) {
                    $produtoData['locais'][] = [
                        'localizacao_nome' => $localizacaoEstoque->getLocalizacao(),
                        'quantidade' => $itemEstoque->getQuantidadeAtual(),
                        'id_item_estoque' => $itemEstoque->getIdItemEstoque()
                    ];
                    $produtoData['estoque_total'] += $itemEstoque->getQuantidadeAtual();
                }
            }
            $estoqueDetalhado[] = $produtoData;
        }
        return $estoqueDetalhado;
    }

    /**
     * Exibe o formulário para adicionar ou remover estoque de um produto específico.
     * Corresponde à rota GET /estoque/movimentar/{id_produto}/{id_estoque?}
     *
     * @param int $idProduto O ID do produto a ser movimentado.
     * @param int|null $idEstoque Opcional. O ID do estoque específico para pré-selecionar no formulário.
     * @return void
     */
    public function movimentar(int $idProduto, ?int $idEstoque = null): ?array
    {
        //var_dump($idEstoque);die();
        $produto = Produto::buscar($idProduto);
        if (!$produto) {
            $_SESSION['error_message'] = "Produto não encontrado para movimentação de estoque.";
            header('Location: /estoque');
            exit();
        }

        $estoquesDisponiveis = Estoque::todos();
        $itemEstoque = null;
        $localizacaoAtual = 'Não em estoque';

        if ($idEstoque !== null) {
            $itemEstoque = ItemEstoque::findByProductAndEstoque($idProduto, $idEstoque);
            $localizacaoObj = Estoque::buscar($idEstoque);
            if ($localizacaoObj) {
                $localizacaoAtual = $localizacaoObj->getLocalizacao();
            }
        }

        return [
            'produto' => $produto,
            'estoquesDisponiveis' => $estoquesDisponiveis,
            'itemEstoque' => $itemEstoque,
            'localizacaoAtual' => $localizacaoAtual
        ];
    }

    /**
     * Processa a submissão do formulário para adicionar/remover estoque.
     * Corresponde à rota POST /estoque/processar-movimentacao
     *
     * @return void
     */
    public function processarMovimentacao(): void
    {
        $idProduto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);
        $idEstoque = filter_input(INPUT_POST, 'id_estoque', FILTER_VALIDATE_INT);
        $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
        $tipoMovimentacao = filter_input(INPUT_POST, 'tipo_movimentacao', FILTER_UNSAFE_RAW);


        if (!$idProduto || !$idEstoque || $quantidade === false || !in_array($tipoMovimentacao, ['adicionar', 'remover'])) {
            $_SESSION['error_message'] = "Dados inválidos para movimentação de estoque.";
            header('Location: /estoque');
            exit();
        }

        $produto = Produto::buscar($idProduto);
        $estoque = Estoque::buscar($idEstoque);

        if (!$produto || !$estoque) {
            $_SESSION['error_message'] = "Produto ou Estoque não encontrados.";
            header('Location: /estoque');
            exit();
        }

        $itemEstoque = ItemEstoque::findByProductAndEstoque($idProduto, $idEstoque);

        try {
            if ($tipoMovimentacao === 'adicionar') {
                if ($itemEstoque === null) {
                    $itemEstoque = new ItemEstoque($idProduto, $idEstoque, 0);
                    $itemEstoque->salvarOuAtualizar();
                }
                if ($itemEstoque->adicionarQuantidade($quantidade)) {
                    $_SESSION['success_message'] = "Estoque de '{$produto->getNome()}' adicionado com sucesso em '{$estoque->getLocalizacao()}'.";
                } else {
                    $_SESSION['error_message'] = "Erro ao adicionar estoque.";
                }
            } elseif ($tipoMovimentacao === 'remover') {
                if ($itemEstoque === null || $itemEstoque->getQuantidadeAtual() < $quantidade) {
                    $_SESSION['error_message'] = "Estoque insuficiente de '{$produto->getNome()}' em '{$estoque->getLocalizacao()}' para remover {$quantidade} unidades.";
                } elseif ($itemEstoque->removerQuantidade($quantidade)) {
                    $_SESSION['success_message'] = "Estoque de '{$produto->getNome()}' removido com sucesso de '{$estoque->getLocalizacao()}'.";
                } else {
                    $_SESSION['error_message'] = "Erro ao remover estoque.";
                }
            }
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error_message'] = "Erro de validação: " . $e->getMessage();
        } catch (\PDOException $e) {
            $_SESSION['error_message'] = "Erro no banco de dados: " . $e->getMessage();
        }

        header('Location: /estoque');
        exit();
    }

    /**
     * Exibe a lista de todas as localizações de estoque.
     * Corresponde à rota GET /estoque/localizacoes
     *
     * @return void
     */
    public function listarLocalizacoes(): array
    {
        return Estoque::todos();

    }

    /**
     * Exibe o formulário para criar uma nova localização de estoque.
     * Corresponde à rota GET /estoque/localizacoes/nova
     *
     * @return void
     */
    public function criarLocalizacao(): Estoque
    {
        return new Estoque();
    }

    /**
     * Processa a submissão do formulário para salvar uma nova localização de estoque.
     * Corresponde à rota POST /estoque/localizacoes/salvar
     *
     * @return void
     */
    public function salvarLocalizacao(): void
    {
        $localizacaoNome = filter_input(INPUT_POST, 'localizacao_nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$localizacaoNome) {
            $_SESSION['error_message'] = "O nome da localização não pode ser vazio.";
            header('Location: /estoque/localizacoes/nova');
            exit();
        }

        $estoque = new Estoque();
        try {
            $estoque->setLocalizacao($localizacaoNome);
            if ($estoque->salvar()) {
                $_SESSION['success_message'] = "Localização '{$estoque->getLocalizacao()}' salva com sucesso!";
                header('Location: /estoque/localizacoes');
                exit();
            } else {
                $_SESSION['error_message'] = "Erro ao salvar a localização no banco de dados.";
                header('Location: /estoque/localizacoes/nova');
                exit();
            }
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error_message'] = "Erro de validação: " . $e->getMessage();
            header('Location: /estoque/localizacoes/nova');
            exit();
        }
    }

    /**
     * Exibe o formulário para editar uma localização de estoque existente.
     * Corresponde à rota GET /estoque/localizacoes/editar/{id}
     *
     * @param int $id O ID da localização de estoque a ser editada.
     * @return void
     */
    public function editarLocalizacao(int $id): ?Estoque
    {
        $estoque = Estoque::buscar($id);

        if (!$estoque) {
            $_SESSION['error_message'] = "Localização de estoque não encontrada.";
            header('Location: /estoque/localizacoes');
            exit();
        }
        return $estoque;
    }

    /**
     * Processa a submissão do formulário para atualizar uma localização de estoque existente.
     * Corresponde à rota POST /estoque/localizacoes/atualizar
     *
     * @return void
     */
    public function atualizarLocalizacao(): void
    {
        $idEstoque = filter_input(INPUT_POST, 'id_estoque', FILTER_VALIDATE_INT);
        $localizacaoNome = filter_input(INPUT_POST, 'localizacao_nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$idEstoque || !$localizacaoNome) {
            $_SESSION['error_message'] = "Dados inválidos para atualização da localização.";
            header('Location: /estoque/localizacoes/editar/' . ($idEstoque ?? ''));
            exit();
        }

        $estoque = Estoque::buscar($idEstoque);
        if (!$estoque) {
            $_SESSION['error_message'] = "Localização de estoque a ser atualizada não encontrada.";
            header('Location: /estoque/localizacoes');
            exit();
        }

        try {
            $estoque->setLocalizacao($localizacaoNome);
            if ($estoque->atualizar()) {
                $_SESSION['success_message'] = "Localização '{$estoque->getLocalizacao()}' atualizada com sucesso!";
                header('Location: /estoque/localizacoes');
                exit();
            } else {
                $_SESSION['error_message'] = "Erro ao atualizar a localização no banco de dados.";
                header('Location: /estoque/localizacoes/editar/' . $idEstoque);
                exit();
            }
        } catch (\InvalidArgumentException $e) {
            $_SESSION['error_message'] = "Erro de validação: " . $e->getMessage();
            header('Location: /estoque/localizacoes/editar/' . $idEstoque);
            exit();
        }
    }

    /**
     * Deleta uma localização de estoque.
     * Corresponde à rota POST /estoque/localizacoes/deletar/{id}
     *
     * @param int $id O ID da localização de estoque a ser deletada.
     * @return void
     */
    public function deletarLocalizacao(int $id): void
    {
        if (Estoque::delete($id)) {
            $_SESSION['success_message'] = "Localização de estoque deletada com sucesso!";
            header('Location: /estoque/localizacoes');
            exit();
        } else {
            $_SESSION['error_message'] = "Erro ao deletar a localização de estoque.";
            header('Location: /estoque/localizacoes');
            exit();
        }
    }
}