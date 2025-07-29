<?php

namespace App\Model;

use App\Config\Database;
use InvalidArgumentException;
use PDO;
use PDOException;

use App\Model\ItemPedido;
use App\Model\Cupom;

class Pedido
{
    private PDO $pdo;

    private ?int $id_pedido;
    private ?int $id_cliente;
    private string $data_pedido;
    private string $status;
    private float $valor_total;
    private ?string $created_at;
    private ?string $updated_at;

    /** @var ItemPedido[] */
    private array $itens;
    /** @var Cupom[] */
    private array $cuponsAplicados;

    /**
     * Construtor da classe Pedido.
     *
     * @param string $data_pedido Data do pedido no formato 'YYYY-MM-DD HH:MM:SS'. Se vazio, usa data/hora atual.
     * @param string $status Status inicial do pedido (padrão 'PENDENTE').
     * @param float $valor_total Valor total inicial do pedido.
     * @param int|null $id_pedido ID único do pedido (null para novos registros).
     * @param int|null $id_cliente ID do cliente associado ao pedido.
     * @param string|null $created_at Data de criação (geralmente gerado pelo DB).
     * @param string|null $updated_at Data de última atualização (geralmente gerado pelo DB).
     * @throws InvalidArgumentException Se algum parâmetro for inválido.
     */
    public function __construct(
        string $data_pedido = '',
        string $status = 'PENDENTE',
        float $valor_total = 0.0,
        ?int $id_pedido = null,
        ?int $id_cliente = null,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->pdo = Database::getConnection();

        $this->id_pedido = $id_pedido;
        $this->setIdCliente($id_cliente);
        $this->setDataPedido($data_pedido);
        $this->setStatus($status);
        $this->setValorTotal($valor_total);
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;

        $this->itens = [];
        $this->cuponsAplicados = [];
    }

    public function getIdPedido(): int { return $this->id_pedido; }
    public function getIdCliente(): ?int { return $this->id_cliente; }
    public function getDataPedido(): string { return $this->data_pedido; }
    public function getStatus(): string { return $this->status; }
    public function getValorTotal(): float { return $this->valor_total; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }
    /**
     * Retorna os itens de pedido associados a este pedido em memória.
     * @return ItemPedido[]
     */
    public function getItens(): array { return $this->itens; }
    /**
     * Retorna os cupons aplicados a este pedido em memória.
     * @return Cupom[]
     */
    public function getCuponsAplicados(): array { return $this->cuponsAplicados; }

    public function setIdPedido(?int $id_pedido): void { $this->id_pedido = $id_pedido; }
    public function setIdCliente(?int $id_cliente): void
    {
        // Futura validação: if ($id_cliente !== null && $id_cliente <= 0) { throw new InvalidArgumentException("ID do cliente inválido."); }
        $this->id_cliente = $id_cliente;
    }
    public function setDataPedido(string $data_pedido): void
    {
        if (empty(trim($data_pedido))) {
            $this->data_pedido = date('Y-m-d H:i:s');
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data_pedido)) {
            throw new InvalidArgumentException("Formato de data do pedido inválido. Use YYYY-MM-DD HH:MM:SS.");
        } else {
            $this->data_pedido = $data_pedido;
        }
    }
    public function setStatus(string $status): void
    {
        $status = strtoupper(trim($status));
        $statusValidos = ['PENDENTE', 'CONFIRMADO', 'ENVIADO', 'ENTREGUE', 'CANCELADO'];
        if (!in_array($status, $statusValidos)) {
            throw new InvalidArgumentException("Status de pedido inválido. Use: " . implode(', ', $statusValidos));
        }
        $this->status = $status;
    }
    public function setValorTotal(float $valor_total): void
    {
        if ($valor_total < 0) {
            throw new InvalidArgumentException("O valor total do pedido não pode ser negativo.");
        }
        $this->valor_total = $valor_total;
    }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
    public function setUpdatedAt(?string $updated_at): void { $this->updated_at = $updated_at; }

    /**
     * Adiciona um ItemPedido ao pedido em memória.
     * @param ItemPedido $item O item do pedido a ser adicionado.
     */
    public function adicionarItem(ItemPedido $item): void
    {
        $this->itens[] = $item;
    }

    /**
     * Remove um ItemPedido do pedido em memória pelo ID do item.
     * @param int $itemId O ID do ItemPedido a ser removido.
     */
    public function removerItem(int $itemId): void
    {
        foreach ($this->itens as $key => $item) {
            if ($item->getIdItemPedido() === $itemId) {
                unset($this->itens[$key]);
                break;
            }
        }
        $this->itens = array_values($this->itens);
    }

    /**
     * Adiciona um Cupom ao pedido em memória.
     * @param Cupom $cupom O cupom a ser aplicado.
     */
    public function adicionarCupom(Cupom $cupom): void
    {
        if ($cupom->validarCupom()) {
            $this->cuponsAplicados[] = $cupom;
        } else {
            throw new InvalidArgumentException("Cupom inválido ou expirado.");
        }
    }

    /**
     * Calcula o subtotal do pedido (soma dos itens, antes de frete/desconto).
     * @return float O subtotal do pedido.
     */
    public function calcularSubtotal(): float
    {
        $subtotal = 0.0;
        foreach ($this->itens as $item) {
            $subtotal += $item->calcularSubtotal();
        }
        return $subtotal;
    }

    /**
     * Calcula o valor total do pedido aplicando descontos de cupons.
     * Este valor ainda não inclui o frete.
     * @return float O valor total com descontos.
     */
    public function calcularTotalComDescontos(): float
    {
        $total = $this->calcularSubtotal();
        foreach ($this->cuponsAplicados as $cupom) {
            $total = $cupom->aplicarDesconto($total);
        }
        return $total;
    }

    /**
     * Calcula o valor do frete com base no subtotal do pedido.
     *
     * @param float $subtotal O subtotal do pedido.
     * @return float O valor do frete.
     */
    public function calcularFrete(float $subtotal): float
    {
        if ($subtotal > 200.00) {
            return 0.00;
        } elseif ($subtotal >= 52.00 && $subtotal <= 166.59) {
            return 15.00;
        } else {
            return 20.00;
        }
    }

    /**
     * Calcula o valor final do pedido, incluindo descontos e frete.
     * @return float O valor final do pedido.
     */
    public function calcularTotalFinal(): float
    {
        $subtotalComDescontos = $this->calcularTotalComDescontos();
        $frete = $this->calcularFrete($subtotalComDescontos);
        return $subtotalComDescontos + $frete;
    }

    /**
     * Salva ou atualiza um pedido no banco de dados.
     * Também salva seus itens associados e cupons aplicados.
     *
     * @return bool True se a operação foi bem-sucedida, false caso contrário.
     */
    public function salvarOuAtualizar(): bool
    {
        try {
            $this->pdo->beginTransaction();

            if (empty($this->getDataPedido())) {
                $this->setDataPedido(date('Y-m-d H:i:s'));
            }
            if (empty($this->getStatus())) {
                $this->setStatus('PENDENTE');
            }

            $this->setValorTotal($this->calcularTotalFinal());

            if ($this->id_pedido === null) {
                $stmt = $this->pdo->prepare("INSERT INTO pedidos (data_pedido, status, valor_total) VALUES (:data_pedido, :status, :valor_total)");
                $stmt->bindValue(':data_pedido', $this->getDataPedido());
                $stmt->bindValue(':status', $this->getStatus());
                $stmt->bindValue(':valor_total', $this->getValorTotal());
                $result = $stmt->execute();
                if ($result) {
                    //echo '<pre>'.print_r((int)$this->pdo->lastInsertId(), true).'</pre>';
                    $this->setIdPedido((int)$this->pdo->lastInsertId());
                } else {
                    $this->pdo->rollBack(); return false;
                }
            } else {

                $stmt = $this->pdo->prepare("UPDATE pedidos SET id_cliente = :id_cliente, data_pedido = :data_pedido, status = :status, valor_total = :valor_total WHERE id_pedido = :id_pedido");
                $stmt->bindValue(':id_cliente', $this->getIdCliente(), PDO::PARAM_INT);
                $stmt->bindValue(':data_pedido', $this->getDataPedido());
                $stmt->bindValue(':status', $this->getStatus());
                $stmt->bindValue(':valor_total', $this->getValorTotal());
                $stmt->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
                if (!$stmt->execute()) {
                    $this->pdo->rollBack(); return false;
                }
                $stmtDeleteItens = $this->pdo->prepare("DELETE FROM itens_pedido WHERE id_pedido = :id_pedido");
                $stmtDeleteItens->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
                $stmtDeleteItens->execute();

                $stmtDeleteCupons = $this->pdo->prepare("DELETE FROM pedidos_cupons WHERE id_pedido = :id_pedido");
                $stmtDeleteCupons->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
                $stmtDeleteCupons->execute();
            }

            foreach ($this->itens as $item) {
                $item->setIdPedido($this->pdo->lastInsertId());
                if (!$item->salvarOuAtualizar()) {
                    //echo '<pre>'.print_r($this, true).'</pre>';
                    $this->pdo->rollBack();
                    error_log("Erro ao salvar ItemPedido para Pedido ID: " . $this->getIdPedido() . " - Item: " . $item->getIdProduto());
                    return false;
                }
            }

            foreach ($this->cuponsAplicados as $cupom) {
                $stmtInsertCupom = $this->pdo->prepare("INSERT INTO pedidos_cupons (id_pedido, id_cupom) VALUES (:id_pedido, :id_cupom)");
                $stmtInsertCupom->bindValue(':id_pedido', $this->getIdPedido(), PDO::PARAM_INT);
                $stmtInsertCupom->bindValue(':id_cupom', $cupom->getIdCupom(), PDO::PARAM_INT);
                if (!$stmtInsertCupom->execute()) {
                    $this->pdo->rollBack();
                    error_log("Erro ao salvar Cupom ID: " . $cupom->getIdCupom() . " para Pedido ID: " . $this->getIdPedido());
                    return false;
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $pdoException) {
            $this->pdo->rollBack();
            error_log("Erro PDO ao salvar/atualizar pedido: " . $pdoException->getMessage());
            return false;
        } catch (InvalidArgumentException $invalidArgumentException) {
            $this->pdo->rollBack();
            error_log("Erro de validação ao salvar/atualizar pedido: " . $invalidArgumentException->getMessage());
            return false;
        }
    }

    /**
     * Busca todos os pedidos no banco de dados.
     *
     * @return array Um array de objetos Pedido.
     */
    public static function todos(): array
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->query("SELECT * FROM pedidos ORDER BY data_pedido DESC");
            $pedidosData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $pedidos = [];
            foreach ($pedidosData as $data) {
                $pedido = new static(
                    $data['data_pedido'],
                    $data['status'],
                    (float)$data['valor_total'],
                    (int)$data['id_pedido'],
                    isset($data['id_cliente']) ? (int)$data['id_cliente'] : null,
                    $data['created_at'],
                    $data['updated_at']
                );
                $pedido->itens = ItemPedido::buscarPorIdPedido($pedido->getIdPedido());

                $stmtCuponsAplicados = $pdo->prepare("SELECT id_cupom FROM pedidos_cupons WHERE id_pedido = :id_pedido");
                $stmtCuponsAplicados->bindValue(':id_pedido', $pedido->getIdPedido(), PDO::PARAM_INT);
                $stmtCuponsAplicados->execute();
                $cuponsIds = $stmtCuponsAplicados->fetchAll(PDO::FETCH_COLUMN);
                foreach ($cuponsIds as $cupomId) {
                    $cupomObj = Cupom::buscar($cupomId);
                    if ($cupomObj) {
                        $pedido->adicionarCupom($cupomObj);
                    }
                }
                $pedidos[] = $pedido;
            }
            return $pedidos;
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao buscar todos os pedidos: " . $pdoException->getMessage());
            return [];
        }
    }

    /**
     * Busca um pedido pelo ID.
     *
     * @param int $id O ID do pedido a ser buscado.
     * @return Pedido|null O objeto Pedido se encontrado, ou null.
     */
    public static function buscar(int $id): ?self
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id_pedido = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $pedido = new static(
                    $data['data_pedido'],
                    $data['status'],
                    (float)$data['valor_total'],
                    (int)$data['id_pedido'],
                    isset($data['id_cliente']) ? (int)$data['id_cliente'] : null,
                    $data['created_at'],
                    $data['updated_at']
                );
                $pedido->itens = ItemPedido::buscarPorIdPedido($pedido->getIdPedido());
                $stmtCuponsAplicados = $pdo->prepare("SELECT id_cupom FROM pedidos_cupons WHERE id_pedido = :id_pedido");
                $stmtCuponsAplicados->bindValue(':id_pedido', $pedido->getIdPedido(), PDO::PARAM_INT);
                $stmtCuponsAplicados->execute();
                $cuponsIds = $stmtCuponsAplicados->fetchAll(PDO::FETCH_COLUMN);
                foreach ($cuponsIds as $cupomId) {
                    $cupomObj = Cupom::buscar($cupomId);
                    if ($cupomObj) {
                        $pedido->adicionarCupom($cupomObj);
                    }
                }
                return $pedido;
            }
            return null;
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao buscar pedido por ID: " . $pdoException->getMessage());
            return null;
        }
    }

    /**
     * Deleta um pedido do banco de dados.
     *
     * @param int $id O ID do pedido a ser deletado.
     * @return bool True se deletado com sucesso, false caso contrário.
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        try {
            $pdo->beginTransaction();

            $stmtItens = $pdo->prepare("DELETE FROM itens_pedido WHERE id_pedido = :id_pedido");
            $stmtItens->bindValue(':id_pedido', $id, PDO::PARAM_INT);
            $stmtItens->execute();

            $stmtCupons = $pdo->prepare("DELETE FROM pedidos_cupons WHERE id_pedido = :id_pedido");
            $stmtCupons->bindValue(':id_pedido', $id, PDO::PARAM_INT);
            $stmtCupons->execute();

            $stmtPedido = $pdo->prepare("DELETE FROM pedidos WHERE id_pedido = :id");
            $stmtPedido->bindValue(':id', $id, PDO::PARAM_INT);
            $result = $stmtPedido->execute();

            if ($result) {
                $pdo->commit();
                return true;
            } else {
                $pdo->rollBack();
                return false;
            }
        } catch (PDOException $pdoException) {
            $pdo->rollBack();
            error_log("Erro PDO ao deletar pedido e seus itens/cupons: " . $pdoException->getMessage());
            return false;
        }
    }
}