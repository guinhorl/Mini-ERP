<?php

$isEditing = isset($estoque) && $estoque->getIdEstoque() !== null;
$formTitle = $isEditing ? 'Editar Localização de Estoque' : 'Nova Localização de Estoque';
$formAction = $isEditing ? '/estoque/localizacoes/atualizar' : '/estoque/localizacoes/salvar';


if (!$isEditing) {
    $estoque = new \App\Model\Estoque();
}
?>

<h1 class="mb-4"><?php echo $formTitle; ?></h1>

<form action="<?php echo $formAction; ?>" method="POST">
    <?php if ($isEditing): ?>
        <input type="hidden" name="id_estoque" value="<?php echo htmlspecialchars($estoque->getIdEstoque()); ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="localizacao_nome" class="form-label">Nome da Localização</label>
        <input type="text" class="form-control" id="localizacao_nome" name="localizacao_nome" value="<?php echo htmlspecialchars($estoque->getLocalizacao() ?? ''); ?>" required>
    </div>

    <button type="submit" class="btn btn-primary"><?php echo $isEditing ? 'Atualizar' : 'Salvar'; ?> Localização</button>
    <a href="/estoque/localizacoes" class="btn btn-secondary">Cancelar</a>
</form>