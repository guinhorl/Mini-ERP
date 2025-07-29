<?php
// public/index.php

// 1. INÍCIO DA SESSÃO - DEVE SER A PRIMEIRA COISA PHP EXECUTADA
session_start();

// 2. INCLUI O AUTOLOADER DO COMPOSER
require __DIR__ . '/../vendor/autoload.php';

// 3. CARREGA AS VARIÁVEIS DE AMBIENTE
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 4. DEFINE O TRATAMENTO DE ERROS (PARA DESENVOLVIMENTO)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 5. IMPORTA CONTROLADORES NECESSÁRIOS
use App\Controller\ProdutoController;
use App\Controller\EstoqueController;
use App\Controller\CupomController;
use App\Controller\PedidoController;

// 6. OBTÉM INFORMAÇÕES DA REQUISIÇÃO
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

// 7. INSTANCIA CONTROLADORES
$produtoController = new ProdutoController();
$estoqueController = new EstoqueController();
$cupomController = new CupomController();
$pedidoController = new PedidoController();

// 8. LÓGICA DE ROTEAMENTO PRINCIPAL
// --- ROTAS QUE PROCESSAM POSTS

// Rotas de PRODUTOS (POST)
if ($requestPath === '/produtos/salvar' && $requestMethod === 'POST') {
    $produtoController->salvar();
} elseif ($requestPath === '/produtos/atualizar' && $requestMethod === 'POST') {
    $produtoController->atualizar();
} elseif (preg_match('/^\/produtos\/deletar\/(\d+)$/', $requestPath, $matches) && $requestMethod === 'POST') {
    $id = (int) $matches[1];
    $produtoController->delete($id);
}
// ROTAS DE ESTOQUE (POST)
elseif ($requestPath === '/estoque/processar-movimentacao' && $requestMethod === 'POST') {
    $estoqueController->processarMovimentacao();
} elseif ($requestPath === '/estoque/localizacoes/salvar' && $requestMethod === 'POST') {
    $estoqueController->salvarLocalizacao();
} elseif ($requestPath === '/estoque/localizacoes/atualizar' && $requestMethod === 'POST') {
    $estoqueController->atualizarLocalizacao();
} elseif (preg_match('/^\/estoque\/localizacoes\/deletar\/(\d+)$/', $requestPath, $matches) && $requestMethod === 'POST') {
    $id = (int) $matches[1];
    $estoqueController->deletarLocalizacao($id);
}
// ROTAS DE CUPONS (POST)
elseif ($requestPath === '/cupons/salvar' && $requestMethod === 'POST') {
    $cupomController->salvar();
} elseif ($requestPath === '/cupons/atualizar' && $requestMethod === 'POST') {
    $cupomController->atualizar();
} elseif (preg_match('/^\/cupons\/deletar\/(\d+)$/', $requestPath, $matches) && $requestMethod === 'POST') {
    $id = (int) $matches[1];
    $cupomController->deletar($id);
}
// ROTAS DE PEDIDOS (POST)
elseif ($requestPath === '/carrinho/adicionar' && $requestMethod === 'POST') {
    $pedidoController->adicionarAoCarrinho();
} elseif ($requestPath === '/carrinho/atualizar' && $requestMethod === 'POST') {
    $pedidoController->atualizarCarrinho();
} elseif ($requestPath === '/carrinho/remover' && $requestMethod === 'POST') {
    $pedidoController->removerDoCarrinho();
} elseif ($requestPath === '/pedidos/finalizar' && $requestMethod === 'POST') {
    $pedidoController->finalizarPedido();
}
elseif (preg_match('/^\/pedido\/deletar\/(\d+)$/', $requestPath, $matches) && $requestMethod === 'POST') {
    $id = (int) $matches[1];
    $pedidoController->delete($id);
}
// --- FIM DO BLOCO DE ROTAS POST ---


// --- LÓGICA PARA ROTAS GET (QUE RENDEMIZAM HTML) ---
$template = null;
$data = [];
$httpStatusCode = 200;

// Rota Inicial (Home)
if ($requestPath === '/' && $requestMethod === 'GET') {
    $template = 'home';
}
// ROTAS DE PRODUTOS
elseif ($requestPath === '/produtos' && $requestMethod === 'GET') {
    $data['produtos'] = $produtoController->index();
    $template = 'produtos/list';
} elseif ($requestPath === '/produtos/novo' && $requestMethod === 'GET') {
    $data['produto'] = $produtoController->adicionar();
    $template = 'produtos/form';
} elseif (preg_match('/^\/produtos\/editar\/(\d+)$/', $requestPath, $matches) && $requestMethod === 'GET') {
    $id = (int) $matches[1];
    $data['produto'] = $produtoController->editar($id);
    if ($data['produto'] === null) {
        $httpStatusCode = 404;
        $template = '404';
    } else {
        $template = 'produtos/form';
    }
}
// ROTAS DE ESTOQUE (Visão Geral e Movimentação)
elseif ($requestPath === '/estoque' && $requestMethod === 'GET') {
    $data['estoqueDetalhado'] = $estoqueController->index();
    $template = 'estoque/dashboard';
} elseif (preg_match('/^\/estoque\/movimentar\/(\d+)(?:\/(\d+))?$/', $requestPath, $matches) && $requestMethod === 'GET') {
    $idProduto = (int) $matches[1];
    $idEstoque = isset($matches[2]) ? (int) $matches[2] : null;
    $movimentacaoData = $estoqueController->movimentar($idProduto, $idEstoque);

    if ($movimentacaoData === null) {
        $httpStatusCode = 404;
        $template = '404';
    } else {
        $template = 'estoque/movimentar_form';
        $data = array_merge($data, $movimentacaoData);
    }
}
// ROTAS DE LOCALIZAÇÕES DE ESTOQUE
elseif ($requestPath === '/estoque/localizacoes' && $requestMethod === 'GET') {
    $data['localizacoes'] = $estoqueController->listarLocalizacoes();
    $template = 'estoque/listagem_localizacoes';
} elseif ($requestPath === '/estoque/localizacoes/nova' && $requestMethod === 'GET') {
    $data['estoque'] = $estoqueController->criarLocalizacao();
    $template = 'estoque/form_localizacao';
} elseif (preg_match('/^\/estoque\/localizacoes\/editar\/(\d+)$/', $requestPath, $matches) && $requestMethod === 'GET') {
    $id = (int) $matches[1];
    $data['estoque'] = $estoqueController->editarLocalizacao($id);
    if ($data['estoque'] === null) {
        $httpStatusCode = 404;
        $template = '404';
    } else {
        $template = 'estoque/form_localizacao';
    }
}
// ROTAS DE CUPONS
elseif ($requestPath === '/cupons' && $requestMethod === 'GET') {
    $data['cupons'] = $cupomController->index();
    $template = 'cupons/listagem';
} elseif ($requestPath === '/cupons/novo' && $requestMethod === 'GET') {
    $data['cupom'] = $cupomController->novo();
    $template = 'cupons/form';
} elseif (preg_match('/^\/cupons\/editar\/(\d+)$/', $requestPath, $matches) && $requestMethod === 'GET') {
    $id = (int) $matches[1];
    $data['cupom'] = $cupomController->editar($id);
    if ($data['cupom'] === null) {
        $httpStatusCode = 404;
        $template = '404';
    } else {
        $template = 'cupons/form';
    }
}
// ROTAS DE PEDIDOS (GET)
elseif ($requestPath === '/pedidos' && $requestMethod === 'GET') {
    $data['pedidos'] = $pedidoController->index();
    $template = 'pedidos/index';
}
elseif ($requestPath === '/carrinho' && $requestMethod === 'GET') {
    $carrinhoData = $pedidoController->verCarrinho();
    $template = 'pedidos/carrinho';
    $data = array_merge($data, $carrinhoData);
} elseif ($requestPath === '/checkout' && $requestMethod === 'GET') {
    $checkoutData = $pedidoController->checkout();
    $template = 'pedidos/checkout';
    $data = array_merge($data, $checkoutData);
} elseif (preg_match('/^\/pedido\/confirmacao\/(\d+)$/', $requestPath, $matches) && $requestMethod === 'GET') {
    $idPedido = (int) $matches[1];
    $confirmacaoData = $pedidoController->confirmacao($idPedido);
    if ($confirmacaoData === null) {
        $httpStatusCode = 404;
        $template = '404';
    } else {
        $template = 'pedidos/confirmacao';
        $data = array_merge($data, $confirmacaoData);
    }
}
// Rota não encontrada (404)
else {
    $httpStatusCode = 404;
    $template = '404';
}

// 9. AJUSTA STATUS HTTP
http_response_code($httpStatusCode);

// 10. EXTRAI MENSAGENS DA SESSÃO PARA O ESCOPO DAS VIEWS
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
$warning_message = $_SESSION['warning_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message'], $_SESSION['warning_message']);

// 11. EXTRAI DADOS DO ARRAY $data PARA VARIÁVEIS LOCAIS (para fácil acesso na View)
extract($data);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Mini ERP Guinho</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($requestPath === '/' ? 'active' : ''); ?>" aria-current="page" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (str_starts_with($requestPath, '/produtos') ? 'active' : ''); ?>" href="/produtos">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (str_starts_with($requestPath, '/pedidos') || str_starts_with($requestPath, '/carrinho') || str_starts_with($requestPath, '/checkout') ? 'active' : ''); ?>" href="/pedidos">Pedidos</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo (str_starts_with($requestPath, '/estoque') || str_starts_with($requestPath, '/estoque/localizacoes') ? 'active' : ''); ?>" href="#" id="navbarDropdownEstoque" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Estoque
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownEstoque">
                        <li><a class="dropdown-item <?php echo ($requestPath === '/estoque' ? 'active' : ''); ?>" href="/estoque">Visão Geral</a></li>
                        <li><a class="dropdown-item <?php echo ($requestPath === '/estoque/localizacoes' ? 'active' : ''); ?>" href="/estoque/localizacoes">Localizações</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (str_starts_with($requestPath, '/cupons') ? 'active' : ''); ?>" href="/cupons">Cupons</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($requestPath === '/carrinho' ? 'active' : ''); ?>" href="/carrinho">Carrinho</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php
    // Exibir mensagens de feedback
    if ($success_message) {
        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($success_message) . '</div>';
    }
    if ($error_message) {
        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error_message) . '</div>';
    }
    if ($warning_message) {
        echo '<div class="alert alert-warning" role="alert">' . htmlspecialchars($warning_message) . '</div>';
    }

    // 12. INCLUSÃO DO CONTEÚDO ESPECÍFICO DA PÁGINA
    if ($template) {
        require __DIR__ . '/../src/View/templates/' . $template . '.php';
    } else {
        echo '<div class="alert alert-danger" role="alert">Erro interno: Template não definido para esta rota.</div>';
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>