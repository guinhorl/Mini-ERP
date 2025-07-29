<?php
if (!isset($produto) || $produto === null) {
    echo '<div class="alert alert-danger" role="alert">Produto não encontrado para movimentação de estoque.</div>';
    return;
}
?>

<h1 class="mb-4">Movimentar Estoque para: <?php echo htmlspecialchars($produto->getNome()); ?></h1>

<div class="card mb-4">
    <div class="card-header">
        Detalhes do Produto
    </div>
    <div class="card-body">
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($produto->getNome()); ?></p>
        <p><strong>SKU:</strong> <?php echo htmlspecialchars($produto->getSku()); ?></p>
        <p><strong>Preço:</strong> R$ <?php echo number_format($produto->getPrecoVenda(), 2, ',', '.'); ?></p>
    </div>
</div>

<form action="/estoque/processar-movimentacao" method="POST">
    <input type="hidden" name="id_produto" value="<?php echo htmlspecialchars($produto->getIdProduto()); ?>">

    <div class="mb-3">
        <label for="id_estoque" class="form-label">Local de Estoque</label>
        <select class="form-select" id="id_estoque" name="id_estoque" required>
            <option value="">Selecione um local</option>
            <?php foreach ($estoquesDisponiveis as $estoque): ?>
                <option value="<?php echo htmlspecialchars($estoque->getIdEstoque()); ?>"
                    <?php
                    if (isset($itemEstoque) && $itemEstoque !== null && $itemEstoque->getIdEstoque() === $estoque->getIdEstoque()) {
                        echo 'selected';
                    }
                    ?>
                >
                    <?php echo htmlspecialchars($estoque->getLocalizacao()); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($estoquesDisponiveis)): ?>
            <div class="form-text">
                Se o produto não tiver registro de estoque para o local selecionado, um novo registro será criado com quantidade 0 antes da movimentação.
            </div>
        <?php else: ?>
            <div class="alert alert-warning mt-2">Nenhuma localização de estoque disponível. Por favor, adicione uma em <a href="/estoque/localizacoes/nova">Gerenciar Localizações</a>.</div>
        <?php endif; ?>
    </div>

    <?php if (isset($itemEstoque) && $itemEstoque !== null): ?>
        <div class="mb-3">
            <label class="form-label">Quantidade Atual em <?php echo htmlspecialchars($localizacaoAtual); ?></label>
            <input type="text" class="form-control" value="<?php echo htmlspecialchars($itemEstoque->getQuantidadeAtual()); ?>" readonly>
        </div>
    <?php else: ?>
        <div class="alert alert-info mt-3" role="alert">
            Este produto não possui registro de estoque para a localização selecionada. Será criado um registro com 0 unidades.
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="quantidade" class="form-label">Quantidade para Movimentar</label>
        <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" required>
        <div class="form-text">Informe a quantidade inteira para adicionar ou remover.</div>
    </div>

    <div class="mb-3">
        <label for="tipo_movimentacao" class="form-label">Tipo de Movimentação</label>
        <div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tipo_movimentacao" id="adicionar" value="adicionar" checked>
                <label class="form-check-label" for="adicionar">Adicionar (Entrada)</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tipo_movimentacao" id="remover" value="remover">
                <label class="form-check-label" for="remover">Remover (Saída)</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Processar Movimentação</button>
    <a href="/estoque" class="btn btn-secondary">Cancelar</a>
</form>