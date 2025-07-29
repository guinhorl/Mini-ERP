<?php
//die('dashboard.php');

if (!isset($estoqueDetalhado) || !is_array($estoqueDetalhado)) {
    echo '<div class="alert alert-danger" role="alert">Erro ao carregar dados do estoque.</div>';
    return;
}
?>

<h1 class="mb-4">Visão Geral do Estoque</h1>

<?php if (empty($estoqueDetalhado)): ?>
    <div class="alert alert-info" role="alert">
        Nenhum produto com registro de estoque encontrado. Cadastre um produto para vê-lo aqui ou movimente o estoque.
    </div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>ID Produto</th>
            <th>Nome do Produto</th>
            <th>SKU</th>
            <th>Estoque Total</th>
            <th>Localizações e Quantidades</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($estoqueDetalhado as $produtoData): ?>
            <tr>
                <td><?php echo htmlspecialchars($produtoData['id_produto'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($produtoData['nome'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($produtoData['sku'] ?? ''); ?></td>
                <td>
                            <span class="badge bg-<?php echo (($produtoData['estoque_total'] ?? 0) > 0 ? 'success' : 'danger'); ?>">
                                <?php echo htmlspecialchars($produtoData['estoque_total'] ?? 0); ?>
                            </span>
                </td>
                <td>
                    <?php if (empty($produtoData['locais'] ?? [])): ?>
                        <span class="text-muted">Sem registro de estoque em locais específicos.</span>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0"> <?php foreach ($produtoData['locais'] as $local): ?>
                                <li>
                                    <?php echo htmlspecialchars($local['localizacao_nome'] ?? ''); ?>:
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($local['quantidade'] ?? 0); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/estoque/movimentar/<?php echo htmlspecialchars($produtoData['id_produto'] ?? ''); ?>" class="btn btn-sm btn-primary">Movimentar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>