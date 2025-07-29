<?php

namespace App\Model;

use App\Config\Database;
use InvalidArgumentException;
use PDO;
use PDOException;

class Cupom
{
    private PDO $pdo;

    private ?int $id_cupom;
    private string $codigo;
    private string $tipo_desconto;
    private float $valor_desconto;
    private string $data_validade;
    private bool $ativo;
    private ?string $created_at;
    private ?string $updated_at;

    /**
     * Construtor da classe Cupom.
     * Agora permite código, tipo e data de validade vazios/nulos na inicialização para objetos que serão preenchidos.
     *
     * @param string|null $codigo Código único do cupom (agora pode ser null ou vazio na construção).
     * @param string|null $tipo_desconto Tipo de desconto ('PERCENTUAL' ou 'FIXO').
     * @param float $valor_desconto Valor ou percentual do desconto.
     * @param string|null $data_validade Data de validade no formato 'YYYY-MM-DD'.
     * @param bool $ativo Indica se o cupom está ativo.
     * @param int|null $id_cupom ID único do cupom (null para novos registros).
     * @param string|null $created_at Data de criação (geralmente gerado pelo DB).
     * @param string|null $updated_at Data de última atualização (geralmente gerado pelo DB).
     * @throws InvalidArgumentException Se algum parâmetro for inválido.
     */
    public function __construct(
        ?string $codigo = '',
        ?string $tipo_desconto = '',
        float $valor_desconto = 0.0,
        ?string $data_validade = '',
        bool $ativo = true,
        ?int $id_cupom = null,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->pdo = Database::getConnection();

        $this->id_cupom = $id_cupom;

        $this->setCodigo($codigo);
        $this->setTipoDesconto($tipo_desconto);
        $this->setValorDesconto($valor_desconto);
        $this->setDataValidade($data_validade);
        $this->setAtivo($ativo);
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getIdCupom(): ?int { return $this->id_cupom; }
    public function getCodigo(): string { return $this->codigo; }
    public function getTipoDesconto(): string { return $this->tipo_desconto; }
    public function getValorDesconto(): float { return $this->valor_desconto; }
    public function getDataValidade(): string { return $this->data_validade; }
    public function isAtivo(): bool { return $this->ativo; }
    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getUpdatedAt(): ?string { return $this->updated_at; }

    public function setIdCupom(?int $id_cupom): void { $this->id_cupom = $id_cupom; }
    public function setCodigo(?string $codigo): void
    {
        $this->codigo = $codigo ?? '';
    }
    public function setTipoDesconto(?string $tipo_desconto): void
    {
        $tipo_desconto = strtoupper($tipo_desconto ?? '');

        if (!empty($tipo_desconto) && !in_array($tipo_desconto, ['PERCENTUAL', 'FIXO'])) {
            throw new InvalidArgumentException("Tipo de desconto inválido. Use 'PERCENTUAL' ou 'FIXO'.");
        }
        $this->tipo_desconto = $tipo_desconto;
    }
    public function setValorDesconto(float $valor_desconto): void
    {
        if ($valor_desconto < 0) {
            throw new InvalidArgumentException("O valor de desconto não pode ser negativo.");
        }
        $this->valor_desconto = $valor_desconto;
    }
    public function setDataValidade(?string $data_validade): void
    {
        if ($data_validade !== null && !empty($data_validade) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_validade)) {
            throw new InvalidArgumentException("Formato de data de validade inválido. Use YYYY-MM-DD.");
        }
        $this->data_validade = $data_validade ?? '';
    }
    public function setAtivo(bool $ativo): void { $this->ativo = $ativo; }
    public function setCreatedAt(?string $created_at): void { $this->created_at = $created_at; }
    public function setUpdatedAt(?string $updated_at): void { $this->updated_at = $updated_at; }

    /**
     * Salva ou atualiza um cupom no banco de dados.
     * Se id_cupom for null, tenta inserir. Caso contrário, tenta atualizar.
     *
     * @return bool True se a operação foi bem-sucedida, false caso contrário.
     */
    public function salvarOuAtualizar(): bool
    {
        try {
            if (empty($this->getCodigo())) {
                throw new InvalidArgumentException("O código do cupom é obrigatório para salvar.");
            }
            if (empty($this->getTipoDesconto())) {
                throw new InvalidArgumentException("O tipo de desconto é obrigatório para salvar.");
            }
            if (empty($this->getDataValidade())) {
                throw new InvalidArgumentException("A data de validade é obrigatória para salvar.");
            }


            if ($this->id_cupom === null) {
                $stmt = $this->pdo->prepare("INSERT INTO cupons (codigo, tipo_desconto, valor_desconto, data_validade, ativo) VALUES (:codigo, :tipo_desconto, :valor_desconto, :data_validade, :ativo)");
                $stmt->bindValue(':codigo', $this->getCodigo());
                $stmt->bindValue(':tipo_desconto', $this->getTipoDesconto());
                $stmt->bindValue(':valor_desconto', $this->getValorDesconto());
                $stmt->bindValue(':data_validade', $this->getDataValidade());
                $stmt->bindValue(':ativo', $this->isAtivo(), PDO::PARAM_BOOL);

                $result = $stmt->execute();
                if ($result) {
                    $this->setIdCupom((int)$this->pdo->lastInsertId());
                }
                return $result;
            } else {
                $stmt = $this->pdo->prepare("UPDATE cupons SET codigo = :codigo, tipo_desconto = :tipo_desconto, valor_desconto = :valor_desconto, data_validade = :data_validade, ativo = :ativo WHERE id_cupom = :id_cupom");
                $stmt->bindValue(':codigo', $this->getCodigo());
                $stmt->bindValue(':tipo_desconto', $this->getTipoDesconto());
                $stmt->bindValue(':valor_desconto', $this->getValorDesconto());
                $stmt->bindValue(':data_validade', $this->getDataValidade());
                $stmt->bindValue(':ativo', $this->isAtivo(), PDO::PARAM_BOOL);
                $stmt->bindValue(':id_cupom', $this->getIdCupom(), PDO::PARAM_INT);
                return $stmt->execute();
            }
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao salvar/atualizar cupom: " . $pdoException->getMessage());
            return false;
        } catch (InvalidArgumentException $invalidArgumentException) {
            error_log("Erro de validação ao salvar/atualizar cupom: " . $invalidArgumentException->getMessage());
            return false;
        }
    }

    /**
     * Busca todos os cupons no banco de dados.
     *
     * @return array Um array de objetos Cupom.
     */
    public static function todos(): array
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->query("SELECT * FROM cupons");
            $cuponsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $cupons = [];
            foreach ($cuponsData as $data) {
                $cupom = new static(
                    $data['codigo'],
                    $data['tipo_desconto'],
                    (float)$data['valor_desconto'],
                    $data['data_validade'],
                    (bool)$data['ativo'],
                    (int)$data['id_cupom'],
                    $data['created_at'],
                    $data['updated_at']
                );
                $cupons[] = $cupom;
            }
            return $cupons;
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao buscar todos os cupons: " . $pdoException->getMessage());
            return [];
        }
    }

    /**
     * Busca um cupom pelo ID.
     *
     * @param int $id O ID do cupom a ser buscado.
     * @return Cupom|null O objeto Cupom se encontrado, ou null.
     */
    public static function buscar(int $id): ?self
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM cupons WHERE id_cupom = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $cupom = new static(
                    $data['codigo'],
                    $data['tipo_desconto'],
                    (float)$data['valor_desconto'],
                    $data['data_validade'],
                    (bool)$data['ativo'],
                    (int)$data['id_cupom'],
                    $data['created_at'],
                    $data['updated_at']
                );
                return $cupom;
            }
            return null;
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao buscar cupom por ID: " . $pdoException->getMessage());
            return null;
        }
    }

    /**
     * Busca um cupom pelo código.
     *
     * @param string $codigo O código do cupom a ser buscado.
     * @return Cupom|null O objeto Cupom se encontrado, ou null.
     */
    public static function buscarPorCodigo(string $codigo): ?self
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT * FROM cupons WHERE codigo = :codigo");
            $stmt->bindValue(':codigo', $codigo);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $cupom = new static(
                    $data['codigo'],
                    $data['tipo_desconto'],
                    (float)$data['valor_desconto'],
                    $data['data_validade'],
                    (bool)$data['ativo'],
                    (int)$data['id_cupom'],
                    $data['created_at'],
                    $data['updated_at']
                );
                return $cupom;
            }
            return null;
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao buscar cupom por código: " . $pdoException->getMessage());
            return null;
        }
    }

    /**
     * Deleta um cupom do banco de dados.
     *
     * @param int $id O ID do cupom a ser deletado.
     * @return bool True se deletado com sucesso, false caso contrário.
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("DELETE FROM cupons WHERE id_cupom = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $pdoException) {
            error_log("Erro PDO ao deletar cupom: " . $pdoException->getMessage());
            return false;
        }
    }

    /**
     * Aplica o desconto do cupom a um valor original.
     *
     * @param float $valorOriginal O valor total do pedido antes do desconto.
     * @return float O valor com o desconto aplicado.
     */
    public function aplicarDesconto(float $valorOriginal): float
    {
        if (!$this->validarCupom()) {
            return $valorOriginal;
        }

        if ($this->getTipoDesconto() === 'PERCENTUAL') {
            return $valorOriginal - ($valorOriginal * ($this->getValorDesconto() / 100));
        } elseif ($this->getTipoDesconto() === 'FIXO') {
            return max(0, $valorOriginal - $this->getValorDesconto());
        }
        return $valorOriginal;
    }

    /**
     * Valida se o cupom pode ser usado (está ativo e não expirou).
     *
     * @return bool True se o cupom é válido, false caso contrário.
     */
    public function validarCupom(): bool
    {
        if (!$this->isAtivo()) {
            return false;
        }
        $dataValidade = new \DateTime($this->getDataValidade());
        $hoje = new \DateTime();

        if ($hoje > $dataValidade) {
            return false;
        }

        return true;
    }
}