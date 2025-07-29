<?php
$isEditing = isset($cupom) && $cupom->getIdCupom() !== null;
$formTitle = $isEditing ? 'Editar Cupom' : 'Novo Cupom';
$formAction = $isEditing ? '/cupons/atualizar' : '/cupons/salvar';

if (!$isEditing) {
    $cupom = new \App\Model\Cupom();
}
?>

<h1 class="mb-4"><?php echo $formTitle; ?></h1>

<form action="<?php echo $formAction; ?>" method="POST">
    <?php if ($isEditing): ?>
        <input type="hidden" name="id_cupom" value="<?php echo htmlspecialchars($cupom->getIdCupom()); ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="codigo" class="form-label">Código do Cupom</label>
        <input type="text" class="form-control" id="codigo" name="codigo" value="<?php echo htmlspecialchars($cupom->getCodigo() ?? ''); ?>" required>
    </div>

    <div class="mb-3">
        <label for="tipo_desconto" class="form-label">Tipo de Desconto</label>
        <select class="form-select" id="tipo_desconto" name="tipo_desconto" required>
            <option value="">Selecione o tipo</option>
            <option value="PERCENTUAL" <?php echo (($cupom->getTipoDesconto() ?? '') === 'PERCENTUAL' ? 'selected' : ''); ?>>Percentual</option>
            <option value="FIXO" <?php echo (($cupom->getTipoDesconto() ?? '') === 'FIXO' ? 'selected' : ''); ?>>Fixo (R$)</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="valor_desconto" class="form-label">Valor do Desconto</label>
        <input type="number" class="form-control" id="valor_desconto" name="valor_desconto" step="0.01" min="0" value="<?php echo htmlspecialchars($cupom->getValorDesconto() ?? ''); ?>" required>
        <div class="form-text">Para percentual, digite 10 para 10%. Para fixo, digite 50.00 para R$50,00.</div>
    </div>

    <div class="mb-3">
        <label for="data_validade" class="form-label">Data de Validade</label>
        <input type="date" class="form-control" id="data_validade" name="data_validade" value="<?php echo htmlspecialchars($cupom->getDataValidade() ?? ''); ?>" required>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="ativo" name="ativo" <?php echo (($cupom->isAtivo() ?? true) ? 'checked' : ''); ?>>
        <label class="form-check-label" for="ativo">Ativo</label>
    </div>

    <button type="submit" class="btn btn-primary"><?php echo $isEditing ? 'Atualizar' : 'Salvar'; ?> Cupom</button>
    <a href="/cupons" class="btn btn-secondary">Cancelar</a>
</form>