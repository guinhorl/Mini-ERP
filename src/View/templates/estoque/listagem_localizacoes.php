<h1 class="mb-4">Localizações de Estoque</h1>

<a href="/estoque/localizacoes/nova" class="btn btn-success mb-3">Adicionar Nova Localização</a>

<?php if (empty($localizacoes)): ?>
    <div class="alert alert-warning" role="alert">
        Nenhuma localização de estoque cadastrada.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th>ID</th>
                <th>Localização</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($localizacoes as $localizacao): ?>
                <tr>
                    <td><?php echo htmlspecialchars($localizacao->getIdEstoque()); ?></td>
                    <td><?php echo htmlspecialchars($localizacao->getLocalizacao()); ?></td>
                    <td>
                        <a href="/estoque/localizacoes/editar/<?php echo $localizacao->getIdEstoque(); ?>"
                           class="btn btn-sm btn-info me-2">Editar</a>
                        <form action="/estoque/localizacoes/deletar/<?php echo $localizacao->getIdEstoque(); ?>"
                              method="POST" style="display:inline-block;">
                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Tem certeza que deseja deletar esta localização? Isso pode afetar itens de estoque associados!');">
                                Deletar
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>