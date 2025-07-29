<?php

namespace App\Model;

use App\Config\Database;
use InvalidArgumentException;
use PDO;
use PDOException;
use App\Model\ItemEstoque;
use App\Model\Estoque;

class Produto
{
    private PDO $pdo;

    private ?int $id_produto;
    private string $nome;
    private ?string $descricao;
    private float $preco_venda;
    private string $sku;
    private ?string $created_at;
    private ?string $updated_at;
    private ?string $deleted_at;

    public function __construct(
        ?int    $id_produto = null,
        string  $nome = '',
        ?string $descricao = null,
        float   $preco_venda = 0.0,
        string  $sku = '',
        ?string $created_at = null,
        ?string $updated_at = null,
        ?string $deleted_at = null
    )
    {
        $this->pdo = Database::getConnection();

        $this->id_produto = $id_produto;
        $this->nome = $nome;
        $this->descricao = $descricao;
        $this->preco_venda = $preco_venda;
        $this->sku = $sku;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
        $this->deleted_at = $deleted_at;
    }

    public function getIdProduto(): ?int { return $this->id_produto; }
    public function getNome(): string { return $this->nome; }
    public function getDescricao(): ?string { return $this->descricao; }
    public function getPrecoVenda(): float { return $this->preco_venda; }
    public function getSku(): string { return $this->sku; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    public function getDeletedAt(): ?string { return $this->deleted_at; }

    public function setIdProduto(?int $id_produto): void { $this->id_produto = $id_produto; }
    public function setNome(string $nome): void
    {
        if (empty(trim($nome))) {
            throw new InvalidArgumentException("O nome do produto não pode ser vazio.");
        }
        $this->nome = $nome;
    }
    public function setDescricao(?string $descricao): void { $this->descricao = $descricao; }
    public function setPrecoVenda(float $preco_venda): void
    {
        if ($preco_venda < 0) {
            throw new InvalidArgumentException("O preço de venda não pode ser negativo.");
        }
        $this->preco_venda = $preco_venda;
    }
    public function setSku(string $sku): void
    {
        if (empty(trim($sku))) {
            throw new InvalidArgumentException("O SKU do produto não pode ser vazio.");
        }
        $this->sku = $sku;
    }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
    public function setUpdatedAt(?string $updated_at): void { $this->updated_at = $updated_at; }
    public function setDeletedAt(?string $deleted_at): void { $this->deleted_at = $deleted_at; }


    /**
     * Define os atributos do objeto Produto a partir de um array.
     * Útil para popular o objeto após buscar do banco ou receber dados de um formulário.
     *
     * @param array $dados Array associativo com os dados do produto.
     * @return void
     */
    public function preencher(array $dados): void
    {
        if (isset($dados['id_produto'])) {
            $this->setIdProduto($dados['id_produto']);
        }
        if (isset($dados['nome'])) {
            $this->setNome($dados['nome']);
        }
        if (isset($dados['descricao'])) {
            $this->setDescricao($dados['descricao']);
        }
        if (isset($dados['preco_venda'])) {
            $this->setPrecoVenda((float)$dados['preco_venda']);
        }
        if (isset($dados['sku'])) {
            $this->setSku($dados['sku']);
        }
        if (isset($dados['created_at'])) {
            $this->setCreatedAt($dados['created_at']);
        }
        if (isset($dados['updated_at'])) {
            $this->setUpdatedAt($dados['updated_at']);
        }
        if (isset($dados['deleted_at'])) { // NOVO: Preencher deleted_at
            $this->setDeletedAt($dados['deleted_at']);
        }
    }

    /**
     * Salva um novo produto no banco de dados e associa-o a um estoque padrão.
     *
     * @return bool True se o produto e a associação de estoque forem salvos com sucesso, false caso contrário.
     */
    public function salvar(): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO produtos (nome, descricao, preco_venda, sku) VALUES (:nome, :descricao, :preco_venda, :sku)");
            $stmt->bindValue(':nome', $this->getNome());
            $stmt->bindValue(':descricao', $this->getDescricao());
            $stmt->bindValue(':preco_venda', $this->getPrecoVenda());
            $stmt->bindValue(':sku', $this->getSku());
            $result = $stmt->execute();

            //var_dump($result);die();
            if ($result) {
                $this->setIdProduto((int)$this->pdo->lastInsertId());

                $estoquePadrao = Estoque::buscar(1);

                if ($estoquePadrao) {
                    //die('entrou aqui');
                    /*VOU GARANTIR QUE TODO PRODUTO CRIADO JA TENHA UM REGISTRO DE ESTOQUE ASSOCIADO
                    EVITANDO QUE ELE DESAPARECA DO INVENTARIO E QUE A QUANTIDADE REAL SEJA ADICIONADA POSTERIOMENTE*/
                    $itemEstoque = new ItemEstoque($this->getIdProduto(), $estoquePadrao->getIdEstoque(), 0);

                    if (!$itemEstoque->salvarOuAtualizar()) {
                        //PARA UM SISTEMA MAIS ROBUSTO UMA TRANSACAO AQUI SERIA IDEAL PARA REVERTER TUDO.
                        error_log("Erro ao criar item de estoque para o produto ID: " . $this->getIdProduto() . " - " . date('Y-m-d H:i:s'));
                         /*OPCIONAL: DELETAR O PRODUTO RECEM-CRIADO SE A ASSOCIACAO DE ESTOQUE FOR CRITICOO
                         self::delete($this->getIdProduto());*/
                        return false;
                    }
                } else {
                    error_log("Estoque padrão (ID 1) não encontrado. Produto ID: " . $this->getIdProduto() . " não foi associado a um estoque. " . date('Y-m-d H:i:s'));
                    //POR ENQUANTO O PRODUTO É SALVO, MAS O ITEM DE ESTOQUE NAO
                }
            }
            return $result;
        } catch (PDOException $erro) {
            echo "Erro ao salvar produto: " . $erro->getMessage();
            return false;
        } catch (InvalidArgumentException $e) {
            echo "Erro de validação ao salvar produto: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Busca todos os produtos no banco de dados.
     *
     * @return array Um array de objetos Produto.
     */
    public static function todos(): array
    {
        $instance = new static();
        try {
            $stmt = $instance->pdo->query("SELECT * FROM produtos WHERE deleted_at IS NULL");
            $produtosData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $produtos = [];
            foreach ($produtosData as $data) {
                $produto = new static();
                $produto->preencher($data);
                $produtos[] = $produto;
            }
            return $produtos;
        } catch (PDOException $erro) {
            echo "Erro ao buscar todos os produtos: " . $erro->getMessage();
            return [];
        }
    }

    /**
     * Busca um produto pelo ID.
     *
     * @param int $idProduto O ID do produto a ser buscado.
     * @return Produto|null O objeto Produto se encontrado, ou null caso contrário.
     */
    public static function buscar(int $idProduto): ?self
    {
        $instance = new static();
        try {
            $stmt = $instance->pdo->prepare("SELECT * FROM produtos WHERE id_produto = :id_produto");
            $stmt->bindValue(':id_produto', $idProduto, PDO::PARAM_INT);
            $stmt->execute();
            $produtoData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($produtoData) {
                $produto = new static();
                $produto->preencher($produtoData);
                return $produto;
            }
            return null;
        } catch (PDOException $erro) {
            echo "Erro ao buscar produto: " . $erro->getMessage();
            return null;
        }
    }

    /**
     * Atualiza um produto existente no banco de dados.
     *
     * @return bool True se o produto foi atualizado com sucesso, false caso contrário.
     */
    public function atualizar(): bool
    {
        if ($this->id_produto === null) {
            echo "Erro: ID do produto é necessário para atualização.";
            return false;
        }
        try {
            $stmt = $this->pdo->prepare("UPDATE produtos SET nome = :nome, descricao = :descricao, preco_venda = :preco_venda, sku = :sku WHERE id_produto = :id_produto");
            $stmt->bindValue(':nome', $this->getNome());
            $stmt->bindValue(':descricao', $this->getDescricao());
            $stmt->bindValue(':preco_venda', $this->getPrecoVenda());
            $stmt->bindValue(':sku', $this->getSku());
            $stmt->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $erro) {
            echo "Erro ao atualizar produto: " . $erro->getMessage();
            return false;
        } catch (InvalidArgumentException $e) {
            echo "Erro de validação ao atualizar produto: " . $e->getMessage();
            return false;
        }
    }


    /**
     * Realiza um SOFT DELETE em um produto (marca como deletado, não remove fisicamente).
     * POrque não configurei o migration para delatar em cascata
     *
     * @param int $id O ID do produto a ser soft-deletado.
     * @return bool True se o produto foi soft-deletado com sucesso, false caso contrário.
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        try {
            $produto = self::buscar($id, true);
            if (!$produto) {
                error_log("Produto ID: {$id} não encontrado para soft delete.");
                return false;
            }
            if ($produto->getDeletedAt() !== null) {
                error_log("Produto ID: {$id} já está soft-deletado.");
                return true;
            }

            $stmt = $pdo->prepare("UPDATE produtos SET deleted_at = NOW() WHERE id_produto = :id_produto");
            $stmt->bindValue(':id_produto', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $erro) {
            error_log("Erro PDO ao soft deletar produto: " . $erro->getMessage());
            return false;
        }
    }
}