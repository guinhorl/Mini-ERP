<?php

namespace App\Controller;

use App\Model\Cupom;
use InvalidArgumentException;
use PDOException;

class CupomController
{
    /**
     * Exibe a lista de todos os cupons.
     * Corresponde à rota GET /cupons
     *
     * @return array Um array de objetos Cupom.
     */
    public function index(): array
    {
        return Cupom::todos();
    }

    /**
     * Exibe o formulário para criar um novo cupom.
     * Corresponde à rota GET /cupons/novo
     *
     * @return Cupom Um objeto Cupom vazio para o formulário.
     */
    public function novo(): Cupom
    {
        return new Cupom();
    }

    /**
     * Processa a submissão do formulário para salvar um novo cupom.
     * Corresponde à rota POST /cupons/salvar
     *
     * @return void
     */
    public function salvar(): void
    {
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tipoDesconto = filter_input(INPUT_POST, 'tipo_desconto', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $valorDesconto = filter_input(INPUT_POST, 'valor_desconto', FILTER_VALIDATE_FLOAT);
        $dataValidade = filter_input(INPUT_POST, 'data_validade', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $ativo = filter_input(INPUT_POST, 'ativo', FILTER_SANITIZE_FULL_SPECIAL_CHARS) === 'on' ? true : false;


        if (!$codigo || !$tipoDesconto || $valorDesconto === false || !$dataValidade) {
            $_SESSION['error_message'] = "Preencha todos os campos obrigatórios do cupom!";
            header('Location: /cupons/novo');
            exit();
        }

        $cupom = new Cupom();
        try {
            $cupom->setCodigo($codigo);
            $cupom->setTipoDesconto($tipoDesconto);
            $cupom->setValorDesconto($valorDesconto);
            $cupom->setDataValidade($dataValidade);
            $cupom->setAtivo($ativo);

            if ($cupom->salvarOuAtualizar()) {
                $_SESSION['success_message'] = "Cupom '{$cupom->getCodigo()}' salvo com sucesso!";
                header('Location: /cupons');
                exit();
            } else {
                $_SESSION['error_message'] = "Erro ao salvar o cupom no banco de dados.";
                header('Location: /cupons/novo');
                exit();
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['error_message'] = "Erro de validação: " . $e->getMessage();
            header('Location: /cupons/novo');
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['error_message'] = "Erro: Já existe um cupom com este código. Use um código único.";
            } else {
                $_SESSION['error_message'] = "Erro no banco de dados ao salvar cupom: " . $e->getMessage();
            }
            header('Location: /cupons/novo');
            exit();
        }
    }

    /**
     * Exibe o formulário para editar um cupom existente.
     * Corresponde à rota GET /cupons/editar/{id}
     *
     * @param int $id O ID do cupom a ser editado.
     * @return Cupom|null O objeto Cupom se encontrado, ou null se não.
     */
    public function editar(int $id): ?Cupom
    {
        $cupom = Cupom::buscar($id);
        if (!$cupom) {
            $_SESSION['error_message'] = "Cupom não encontrado para edição.";
            header('Location: /cupons');
            exit();
        }
        return $cupom;
    }

    /**
     * Processa a submissão do formulário para atualizar um cupom existente.
     * Corresponde à rota POST /cupons/atualizar
     *
     * @return void
     */
    public function atualizar(): void
    {
        $idCupom = filter_input(INPUT_POST, 'id_cupom', FILTER_VALIDATE_INT);
        $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $tipoDesconto = filter_input(INPUT_POST, 'tipo_desconto', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $valorDesconto = filter_input(INPUT_POST, 'valor_desconto', FILTER_VALIDATE_FLOAT);
        $dataValidade = filter_input(INPUT_POST, 'data_validade', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $ativo = filter_input(INPUT_POST, 'ativo', FILTER_SANITIZE_FULL_SPECIAL_CHARS) === 'on' ? true : false;

        if (!$idCupom || !$codigo || !$tipoDesconto || $valorDesconto === false || !$dataValidade) {
            $_SESSION['error_message'] = "Dados inválidos para atualização do cupom. Verifique todos os campos.";
            header('Location: /cupons/editar/' . ($idCupom ?? ''));
            exit();
        }

        $cupom = Cupom::buscar($idCupom);
        if (!$cupom) {
            $_SESSION['error_message'] = "Cupom a ser atualizado não encontrado!";
            header('Location: /cupons');
            exit();
        }

        try {
            $cupom->setCodigo($codigo);
            $cupom->setTipoDesconto($tipoDesconto);
            $cupom->setValorDesconto($valorDesconto);
            $cupom->setDataValidade($dataValidade);
            $cupom->setAtivo($ativo);

            if ($cupom->salvarOuAtualizar()) {
                $_SESSION['success_message'] = "Cupom '{$cupom->getCodigo()}' atualizado com sucesso!";
                header('Location: /cupons');
                exit();
            } else {
                $_SESSION['error_message'] = "Erro ao atualizar o cupom no banco de dados.";
                header('Location: /cupons/editar/' . $idCupom);
                exit();
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['error_message'] = "Erro de validação: " . $e->getMessage();
            header('Location: /cupons/editar/' . $idCupom);
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $_SESSION['error_message'] = "Erro: Já existe um cupom com este código. Use um código único.";
            } else {
                $_SESSION['error_message'] = "Erro no banco de dados ao atualizar cupom: " . $e->getMessage();
            }
            header('Location: /cupons/editar/' . $idCupom);
            exit();
        }
    }

    /**
     * Deleta um cupom.
     * Corresponde à rota POST /cupons/deletar/{id}
     *
     * @param int $id O ID do cupom a ser deletado.
     * @return void
     */
    public function deletar(int $id): void
    {
        if (Cupom::delete($id)) {
            $_SESSION['success_message'] = "Cupom deletado com sucesso!";
            header('Location: /cupons');
            exit();
        } else {
            $_SESSION['error_message'] = "Erro ao deletar o cupom.";
            header('Location: /cupons');
            exit();
        }
    }
}