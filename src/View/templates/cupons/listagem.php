<h1 class="mb-4">Gerenciamento de Cupons</h1>

<a href="/cupons/novo" class="btn btn-success mb-3">Adicionar Novo Cupom</a>

<?php if (empty($cupons)): ?>
    <div class="alert alert-info" role="alert">
        Nenhum cupom cadastrado.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Validade</th>
                <th>Ativo</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($cupons as $cupom): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cupom->getIdCupom()); ?></td>
                    <td><?php echo htmlspecialchars($cupom->getCodigo()); ?></td>
                    <td><?php echo htmlspecialchars($cupom->getTipoDesconto()); ?></td>
                    <td>
                        <?php
                        if ($cupom->getTipoDesconto() === 'PERCENTUAL') {
                            echo htmlspecialchars(number_format($cupom->getValorDesconto(), 2, ',', '.')) . '%';
                        } else {
                            echo 'R$ ' . htmlspecialchars(number_format($cupom->getValorDesconto(), 2, ',', '.'));
                        }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($cupom->getDataValidade()))); ?></td>
                    <td>
                        <?php if ($cupom->isAtivo()): ?>
                            <span class="badge bg-success">Sim</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Não</span>
                        <?php endif; ?>
                        <?php if (!$cupom->validarCupom()): ?>
                            <span class="badge bg-warning text-dark">Expirado/Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/cupons/editar/<?php echo $cupom->getIdCupom(); ?>" class="btn btn-sm btn-info me-2">Editar</a>
                        <form action="/cupons/deletar/<?php echo $cupom->getIdCupom(); ?>" method="POST"
                              style="display:inline-block;">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Tem certeza que deseja deletar este cupom?');">Deletar
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>