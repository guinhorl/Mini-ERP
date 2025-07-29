<?php

namespace App\Model;

use App\Config\Database;
use InvalidArgumentException;
use PDO;
use PDOException;

class ItemPedido
{
    private PDO $pdo;


    private ?int $id_item_pedido;
    private ?int $id_pedido;
    private int $id_produto;
    private int $quantidade;
    private float $preco_unitario;
    private ?string $created_at;
    private ?string $updated_at;


    private ?Produto $produtoObjeto = null;

    /**
     * Construtor da classe ItemPedido.
     *
     * @param int $id_pedido ID do pedido ao qual este item pertence.
     * @param int $id_produto ID do produto.
     * @param int $quantidade Quantidade do produto neste item.
     * @param float $preco_unitario Preço unitário do produto no momento da adição ao pedido.
     * @param int|null $id_item_pedido ID único do item de pedido (null para novos registros).
     * @param string|null $created_at Data de criação (geralmente gerado pelo DB).
     * @param string|null $updated_at Data de última atualização (geralmente gerado pelo DB).
     * @throws InvalidArgumentException Se algum parâmetro for inválido.
     */
    public function __construct(
        ?int $id_pedido,
        int $id_produto,
        int $quantidade,
        float $preco_unitario,
        ?int $id_item_pedido = null,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->pdo = Database::getConnection();

        $this->id_item_pedido = $id_item_pedido;
        $this->setIdPedido($id_pedido);
        $this->setIdProduto($id_produto);
        $this->setQuantidade($quantidade);
        $this->setPrecoUnitario($preco_unitario);
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getIdItemPedido(): ?int { return $this->id_item_pedido; }
    public function getIdPedido(): ?int { return $this->id_pedido; }
    public function getIdProduto(): int { return $this->id_produto; }
    public function getQuantidade(): int { return $this->quantidade; }
    public function getPrecoUnitario(): float { return $this->preco_unitario; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    /**
     * Retorna o objeto Produto associado a este ItemPedido.
     * Busca o produto no banco de dados se ainda não estiver carregado.
     * @return Produto|null
     */
    public function getProduto(): ?Produto
    {
        if ($this->produtoObjeto === null && $this->id_produto !== null) {
            $this->produtoObjeto = Produto::buscar($this->id_produto);
        }
        return $this->produtoObjeto;
    }

    public function setIdItemPedido(?int $id_item_pedido): void { $this->id_item_pedido = $id_item_pedido; }
    public function setIdPedido(?int $id_pedido): void
    {
        $this->id_pedido = $id_pedido;
    }
    public function setIdProduto(int $id_produto): void
    {
        if ($id_produto <= 0) {
            throw new InvalidArgumentException("ID do produto deve ser um número inteiro positivo.");
        }
        $this->id_produto = $id_produto;
        $this->produtoObjeto = null;
    }
    public function setQuantidade(int $quantidade): void
    {
        if ($quantidade <= 0) {
            throw new InvalidArgumentException("A quantidade do item de pedido deve ser um número inteiro positivo.");
        }
        $this->quantidade = $quantidade;
    }
    public function setPrecoUnitario(float $preco_unitario): void
    {
        if ($preco_unitario < 0) {
            throw new InvalidArgumentException("O preço unitário do item de pedido não pode ser negativo.");
        }
        $this->preco_unitario = $preco_unitario;
    }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
    public function setUpdatedAt(?string $updated_at): void { $this->updated_at = $updated_at; }

    /**
     * Calcula o subtotal deste item de pedido (quantidade * preco_unitario).
     * @return float O subtotal do item.
     */
    public function calcularSubtotal(): float
    {
        return $this->quantidade * $this->preco_unitario;
    }

    /**
     * Preenche os atributos do objeto ItemPedido a partir de um array.
     * Utiliza os setters para garantir a validação.
     *
     * @param array $dados Array associativo com os dados.
     * @return void
     */
    public function preencher(array $dados): void
    {
        if (isset($dados['id_item_pedido'])) $this->setIdItemPedido($dados['id_item_pedido']);
        if (isset($dados['id_pedido'])) $this->setIdPedido($dados['id_pedido']);
        if (isset($dados['id_produto'])) $this->setIdProduto($dados['id_produto']);
        if (isset($dados['quantidade'])) $this->setQuantidade($dados['quantidade']);
        if (isset($dados['preco_unitario'])) $this->setPrecoUnitario((float)$dados['preco_unitario']);
        if (isset($dados['created_at'])) $this->setCreatedAt($dados['created_at']);
        if (isset($dados['updated_at'])) $this->setUpdatedAt($dados['updated_at']);
    }

    /**
     * Salva ou atualiza um item de pedido no banco de dados.
     * Se id_item_pedido for null, tenta inserir. Caso contrário, tenta atualizar.
     *
     * @return bool True se a operação foi bem-sucedida, false caso contrário.
     */
    public function salvarOuAtualizar(): bool
    {
        try {
            if ($this->getIdPedido() <= 0) {
                throw new InvalidArgumentException("Item de pedido deve ser associado a um pedido válido.");
            }
            if ($this->getIdProduto() <= 0) {
                throw new InvalidArgumentException("Item de pedido deve ser associado a um produto válido.");
            }
            if ($this->getQuantidade() <= 0) {
                throw new InvalidArgumentException("A quantidade do item de pedido deve ser maior que zero.");
            }
            if ($this->getPrecoUnitario() < 0) {
                throw new InvalidArgumentException("O preço unitário do item de pedido não pode ser negativo.");
            }


            if ($this->id_item_pedido === null) {
                $stmt = $this->pdo->prepare("INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario) VALUES (:id_pedido, :id_produto, :quantidade, :preco_unitario)");
                $stmt->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
                $stmt->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
                $stmt->bindValue(':quantidade', $this->getQuantidade(), PDO::PARAM_INT);
                $stmt->bindValue(':preco_unitario', $this->getPrecoUnitario());

                $result = $stmt->execute();
                if ($result) {
                    $this->setIdItemPedido((int)$this->pdo->lastInsertId());
                }
                return $result;
            } else {
                $stmt = $this->pdo->prepare("UPDATE itens_pedido SET id_pedido = :id_pedido, id_produto = :id_produto, quantidade = :quantidade, preco_unitario = :preco_unitario WHERE id_item_pedido = :id_item_pedido");
                $stmt->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
                $stmt->bindValue(':id_produto', $this->getIdProduto(), PDO::PARAM_INT);
                $stmt->bindValue(':quantidade', $this->getQuantidade(), PDO::PARAM_INT);
                $stmt->bindValue(':preco_unitario', $this->getPrecoUnitario());
                $stmt->bindValue(':id_item_pedido', $this->getIdItemPedido(), PDO::PARAM_INT);
                return $stmt->execute();
            }
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao salvar/atualizar item de pedido: " . $pdoException->getMessage());
            return false;
        } catch (InvalidArgumentException $invalidArgumentException) {
            error_log("Erro de validação ao salvar/atualizar item de pedido: " . $invalidArgumentException->getMessage());
            return false;
        }
    }

    /**
     * Busca um item de pedido pelo ID.
     *
     * @param int $id O ID do item de pedido a ser buscado.
     * @return ItemPedido|null O objeto ItemPedido se encontrado, ou null.
     */
    public static function buscar(int $id): ?self
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM itens_pedido WHERE id_item_pedido = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $item = new static(
                    (int)$data['id_pedido'],
                    (int)$data['id_produto'],
                    (int)$data['quantidade'],
                    (float)$data['preco_unitario'],
                    (int)$data['id_item_pedido'],
                    $data['created_at'],
                    $data['updated_at']
                );
                return $item;
            }
            return null;
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao buscar item de pedido por ID: " . $pdoException->getMessage());
            return null;
        }
    }

    /**
     * Busca itens de pedido pelo ID do pedido ao qual pertencem.
     * Útil para carregar todos os itens de um pedido específico.
     *
     * @param int $idPedido O ID do pedido.
     * @return array Um array de objetos ItemPedido.
     */
    public static function buscarPorIdPedido(int $idPedido): array
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM itens_pedido WHERE id_pedido = :id_pedido");
            $stmt->bindValue(':id_pedido', $idPedido, PDO::PARAM_INT);
            $stmt->execute();
            $itemsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $items = [];
            foreach ($itemsData as $data) {
                $item = new static(
                    (int)$data['id_pedido'],
                    (int)$data['id_produto'],
                    (int)$data['quantidade'],
                    (float)$data['preco_unitario'],
                    (int)$data['id_item_pedido'],
                    $data['created_at'],
                    $data['updated_at']
                );
                $items[] = $item;
            }
            return $items;
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao buscar itens de pedido por ID do pedido: " . $pdoException->getMessage());
            return [];
        }
    }

    /**
     * Deleta um item de pedido do banco de dados.
     *
     * @param int $id O ID do item de pedido a ser deletado.
     * @return bool True se deletado com sucesso, false caso contrário.
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM itens_pedido WHERE id_item_pedido = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao deletar item de pedido: " . $pdoException->getMessage());
            return false;
        }
    }
}