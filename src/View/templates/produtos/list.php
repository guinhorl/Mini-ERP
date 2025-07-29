<?php
use App\Model\ItemEstoque;
?>
    <h1 class="mb-4">Lista de Produtos</h1>

    <a href="/produtos/novo" class="btn btn-success mb-3">Adicionar Novo Produto</a>

<?php if (empty($produtos)): ?>
    <div class="alert alert-info" role="alert">
        Nenhum produto cadastrado.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Preço de Venda</th>
                <th>SKU</th>
                <th>Estoque Total</th> <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?php echo htmlspecialchars($produto->getIdProduto() ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($produto->getNome() ?? ''); ?></td>
                    <td>R$ <?php echo number_format($produto->getPrecoVenda() ?? 0.0, 2, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($produto->getSku() ?? ''); ?></td>
                    <td>
                        <?php
                        // Lógica para exibir o estoque total do produto
                        // No ProdutoController::index(), precisamos buscar o estoque total de cada produto
                        // e adicioná-lo ao objeto $produto ou passá-lo separadamente para a view.
                        // Por agora, vamos buscar aqui para simplificar a demonstração, mas o ideal é no Controller.

                        $estoqueTotalProduto = 0;
                        $itensEstoqueDoProduto = ItemEstoque::encontrarPorIDDoProduto($produto->getIdProduto());
                        foreach ($itensEstoqueDoProduto as $itemEstoque) {
                            $estoqueTotalProduto += $itemEstoque->getQuantidadeAtual();
                        }
                        ?>
                        <span class="badge bg-<?php echo ($estoqueTotalProduto > 0 ? 'success' : 'danger'); ?>"><?php echo htmlspecialchars($estoqueTotalProduto); ?></span>
                    </td>
                    <td>
                        <form action="/carrinho/adicionar" method="POST" class="d-inline-block me-2">
                            <input type="hidden" name="id_produto" value="<?php echo htmlspecialchars($produto->getIdProduto() ?? ''); ?>">
                            <input type="number" name="quantidade" value="1" min="1" class="form-control form-control-sm d-inline-block" style="width: 60px;">
                            <button type="submit" class="btn btn-sm btn-primary ms-1" <?php echo ($estoqueTotalProduto <= 0 ? 'disabled' : ''); ?>>Comprar</button>
                        </form>
                        <a href="/produtos/editar/<?php echo htmlspecialchars($produto->getIdProduto() ?? ''); ?>" class="btn btn-sm btn-info me-2">Editar</a>
                        <form action="/produtos/deletar/<?php echo htmlspecialchars($produto->getIdProduto() ?? ''); ?>" method="POST" style="display:inline-block;">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja deletar este produto?');">Deletar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>