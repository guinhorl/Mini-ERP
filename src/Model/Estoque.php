<?php

namespace App\Model;

use App\Config\Database;
use InvalidArgumentException;
use PDO;
use PDOException;

class Estoque
{
    private PDO $pdo;

    private ?int $id_estoque;
    private string $localizacao;
    private ?string $created_at;
    private ?string $updated_at;

    public function __construct(
        ?int $id_estoque = null,
        string $localizacao = '',
        ?string $created_at = null,
        ?string $updated_at = null
    ){
        $this->pdo = Database::getConnection();
        $this->id_estoque = $id_estoque;
        $this->localizacao = $localizacao;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function getIdEstoque(): ?int
    {
        return $this->id_estoque;
    }

    public function setIdEstoque(?int $id_estoque): void
    {
        $this->id_estoque = $id_estoque;
    }

    public function getLocalizacao(): string
    {
        return $this->localizacao;
    }

    public function setLocalizacao(string $localizacao): void
    {
        if (empty(trim($localizacao))) {
            throw new InvalidArgumentException("A localização não pode ser vazia.");
        }
        $this->localizacao = $localizacao;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?string $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    /**
     * Define os atributos do objeto Estoque a partir de um array.
     * Útil para popular o objeto após buscar do banco ou receber dados de um formulário.
     *
     * @param array $dados Array associativo com os dados do estoque.
     * @return void
     */
    public function preencher(array $dados): void
    {
        if (isset($dados['id_estoque'])) {
            $this->setIdEstoque($dados['id_estoque']);
        }
        if (isset($dados['localizacao'])) {
            $this->setLocalizacao($dados['localizacao']);
        }
        if (isset($dados['created_at'])) {
            $this->setCreatedAt($dados['created_at']);
        }
        if (isset($dados['updated_at'])) {
            $this->setUpdatedAt($dados['updated_at']);
        }
    }

    /**
     * Salva um novo estoque no banco de dados.
     *
     * @return bool True se o estoque foi salvo com sucesso, false caso contrário.
     */
    public function salvar(): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO estoques (localizacao) VALUES (:localizacao)");
            $stmt->bindValue(':localizacao', $this->getLocalizacao());

            $result = $stmt->execute();
            //var_dump($result);die();

            if ($result) {
                $this->setIdEstoque((int)$this->pdo->lastInsertId());
            }
            return $result;
        } catch (PDOException $pdoException) {
            error_log("Erro ao salvar estoque: " . $pdoException->getMessage());
            //echo "Erro ao salvar estoque: " . $pdoException->getMessage();
            return false;
        } catch (InvalidArgumentException $invalidArgumentException) {
            error_log("Erro de validação ao salvar estoque: " . $invalidArgumentException->getMessage());
            //echo "Erro de validação ao salvar estoque: " . $invalidArgumentException->getMessage();
            return false;
        }
    }

    /**
     * Busca todos os estoques no banco de dados.
     *
     * @return array Um array de objetos Estoque.
     */
    public static function todos(): array
    {
        $instance = new static();
        try {
            $stmt = $instance->pdo->query("SELECT * FROM estoques");
            $estoquesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $estoques = [];
            foreach ($estoquesData as $data) {
                $estoque = new static();
                $estoque->preencher($data);
                $estoques[] = $estoque;
            }
            return $estoques;
        } catch (PDOException $pdoException) {
            error_log("Erro ao buscar todos os estoques: " . $pdoException->getMessage());
            //echo "Erro ao buscar todos os estoques: " . $pdoException->getMessage();
            return [];
        }
    }

    /**
     * Busca um estoque pelo ID.
     *
     * @param int $idEstoque O ID do estoque a ser buscado.
     * @return Estoque|null O objeto Estoque se encontrado, ou null caso contrário.
     */
    public static function buscar(int $idEstoque): ?self
    {
        $instance = new static();
        try {
            $stmt = $instance->pdo->prepare("SELECT * FROM estoques WHERE id_estoque = :id_estoque");
            $stmt->bindValue(':id_estoque', $idEstoque, PDO::PARAM_INT);
            $stmt->execute();
            $estoqueData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($estoqueData) {
                $estoque = new static();
                $estoque->preencher($estoqueData);
                return $estoque;
            }
            return null;
        } catch (PDOException $pdoException) {
            echo "Erro ao buscar estoque: " . $pdoException->getMessage();
            return null;
        }
    }

    /**
     * Atualiza um estoque existente no banco de dados.
     *
     * @return bool True se o estoque foi atualizado com sucesso, false caso contrário.
     */
    public function atualizar(): bool
    {
        if ($this->id_estoque === null) {
            echo "Erro: ID do estoque é necessário para atualização.";
            return false;
        }
        try {
            $stmt = $this->pdo->prepare("UPDATE estoques SET localizacao = :localizacao WHERE id_estoque = :id_estoque");
            $stmt->bindValue(':localizacao', $this->getLocalizacao());
            $stmt->bindValue(':id_estoque', $this->getIdEstoque(), PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $pdoException) {
            error_log("Erro ao atualizar estoque: " . $pdoException->getMessage());
            //echo "Erro ao atualizar estoque: " . $pdoException->getMessage();
            return false;
        } catch (InvalidArgumentException $invalidArgumentException) {
            error_log("Erro de validação ao atualizar estoque: " . $invalidArgumentException->getMessage());
            //echo "Erro de validação ao atualizar estoque: " . $invalidArgumentException->getMessage();
            return false;
        }
    }

    /**
     * Deleta um estoque do banco de dados.
     *
     * @param int $id_estoque O ID do estoque a ser deletado.
     * @return bool True se o estoque foi deletado com sucesso, false caso contrário.
     */
    public static function delete(int $id_estoque): bool
    {
        $instance = new static();
        try {
            $stmt = $instance->pdo->prepare("DELETE FROM estoques WHERE id_estoque = :id_estoque");
            $stmt->bindValue(':id_estoque', $id_estoque, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $pdoException) {
            error_log("Erro ao deletar estoque: " . $pdoException->getMessage());
            //echo "Erro ao deletar estoque: " . $pdoException->getMessage();
            return false;
        }
    }
}