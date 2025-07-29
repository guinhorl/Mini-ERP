<?php

namespace App\Controller;

use App\Model\Produto;
use InvalidArgumentException;
use PDOException;

class ProdutoController
{
    /**
     * Exibe a lista de todos os produtos.
     * Corresponde à rota GET /produtos
     * @return void
     */
    public function index(): array
    {
        return Produto::todos();
    }

    /**
     * Exibe o formulário para criar um novo produto.
     * Corresponde à rota GET /produtos/novo
     * @return void
     */
    public function adicionar(): Produto
    {
        return new Produto();
    }

    /**
     * Processa a submissão do formulário para salvar um novo produto.
     * Corresponde à rota POST /produtos/salvar
     * @return void
     */
    public function salvar(): void
    {
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $precoVenda = filter_input(INPUT_POST, 'preco_venda', FILTER_VALIDATE_FLOAT);
        $sku = filter_input(INPUT_POST, 'sku', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$nome || $precoVenda === false || !$sku) {
            $_SESSION['error_message'] = "Preencha todos os campos corretamente (Nome, Preço e SKU são obrigatórios)!";
            header('Location: /produtos/novo');
            exit();
        }

        $produto = new Produto();
        try {
            $produto->setNome($nome);
            $produto->setDescricao($descricao);
            $produto->setPrecoVenda($precoVenda);
            $produto->setSku($sku);

            if ($produto->salvar()){
                $_SESSION['success_message'] = "Produto cadastrado com sucesso!";
                header('Location: /produtos');
                exit();
            } else {
                $_SESSION['error_message'] = "Erro ao cadastrar produto!";
                header('Location: /produtos/novo');
                exit();
            }

        } catch (InvalidArgumentException $erro){
            $_SESSION['error_message'] = "Erro de validação:  ".$erro->getMessage();
            header('Location: /produtos/novo');
            exit();
        } catch (PDOException $erro){
            $_SESSION['error_message'] = "Erro no banco de dados ao salvar produto:  ".$erro->getMessage();
            header('Location: /produtos/novo');
            exit();
        }
    }

    /**
     * Exibe o formulário para editar um produto existente.
     * Corresponde à rota GET /produtos/editar/{id}
     * @param int $id O ID do produto a ser editado.
     * @return void
     */
    public function editar(int $id): ?Produto
    {
        $produto = Produto::buscar($id);
        if (!$produto) {
            $_SESSION['error_message'] = "Produto não encontrado para edição.";
            header('Location: /produtos');
            exit();
        }
        return $produto;
    }

    /**
     * Processa a submissão do formulário para atualizar um produto existente.
     * Corresponde à rota POST /produtos/atualizar
     * @return void
     */
    public function atualizar(): void
    {
        $idProduto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $precoVenda = filter_input(INPUT_POST, 'preco_venda', FILTER_VALIDATE_FLOAT);
        $sku = filter_input(INPUT_POST, 'sku', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$idProduto || $nome === false || $precoVenda === false || $sku === false) {
            $_SESSION['error_message'] = "Dados inválidos para atualização. Verifique todos os campos.";
            header('Location: /produtos/editar/' . ($idProduto ?? ''));
            exit();
        }

        $produto = Produto::buscar($idProduto);
        if (!$produto) {
            $_SESSION['error_message'] = "Produto a ser atualizado não encontrado!";
            header('Location: /produtos');
            exit();
        }

        try {
            $produto->setNome($nome);
            $produto->setDescricao($descricao);
            $produto->setPrecoVenda($precoVenda);
            $produto->setSku($sku);

            if ($produto->atualizar()){
                $_SESSION['success_message'] = "Produto atualizado com sucesso!";
                header('Location: /produtos');
                exit();
            } else {
                $_SESSION['error_message'] = "Erro ao atualizar produto!";
                header('Location: /produtos/editar/' . $idProduto);
                exit();
            }

        } catch (InvalidArgumentException $erro){
            $_SESSION['error_message'] = "Erro de validação: ".$erro->getMessage();
            header('Location: /produtos/editar/' . $idProduto);
            exit();
        } catch (PDOException $erro){
            $_SESSION['error_message'] = "Erro no banco de dados ao atualizar produto:  ".$erro->getMessage();
            header('Location: /produtos/editar/' . $idProduto);
            exit();
        }
    }

    /**
     * Deleta um produto.
     * Corresponde à rota POST /produtos/deletar/{id}
     * @param int $id O ID do produto a ser deletado.
     * @return void
     */
    public function delete(int $id): void
    {
        if (Produto::delete($id)) {
            $_SESSION['success_message'] = "Produto deletado com sucesso!";
            header('Location: /produtos');
            exit();
        } else {
            $_SESSION['error_message'] = "Erro ao deletar o produto.";
            header('Location: /produtos');
            exit();
        }
    }
}