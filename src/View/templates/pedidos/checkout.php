<?php
$itens_carrinho = $itens_carrinho ?? [];
$subtotal = $subtotal ?? 0.0;
$valor_com_descontos = $valor_com_descontos ?? 0.0;
$frete = $frete ?? 0.0;
$total_final = $total_final ?? 0.0;
$cupons_disponiveis = $cupons_disponiveis ?? [];

?>

    <h1 class="mb-4">Finalizar Pedido</h1>

<?php if (empty($itens_carrinho)): ?>
    <div class="alert alert-info" role="alert">
        Seu carrinho está vazio. <a href="/produtos" class="alert-link">Adicione produtos</a> antes de finalizar.
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-8">
            <h2 class="h5">Itens do Pedido</h2>
            <div class="table-responsive mb-4">
                <table class="table table-striped table-sm">
                    <thead>
                    <tr>
                        <th>Produto</th>
                        <th class="text-center">Qtd</th>
                        <th class="text-center">Preço Unit.</th>
                        <th class="text-center">Subtotal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($itens_carrinho as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item->getProduto()->getNome() ?? 'Produto Desconhecido'); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($item->getQuantidade() ?? 0); ?></td>
                            <td class="text-center">R$ <?php echo number_format($item->getPrecoUnitario() ?? 0.0, 2, ',', '.'); ?></td>
                            <td class="text-center">R$ <?php echo number_format($item->calcularSubtotal() ?? 0.0, 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h2 class="h5">Informações de Entrega e Pagamento</h2>
            <form action="/pedidos/finalizar" method="POST" class="mb-4">
                <div class="mb-3">
                    <label for="cep" class="form-label">CEP</label>
                    <input type="text" class="form-control" id="cep" name="cep" placeholder="Ex: 00000-000" pattern="\d{5}-?\d{3}" maxlength="9" required>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="btnBuscarCep">Buscar Endereço</button>
                    <div id="enderecoInfo" class="mt-2"></div>
                </div>

                <div class="mb-3">
                    <label for="cupom_codigo" class="form-label">Código do Cupom (Opcional)</label>
                    <input type="text" class="form-control" id="cupom_codigo" name="cupom_codigo" placeholder="Digite o código do cupom">
                    <?php if (!empty($cupons_disponiveis)): ?>
                        <div class="form-text">Cupons disponíveis:
                            <?php foreach ($cupons_disponiveis as $cupom): ?>
                                <?php if ($cupom->validarCupom()): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($cupom->getCodigo()); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                </div>

                <hr class="my-4">

                <button type="submit" class="btn btn-success btn-lg">Finalizar Pedido</button>
                <a href="/carrinho" class="btn btn-secondary btn-lg ms-2">Voltar ao Carrinho</a>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    Resumo do Pedido
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Subtotal:
                        <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Desconto:
                        <span>R$ <?php echo number_format($subtotal - $valor_com_descontos, 2, ',', '.'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Frete:
                        <span>R$ <?php echo number_format($frete, 2, ',', '.'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
                        Total Final:
                        <span>R$ <?php echo number_format($total_final, 2, ',', '.'); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('btnBuscarCep').addEventListener('click', function() {
            const cep = document.getElementById('cep').value.replace(/\D/g, ''); // Remove não-dígitos
            const enderecoInfo = document.getElementById('enderecoInfo');
            enderecoInfo.innerHTML = '<div class="text-info">Buscando CEP...</div>';

            if (cep.length !== 8) {
                enderecoInfo.innerHTML = '<div class="text-danger">CEP deve ter 8 dígitos.</div>';
                return;
            }

            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        enderecoInfo.innerHTML = '<div class="text-danger">CEP não encontrado.</div>';
                    } else {
                        enderecoInfo.innerHTML = `
                            <div class="alert alert-success">
                                <p class="mb-0"><strong>Endereço:</strong> ${data.logradouro}, ${data.bairro}</p>
                                <p class="mb-0"><strong>Cidade/UF:</strong> ${data.localidade}/${data.uf}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar CEP:', error);
                    enderecoInfo.innerHTML = '<div class="text-danger">Erro ao buscar CEP. Tente novamente.</div>';
                });
        });
    </script>

<?php endif; ?>