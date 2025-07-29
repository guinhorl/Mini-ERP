<?php
$pedidos = $pedidos ?? [];
?>

    <h1 class="mb-4">Lista de Pedidos</h1>

<?php if (empty($pedidos)): ?>
    <div class="alert alert-info" role="alert">
        Nenhum pedido encontrado. Finalize um pedido no <a href="/checkout" class="alert-link">Checkout</a> para vê-lo aqui!
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
            <tr>
                <th scope="col"># Pedido</th>
                <th scope="col">Data</th>
                <th scope="col">Status</th>
                <th scope="col" class="text-end">Valor Total</th>
                <th scope="col" class="text-center">Itens</th>
                <th scope="col" class="text-center">Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pedido->getIdPedido() ?? ''); ?></td>
                    <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($pedido->getDataPedido() ?? ''))); ?></td>
                    <td>
                        <?php
                        $statusClass = 'bg-secondary';
                        switch ($pedido->getStatus() ?? '') {
                            case 'PENDENTE': $statusClass = 'bg-warning text-dark'; break;
                            case 'CONFIRMADO': $statusClass = 'bg-primary'; break;
                            case 'ENVIADO': $statusClass = 'bg-info text-dark'; break;
                            case 'ENTREGUE': $statusClass = 'bg-success'; break;
                            case 'CANCELADO': $statusClass = 'bg-danger'; break;
                        }
                        ?>
                        <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($pedido->getStatus() ?? ''); ?></span>
                    </td>
                    <td class="text-end">R$ <?php echo number_format($pedido->getValorTotal() ?? 0.0, 2, ',', '.'); ?></td>
                    <td class="text-center"><?php echo count($pedido->getItens()); ?></td>
                    <td class="text-center">
                        <a href="/pedido/confirmacao/<?php echo htmlspecialchars($pedido->getIdPedido() ?? ''); ?>" class="btn btn-sm btn-info me-2">Ver Detalhes</a>
                        <form action="/pedido/deletar/<?php echo htmlspecialchars($pedido->getIdPedido() ?? ''); ?>" method="POST" style="display:inline-block;">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja deletar este pedido?');">Deletar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>