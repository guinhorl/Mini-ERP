<?php
if (!isset($pedido) || $pedido === null) {
    echo '<div class="alert alert-danger" role="alert">Detalhes do pedido não encontrados.</div>';
    return;
}
?>

<h1 class="mb-4">Pedido Confirmado!</h1>

<div class="alert alert-success mb-4" role="alert">
    Seu pedido nº <strong><?php echo htmlspecialchars($pedido->getIdPedido() ?? ''); ?></strong> foi finalizado com sucesso!
</div>

<div class="card mb-4">
    <div class="card-header">
        Detalhes do Pedido
    </div>
    <div class="card-body">
        <p><strong>Número do Pedido:</strong> <?php echo htmlspecialchars($pedido->getIdPedido() ?? ''); ?></p>
        <p><strong>Data do Pedido:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($pedido->getDataPedido() ?? ''))); ?></p>
        <p><strong>Status:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($pedido->getStatus() ?? ''); ?></span></p>
        <p><strong>Valor Total:</strong> R$ <?php echo number_format($pedido->getValorTotal() ?? 0.0, 2, ',', '.'); ?></p>
    </div>
</div>

<h2 class="h5">Itens do Pedido</h2>
<?php $itens = $pedido->getItens(); ?>
<?php if (empty($itens)): ?>
    <div class="alert alert-warning" role="alert">Nenhum item encontrado para este pedido.</div>
<?php else: ?>
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
            <?php foreach ($itens as $item): ?>
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
<?php endif; ?>

<h2 class="h5">Cupons Aplicados</h2>
<?php $cupons = $pedido->getCuponsAplicados(); ?>
<?php if (empty($cupons)): ?>
    <div class="alert alert-info" role="alert">Nenhum cupom aplicado a este pedido.</div>
<?php else: ?>
    <ul class="list-group mb-4">
        <?php foreach ($cupons as $cupom): ?>
            <li class="list-group-item">
                <strong><?php echo htmlspecialchars($cupom->getCodigo() ?? ''); ?></strong> (<?php echo htmlspecialchars($cupom->getTipoDesconto() ?? ''); ?>:
                <?php
                if (($cupom->getTipoDesconto() ?? '') === 'PERCENTUAL') {
                    echo htmlspecialchars(number_format($cupom->getValorDesconto() ?? 0.0, 2, ',', '.')) . '%';
                } else {
                    echo 'R$ ' . htmlspecialchars(number_format($cupom->getValorDesconto() ?? 0.0, 2, ',', '.'));
                }
                ?>)
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<div class="text-end">
    <a href="/pedidos" class="btn btn-primary">Ver Meus Pedidos</a>
    <a href="/produtos" class="btn btn-secondary">Comprar Mais</a>
</div>