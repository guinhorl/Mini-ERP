<?php

namespace App\Model;

use App\Config\Database;
use InvalidArgumentException;
use PDO;
use PDOException;

class ItemEstoque
{
    private PDO $pdo;

    private ?int $id_item_estoque;
    private int $id_produto;
    private int $id_estoque;
    private int $quantidade_atual;
    private ?string $data_ultima_atualizacao;
    private ?string $created_at;
    private ?string $updated_at;

    /**
     * Construtor da classe ItemEstoque.
     * Os IDs de produto e estoque são obrigatórios para um ItemEstoque válido.
     *
     * @param int $id_produto ID do produto.
     * @param int $id_estoque ID do estoque (localização).
     * @param int $quantidade_atual Quantidade atual do produto neste estoque.
     * @param int|null $id_item_estoque ID único do item de estoque (null para novos registros).
     * @param string|null $data_ultima_atualizacao Data da última atualização (geralmente gerado pelo DB).
     * @param string|null $created_at Data de criação (geralmente gerado pelo DB).
     * @param string|null $updated_at Data de última atualização (geralmente gerado pelo DB).
     * @throws InvalidArgumentException Se os IDs de produto/estoque ou quantidade forem inválidos.
     */
    public function __construct(
        int $id_produto,
        int $id_estoque,
        int $quantidade_atual = 0,
        ?int $id_item_estoque = null,
        ?string $data_ultima_atualizacao = null,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->pdo = Database::getConnection();

        $this->id_item_estoque = $id_item_estoque;
        $this->setIdProduto($id_produto);
        $this->setIdEstoque($id_estoque);
        $this->setQuantidadeAtual($quantidade_atual);
        $this->data_ultima_atualizacao = $data_ultima_atualizacao;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getIdItemEstoque(): ?int { return $this->id_item_estoque; }
    public function getIdProduto(): int { return $this->id_produto; }
    public function getIdEstoque(): int { return $this->id_estoque; }
    public function getQuantidadeAtual(): int { return $this->quantidade_atual; }
    public function getDataUltimaAtualizacao(): ?string { return $this->data_ultima_atualizacao; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    public function setIdItemEstoque(?int $id_item_estoque): void {
        $this->id_item_estoque = $id_item_estoque;
    }
    public function setIdProduto(int $id_produto): void
    {
        if ($id_produto <= 0) {
            throw new InvalidArgumentException("ID do produto deve ser um número inteiro positivo.");
        }
        $this->id_produto = $id_produto;
    }
    public function setIdEstoque(int $id_estoque): void
    {
        if ($id_estoque <= 0) {
            throw new InvalidArgumentException("ID do estoque deve ser um número inteiro positivo.");
        }
        $this->id_estoque = $id_estoque;
    }
    public function setQuantidadeAtual(int $quantidade_atual): void
    {
        if ($quantidade_atual < 0) {
            throw new InvalidArgumentException("A quantidade atual não pode ser negativa.");
        }
        $this->quantidade_atual = $quantidade_atual;
    }
    public function setDataUltimaAtualizacao(?string $data_ultima_atualizacao): void {
        $this->data_ultima_atualizacao = $data_ultima_atualizacao;
    }
    public function setCreatedAt(?string $created_at): void {
        $this->created_at = $created_at;
    }
    public function setUpdatedAt(?string $updated_at): void {
        $this->updated_at = $updated_at;
    }

    /**
     * Salva ou atualiza um item de estoque no banco de dados.
     * Se id_item_estoque for null, tenta inserir. Caso contrário, tenta atualizar.
     *
     * @return bool True se a operação foi bem-sucedida, false caso contrário.
     */
    public function salvarOuAtualizar(): bool
    {
        try {
            if ($this->id_item_estoque === null) {
                $stmt = $this->pdo->prepare("INSERT INTO itens_estoque (id_produto, id_estoque, quantidade_atual) VALUES (:id_produto, :id_estoque, :quantidade_atual)");
                $stmt->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
                $stmt->bindValue(':id_estoque', $this->getIdEstoque(), PDO::PARAM_INT);
                $stmt->bindValue(':quantidade_atual', $this->getQuantidadeAtual(), PDO::PARAM_INT);

                $result = $stmt->execute();
                if ($result) {
                    $this->setIdItemEstoque((int)$this->pdo->lastInsertId());
                }
                return $result;
            } else {
                $stmt = $this->pdo->prepare("UPDATE itens_estoque SET quantidade_atual = :quantidade_atual WHERE id_item_estoque = :id_item_estoque");
                $stmt->bindValue(':quantidade_atual', $this->getQuantidadeAtual(), PDO::PARAM_INT);
                $stmt->bindValue(':id_item_estoque', $this->getIdItemEstoque(), PDO::PARAM_INT);
                return $stmt->execute();
            }
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao salvar/atualizar item de estoque: " . $pdoException->getMessage());
            //echo "Erro ao salvar/atualizar item de estoque: " . $pdoException->getMessage();
            return false;
        } catch (InvalidArgumentException $invalidArgumentException) {
            error_log("Erro de validação ao salvar/atualizar item de estoque: " . $invalidArgumentException->getMessage());
            //echo "Erro de validação: " . $invalidArgumentException->getMessage();
            return false;
        }
    }

    /**
     * Busca itens de estoque pelo ID do produto.
     * Útil para ver onde um produto específico está estocado.
     *
     * @param int $produtoId O ID do produto.
     * @return array Um array de objetos ItemEstoque.
     */
    public static function encontrarPorIDDoProduto(int $produtoId): array
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM itens_estoque WHERE id_produto = :id_produto");
            $stmt->bindValue(':id_produto', $produtoId, PDO::PARAM_INT);
            $stmt->execute();
            $itemsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $items = [];
            foreach ($itemsData as $data) {
                $item = new static( // Preenche via construtor
                    $data['id_produto'],
                    $data['id_estoque'],
                    $data['quantidade_atual'],
                    $data['id_item_estoque'],
                    $data['data_ultima_atualizacao'],
                    $data['created_at'],
                    $data['updated_at']
                );
                $items[] = $item;
            }
            return $items;
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao buscar itens de estoque por ID do produto: " . $pdoException->getMessage());
            return [];
        }
    }

    /**
     * Busca um item de estoque por ID do produto e ID do estoque.
     * Útil para verificar a quantidade de um produto específico em um estoque específico.
     *
     * @param int $produtoId O ID do produto.
     * @param int $estoqueId O ID do estoque.
     * @return ItemEstoque|null O objeto ItemEstoque se encontrado, ou null.
     */
    public static function findByProductAndEstoque(int $produtoId, int $estoqueId): ?self
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM itens_estoque WHERE id_produto = :id_produto AND id_estoque = :id_estoque");
            $stmt->bindValue(':id_produto', $produtoId, PDO::PARAM_INT);
            $stmt->bindValue(':id_estoque', $estoqueId, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $item = new static( // Preenche via construtor
                    $data['id_produto'],
                    $data['id_estoque'],
                    $data['quantidade_atual'],
                    $data['id_item_estoque'],
                    $data['data_ultima_atualizacao'],
                    $data['created_at'],
                    $data['updated_at']
                );
                return $item;
            }
            return null;
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao buscar item de estoque por produto e estoque: " . $pdoException->getMessage());
            return null;
        }
    }

    /**
     * Deleta um item de estoque.
     *
     * @param int $id O ID do item de estoque a ser deletado.
     * @return bool True se deletado com sucesso, false caso contrário.
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM itens_estoque WHERE id_item_estoque = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao deletar item de estoque: " . $pdoException->getMessage());
            return false;
        }
    }

    /**
     * Adiciona uma quantidade ao estoque do item.
     *
     * @param int $quantidade A quantidade a ser adicionada.
     * @return bool True se a atualização foi bem-sucedida.
     * @throws InvalidArgumentException Se a quantidade for negativa.
     */
    public function adicionarQuantidade(int $quantidade): bool
    {
        if ($quantidade < 0) {
            throw new InvalidArgumentException("A quantidade a adicionar não pode ser negativa.");
        }
        $this->setQuantidadeAtual($this->getQuantidadeAtual() + $quantidade);
        return $this->salvarOuAtualizar();
    }

    /**
     * Remove uma quantidade do estoque do item.
     *
     * @param int $quantidade A quantidade a ser removida.
     * @return bool True se a atualização foi bem-sucedida e o estoque não ficou negativo.
     * @throws InvalidArgumentException Se a quantidade for negativa ou o estoque for insuficiente.
     */
    public function removerQuantidade(int $quantidade): bool
    {
        if ($quantidade < 0) {
            throw new InvalidArgumentException("A quantidade a remover não pode ser negativa.");
        }
        if ($this->getQuantidadeAtual() - $quantidade < 0) {
            throw new InvalidArgumentException("Não há estoque suficiente para remover esta quantidade.");
        }
        $this->setQuantidadeAtual($this->getQuantidadeAtual() - $quantidade);
        return $this->salvarOuAtualizar();
    }
}