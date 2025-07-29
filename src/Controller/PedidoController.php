<?php

namespace App\Controller;

use App\Model\Pedido;
use App\Model\ItemPedido;
use App\Model\Produto;
use App\Model\Cupom;
use App\Model\ItemEstoque;
use InvalidArgumentException;
use PDOException;

class PedidoController
{
    /**
     * Exibe a lista de todos os pedidos.
     * Corresponde à rota GET /pedidos
     *
     * @return array Um array de objetos Pedido.
     */
    public function index(): array
    {
        return Pedido::todos();
    }

    /**
     * Retorna o carrinho armazenado na sessão.
     * O carrinho será um array associativo: [ 'id_produto' => quantidade, ... ]
     *
     * @return array
     */
    private function _getCart(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    /**
     * Salva o carrinho na sessão.
     *
     * @param array $cart O array do carrinho a ser salvo.
     * @return void
     */
    private function _saveCart(array $cart): void
    {
        $_SESSION['cart'] = $cart;
    }

    /**
     * Limpa o carrinho da sessão.
     *
     * @return void
     */
    private function _clearCart(): void
    {
        unset($_SESSION['cart']);
    }

    /**
     * Exibe o conteúdo atual do carrinho.
     * Corresponde à rota GET /carrinho
     *
     * @return array Dados do carrinho para a View.
     */
    public function verCarrinho(): array
    {
        $cartData = $this->_getCart();
        $itensCarrinho = [];
        $subtotal = 0.0;

        foreach ($cartData as $idProduto => $quantidade) {
            $produto = Produto::buscar($idProduto);
            if ($produto) {
                $precoUnitario = $produto->getPrecoVenda();
                $subtotalItem = $quantidade * $precoUnitario;
                $itensCarrinho[] = [
                    'produto' => $produto,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $precoUnitario,
                    'subtotal_item' => $subtotalItem
                ];
                $subtotal += $subtotalItem;
            } else {
                unset($cartData[$idProduto]);
                $this->_saveCart($cartData);
            }
        }

        $frete = $this->_calcularFrete($subtotal);
        $totalComFrete = $subtotal + $frete;

        return [
            'itens_carrinho' => $itensCarrinho,
            'subtotal' => $subtotal,
            'frete' => $frete,
            'total_com_frete' => $totalComFrete
        ];
    }

    /**
     * Adiciona um produto ao carrinho.
     * Corresponde à rota POST /carrinho/adicionar
     * Os dados esperados no POST são 'id_produto' e 'quantidade'.
     *
     * @return void
     */
    public function adicionarAoCarrinho(): void
    {
        $idProduto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);
        $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

        if (!$idProduto || $quantidade <= 0) {
            $_SESSION['error_message'] = "Dados inválidos para adicionar ao carrinho.";
            header('Location: /produtos');
            exit();
        }

        $produto = Produto::buscar($idProduto);
        if (!$produto) {
            $_SESSION['error_message'] = "Produto não encontrado.";
            header('Location: /produtos');
            exit();
        }

        $cart = $this->_getCart();
        $cart[$idProduto] = ($cart[$idProduto] ?? 0) + $quantidade;
        $this->_saveCart($cart);

        $_SESSION['success_message'] = "Produto '{$produto->getNome()}' adicionado ao carrinho.";
        header('Location: /carrinho');
        exit();
    }

    /**
     * Atualiza a quantidade de um produto no carrinho.
     * Corresponde à rota POST /carrinho/atualizar
     * Os dados esperados no POST são 'id_produto' e 'quantidade'.
     * Se quantidade for 0, remove o item.
     *
     * @return void
     */
    public function atualizarCarrinho(): void
    {
        $idProduto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);
        $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

        if (!$idProduto || $quantidade < 0) {
            $_SESSION['error_message'] = "Dados inválidos para atualizar o carrinho.";
            header('Location: /carrinho');
            exit();
        }

        $cart = $this->_getCart();

        if ($quantidade === 0) {
            unset($cart[$idProduto]);
            $_SESSION['success_message'] = "Produto removido do carrinho.";
        } elseif (isset($cart[$idProduto])) {
            $cart[$idProduto] = $quantidade;
            $_SESSION['success_message'] = "Quantidade do produto atualizada no carrinho.";
        } else {
            $_SESSION['error_message'] = "Produto não encontrado no carrinho para atualização.";
        }

        $this->_saveCart($cart);
        header('Location: /carrinho');
        exit();
    }

    /**
     * Remove um produto do carrinho.
     * Corresponde à rota POST /carrinho/remover
     * O dado esperado no POST é 'id_produto'.
     *
     * @return void
     */
    public function removerDoCarrinho(): void
    {
        $idProduto = filter_input(INPUT_POST, 'id_produto', FILTER_VALIDATE_INT);

        if (!$idProduto) {
            $_SESSION['error_message'] = "ID do produto inválido para remover do carrinho.";
            header('Location: /carrinho');
            exit();
        }

        $cart = $this->_getCart();
        if (isset($cart[$idProduto])) {
            unset($cart[$idProduto]);
            $this->_saveCart($cart);
            $_SESSION['success_message'] = "Produto removido do carrinho.";
        } else {
            $_SESSION['error_message'] = "Produto não encontrado no carrinho para remoção.";
        }

        header('Location: /carrinho');
        exit();
    }

    /**
     * Exibe a página de checkout.
     * Corresponde à rota GET /checkout
     *
     * @return array Dados para a view de checkout.
     */
    public function checkout(): array
    {
        $cartData = $this->_getCart();
        if (empty($cartData)) {
            $_SESSION['error_message'] = "Seu carrinho está vazio. Adicione produtos antes de ir para o checkout.";
            header('Location: /carrinho');
            exit();
        }

        $pedido = new Pedido();
        $errosEstoque = [];

        foreach ($cartData as $idProduto => $quantidadeNoCarrinho) {
            $produto = Produto::buscar($idProduto);
            if (!$produto) {
                $errosEstoque[] = "Produto com ID {$idProduto} não encontrado e será removido do carrinho.";
                unset($cartData[$idProduto]);
                continue;
            }

            $quantidadeDisponivel = 0;
            $itensEstoqueProduto = ItemEstoque::encontrarPorIDDoProduto($idProduto);
            foreach ($itensEstoqueProduto as $itemEstoque) {
                $quantidadeDisponivel += $itemEstoque->getQuantidadeAtual();
            }

            if ($quantidadeNoCarrinho > $quantidadeDisponivel) {
                $errosEstoque[] = "Estoque insuficiente para '{$produto->getNome()}'. Disponível: {$quantidadeDisponivel}, No carrinho: {$quantidadeNoCarrinho}.";
            }

            $itemPedido = new ItemPedido(0, $idProduto, $quantidadeNoCarrinho, $produto->getPrecoVenda());
            $pedido->adicionarItem($itemPedido);
        }

        $this->_saveCart($cartData);

        if (!empty($errosEstoque)) {
            $_SESSION['error_message'] = "Problemas de estoque detectados: <br>" . implode("<br>", $errosEstoque);
            header('Location: /carrinho');
            exit();
        }

        $subtotal = $pedido->calcularSubtotal();
        $valorComDescontos = $pedido->calcularTotalComDescontos();
        $frete = $pedido->calcularFrete($valorComDescontos);
        $totalFinal = $valorComDescontos + $frete;

        return [
            'itens_carrinho' => $pedido->getItens(),
            'subtotal' => $subtotal,
            'valor_com_descontos' => $valorComDescontos,
            'frete' => $frete,
            'total_final' => $totalFinal,
            'cupons_disponiveis' => Cupom::todos()
        ];
    }

    /**
     * Processa a finalização do pedido.
     * Corresponde à rota POST /pedido/finalizar
     *
     * @return void
     */
    public function finalizarPedido(): void
    {
        $cartData = $this->_getCart();

        if (empty($cartData)) {
            $_SESSION['error_message'] = "Seu carrinho está vazio. Não é possível finalizar um pedido vazio.";
            header('Location: /carrinho');
            exit();
        }

        $cep = filter_input(INPUT_POST, 'cep', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $codigoCupom = filter_input(INPUT_POST, 'cupom_codigo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


        $endereco = null;
        if (!empty($cep)) {
            $endereco = $this->_validarCep($cep);
            if ($endereco === null) {
                $_SESSION['error_message'] = "CEP inválido ou não encontrado: {$cep}.";
                header('Location: /checkout');
                exit();
            }
        }

        $pedido = new Pedido();
        $pedido->setStatus('CONFIRMADO');
        // $pedido->setIdCliente(...) // FUTURAMENTE SE OUVER LOGIN

        $valorTotalDosItens = 0.0;
        foreach ($cartData as $idProduto => $quantidadeNoCarrinho) {
            $produto = Produto::buscar($idProduto);
            if (!$produto) {
                $_SESSION['error_message'] = "Erro: Produto ID {$idProduto} não encontrado durante a finalização do pedido.";
                header('Location: /carrinho');
                exit();
            }

            $quantidadeDisponivel = 0;
            $itensEstoqueProduto = ItemEstoque::encontrarPorIDDoProduto($idProduto);
            foreach ($itensEstoqueProduto as $itemEstoque) {
                $quantidadeDisponivel += $itemEstoque->getQuantidadeAtual();
            }

            if ($quantidadeNoCarrinho > $quantidadeDisponivel) {
                $_SESSION['error_message'] = "Estoque insuficiente para '{$produto->getNome()}'. Disponível: {$quantidadeDisponivel}, No carrinho: {$quantidadeNoCarrinho}.";
                header('Location: /carrinho');
                exit();
            }

            $itemPedido = new ItemPedido(0, $idProduto, $quantidadeNoCarrinho, $produto->getPrecoVenda());
            $pedido->adicionarItem($itemPedido);
        }

        if (!empty($codigoCupom)) {
            $cupom = Cupom::buscarPorCodigo($codigoCupom);
            if ($cupom && $cupom->validarCupom()) {
                $pedido->adicionarCupom($cupom);
            } else {
                $_SESSION['warning_message'] = "O cupom '{$codigoCupom}' não é válido ou está expirado e não será aplicado.";
            }
        }

        $pedido->setValorTotal($pedido->calcularTotalFinal());

        try {
            if ($pedido->salvarOuAtualizar()) {
                //die('aqui');
                foreach ($pedido->getItens() as $item) {
                    $quantidadeRemover = $item->getQuantidade();
                    // LÓGICA PARA REMOVER A QUANTIDADE DO ESTOQUE:
                    // ENCONTRAR O ITEMESTOQUE PARA O PRODUTO, E REMOVER A QUANTIDADE.
                    // ISSO PODE SER MAIS COMPLEXO SE PRECISAR ESCOLHER QUAL LOCALIZAÇÃO DE ESTOQUE DAR BAIXA.
                    // POR SIMPLICIDADE, VAMOS REMOVER DO PRIMEIRO ITEMESTOQUE ENCONTRADO PARA O PRODUTO.
                    $itensEstoqueProduto = ItemEstoque::encontrarPorIDDoProduto($item->getIdProduto());
                    if (!empty($itensEstoqueProduto)) {
                        $primeiroItemEstoque = $itensEstoqueProduto[0]; // Pega o primeiro local para dar baixa
                        $primeiroItemEstoque->removerQuantidade($quantidadeRemover);
                    } else {
                        error_log("Produto ID: {$item->getIdProduto()} sem registro de estoque para baixa.");
                    }
                }

                $this->_clearCart();

                $_SESSION['success_message'] = "Pedido nº {$pedido->getIdPedido()} finalizado com sucesso!";
                header('Location: /pedido/confirmacao/' . $pedido->getIdPedido());
                exit();
            } else {
                $_SESSION['error_message'] = "Erro ao finalizar o pedido. Tente novamente.";
                header('Location: /checkout');
                exit();
            }
        } catch (InvalidArgumentException $e) {
            $_SESSION['error_message'] = "Erro de validação ao finalizar pedido: " . $e->getMessage();
            header('Location: /checkout');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Erro no banco de dados: " . $e->getMessage();
            header('Location: /checkout');
            exit();
        }
    }

    /**
     * Exibe a página de confirmação do pedido.
     * Corresponde à rota GET /pedido/confirmacao/{id_pedido}
     *
     * @param int $idPedido O ID do pedido finalizado.
     * @return array Dados do pedido para exibição.
     */
    public function confirmacao(int $idPedido): array
    {
        $pedido = Pedido::buscar($idPedido);
        if (!$pedido) {
            $_SESSION['error_message'] = "Pedido não encontrado.";
            header('Location: /pedido');
            exit();
        }

        return [
            'pedido' => $pedido
        ];
    }

    /**
     * Calcula o valor do frete com base no subtotal do pedido.
     * Esta lógica é replicada do Pedido Model para fácil acesso no Controller,
     * mas a fonte da verdade é o Model.
     *
     * @param float $subtotal O subtotal do pedido.
     * @return float O valor do frete.
     */
    private function _calcularFrete(float $subtotal): float
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
     * Valida um CEP usando a API ViaCEP.
     *
     * @param string $cep O CEP a ser validado.
     * @return array|null Dados do endereço se o CEP for válido, ou null se não.
     */
    private function _validarCep(string $cep): ?array
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        if (strlen($cep) !== 8) {
            return null;
        }
        $url = "https://viacep.com.br/ws/{$cep}/json/";

        $response = @file_get_contents($url);
        if ($response === false) {
            error_log("Erro ao consultar ViaCEP para o CEP: {$cep}");
            return null;
        }
        $data = json_decode($response, true);
        if (isset($data['erro']) && $data['erro'] === true) {
            return null;
        }
        return $data;
    }

    /**
     * Deleta um pedido.
     * Corresponde à rota POST /pedido/deletar/{id}
     *
     * @param int $id O ID do pedido a ser deletado.
     * @return void
     */
    public function delete(int $id): void
    {
        if (Pedido::delete($id)) {
            $_SESSION['success_message'] = "Pedido deletado com sucesso!";
            header('Location: /pedidos');
            exit();
        } else {
            $_SESSION['error_message'] = "Erro ao deletar o pedido.";
            header('Location: /pedidos');
            exit();
        }
    }

}