<?php
$itens_carrinho = $itens_carrinho ?? [];
$subtotal = $subtotal ?? 0.0;
$frete = $frete ?? 0.0;
$total_com_frete = $total_com_frete ?? 0.0;

?>

    <h1 class="mb-4">Seu Carrinho de Compras</h1>

<?php if (empty($itens_carrinho)): ?>
    <div class="alert alert-info" role="alert">
        Seu carrinho está vazio. <a href="/produtos" class="alert-link">Adicione alguns produtos!</a>
    </div>
<?php else: ?>
    <div class="table-responsive mb-4">
        <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th scope="col">Produto</th>
                <th scope="col">SKU</th>
                <th scope="col" class="text-center">Preço Unit.</th>
                <th scope="col" class="text-center">Quantidade</th>
                <th scope="col" class="text-center">Subtotal</th>
                <th scope="col" class="text-center">Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($itens_carrinho as $item): ?>
                <tr>
                    <td>
                        <?php echo htmlspecialchars($item['produto']->getNome() ?? 'Produto Desconhecido'); ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['produto']->getSku() ?? ''); ?></td>
                    <td class="text-center">R$ <?php echo number_format($item['preco_unitario'] ?? 0.0, 2, ',', '.'); ?></td>
                    <td class="text-center">
                        <form action="/carrinho/atualizar" method="POST" class="d-inline-flex">
                            <input type="hidden" name="id_produto" value="<?php echo htmlspecialchars($item['produto']->getIdProduto() ?? ''); ?>">
                            <input type="number" name="quantidade" value="<?php echo htmlspecialchars($item['quantidade'] ?? 0); ?>" min="0" class="form-control form-control-sm text-center" style="width: 80px;" onchange="this.form.submit()">
                        </form>
                    </td>
                    <td class="text-center">R$ <?php echo number_format($item['subtotal_item'] ?? 0.0, 2, ',', '.'); ?></td>
                    <td class="text-center">
                        <form action="/carrinho/remover" method="POST" style="display:inline-block;">
                            <input type="hidden" name="id_produto" value="<?php echo htmlspecialchars($item['produto']->getIdProduto() ?? ''); ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        <div class="col-md-5">
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
                        Frete Provisório:
                        <span>R$ <?php echo number_format($frete, 2, ',', '.'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
                        Total Geral:
                        <span>R$ <?php echo number_format($total_com_frete, 2, ',', '.'); ?></span>
                    </li>
                </ul>
                <div class="card-body text-end">
                    <a href="/produtos" class="btn btn-secondary me-2">Continuar Comprando</a>
                    <a href="/checkout" class="btn btn-primary <?php echo (empty($itens_carrinho) ? 'disabled' : ''); ?>">Ir para o Checkout</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>