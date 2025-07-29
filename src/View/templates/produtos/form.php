<?php
use App\Model\Produto;

$isEditing = isset($produto) && $produto->getIdProduto() !== null;
$formTitle = $isEditing ? 'Editar Produto' : 'Novo Produto';
$formAction = $isEditing ? '/produtos/atualizar' : '/produtos/salvar';


//GARANTE QUE $produto EXISTA MEMSO PARA UM NOVO FORMULARIO, EVITAR ERROS DE "Undefined Variable"
if (!$isEditing) {
    $produto = new Produto();
}
?>

<h1 class="mb-4"><?php echo $formTitle; ?></h1>

<form action="<?php echo $formAction; ?>" method="POST">
    <?php if ($isEditing): ?>
        <input type="hidden" name="id_produto" value="<?php echo htmlspecialchars($produto->getIdProduto()); ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="nome" class="form-label">Nome do Produto</label>
        <input type="text" class="form-control" id="nome" name="nome" value="<?= $produto->getNome() ?? '' ?>" required>
    </div>

    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição</label>
        <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo htmlspecialchars($produto->getDescricao() ?? ''); ?></textarea>
    </div>

    <div class="mb-3">
        <label for="preco_venda" class="form-label">Preço de Venda (R$)</label>
        <input type="number" class="form-control" id="preco_venda" name="preco_venda" step="0.01" min="0" value="<?php echo htmlspecialchars($produto->getPrecoVenda() ?? ''); ?>" required>
    </div>

    <div class="mb-3">
        <label for="sku" class="form-label">SKU</label>
        <input type="text" class="form-control" id="sku" name="sku" value="<?php echo htmlspecialchars($produto->getSku() ?? ''); ?>" required>
    </div>

    <button type="submit" class="btn btn-primary"><?php echo $isEditing ? 'Atualizar' : 'Salvar'; ?> Produto</button>
    <a href="/produtos" class="btn btn-secondary">Cancelar</a>
</form>