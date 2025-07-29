# Mini ERP - Sistema de Gerenciamento de Pedidos e Estoque

Este é um projeto de um sistema **Mini ERP (Enterprise Resource Planning)** simples, desenvolvido em PHP puro com uma arquitetura MVC (Model-View-Controller). Ele foca no gerenciamento básico de produtos, estoque, cupons e o fluxo de pedidos, incluindo um carrinho de compras em sessão, cálculo de frete e verificação de CEP.

---

## 🚀 Funcionalidades

* **Gerenciamento de Produtos:**
    * Cadastro, edição e listagem de produtos com Nome, Preço, SKU e Descrição.
    * **Soft Delete:** Produtos "deletados" são marcados como inativos, preservando o histórico em pedidos.
    * Associação automática de novos produtos a um estoque padrão com quantidade inicial zero.
* **Controle de Estoque:**
    * Visão geral do estoque por produto e por localização.
    * Movimentação de estoque (adição e remoção de quantidade) para produtos em locais específicos.
    * **Gerenciamento de Localizações de Estoque (CRUD):** Cadastro, edição, listagem e remoção de armazéns/lojas.
* **Gerenciamento de Cupons:**
    * Cadastro, edição e listagem de cupons de desconto (percentual ou fixo).
    * Validação de cupons (ativo e data de validade).
* **Módulo de Pedidos (Carrinho & Checkout):**
    * Adicionar produtos ao carrinho de compras (gerenciado em sessão).
    * Atualizar quantidades e remover itens do carrinho.
    * Página de Checkout com resumo do pedido.
    * **Cálculo de Frete:** Implementação de regras de frete baseadas no subtotal do pedido:
        * Entre R$52,00 e R$166,59: Frete R$15,00.
        * Maior que R$200,00: Frete Grátis.
        * Outros valores: Frete R$20,00.
    * **Verificação de CEP:** Integração com a API [ViaCEP](https://viacep.com.br/) para buscar endereço automaticamente.
    * Finalização de Pedidos com persistência no banco de dados.
    * Baixa automática de estoque após a finalização do pedido.
    * Página de Confirmação de Pedido com detalhes.
    * Listagem de todos os pedidos finalizados.


## 💻 Tecnologias Utilizadas

* **Backend:** PHP 8.3 (PHP Puro, Orientado a Objetos - POO)
* **Banco de Dados:** MySQL 8.0
* **Servidor Web:** Nginx
* **Orquestração de Ambiente:** Docker e Docker Compose
* **Gerenciamento de Dependências PHP:** Composer
* **Front-end:** HTML, CSS, JavaScript (puro), Bootstrap 5 (via CDN)
* **Variáveis de Ambiente:** `vlucas/phpdotenv`


## ⚙️ Como Começar

Siga os passos abaixo para configurar e rodar o projeto em seu ambiente local.

### Pré-requisitos

Certifique-se de ter os seguintes softwares instalados em sua máquina:

* **Git:** Para clonar o repositório.
* **Docker Desktop:** (Inclui Docker Engine e Docker Compose) Para criar e gerenciar os contêineres.

### Instalação

1.  **Clone o Repositório:**
    
    git clone https://github.com/guinhorl/Mini-ERP.git
    cd NomeDoSeuRepo
    

2.  **Configurar Variáveis de Ambiente:**
    Crie um arquivo `.env` na raiz do projeto (na mesma pasta do `docker-compose.yml`) e adicione as seguintes variáveis:

    # .env
    DB_HOST=db
    DB_NAME=mini_erp_db
    DB_USER=user_erp
    DB_PASSWORD=password_erp
    MYSQL_ROOT_PASSWORD=root_password
    APP_ENV=development
    APP_DEBUG=true


3.  **Subir os Contêineres Docker:**
    Na raiz do projeto, execute o Docker Compose para construir as imagens e iniciar os serviços:

    docker-compose up --build -d
    
    # Este comando pode levar alguns minutos na primeira execução, pois baixará as imagens e construirá os contêineres.

4.  **Instalar Dependências PHP (via Composer):**
    Com o contêiner PHP em execução, instale as dependências do Composer dentro dele:

    docker-compose exec php composer install

5.  **Executar Migrações do Banco de Dados:**
    Crie as tabelas do banco de dados executando os scripts de migração:

    docker-compose exec php php database/migrate.php

6.  **Popular o Banco de Dados com Dados Iniciais (Opcional):**
    Adicione alguns dados de exemplo para teste:

    docker-compose exec php php database/seed.php

## 🚀 Utilização

Após a instalação, seu Mini ERP estará acessível em:

* **`http://localhost`**

### Rotas Principais:

* **`/`**: Página inicial
* **`/produtos`**: Listar, cadastrar, editar e "deletar" (soft delete) produtos.
* **`/estoque`**: Visão geral do estoque por produto.
* **`/estoque/movimentar/{id_produto}`**: Formulário para movimentar estoque de um produto.
* **`/estoques/localizacoes`**: Gerenciar localizações de estoque (CRUD).
* **`/cupons`**: Listar, cadastrar, editar e deletar cupons.
* **`/carrinho`**: Ver e gerenciar o carrinho de compras.
* **`/checkout`**: Finalizar o pedido.
* **`/pedido/confirmacao/{id_pedido}`**: Página de confirmação de um pedido específico.
* **`/pedidos`**: Listar todos os pedidos finalizados.

---

## 📂 Estrutura do Projeto


mini_erp_php/
├── public/                 # Document root do servidor web
│   └── index.php           # Front controller
├── src/                    # Código fonte da aplicação
│   ├── Config/             # Configurações de conexão (Database.php)
│   ├── Controller/         # Camada de Controladores (ProdutoController.php, PedidoController.php, etc.)
│   ├── Model/              # Camada de Modelos (Produto.php, Pedido.php, ItemPedido.php, etc.)
│   └── View/               # Camada de Views (templates HTML/PHP)
│       └── templates/
│           ├── cupons/
│           ├── estoque/
│           ├── pedidos/
│           └── produtos/
├── docker/                 # Arquivos de configuração Docker
├── database/               # Migrações e seeders SQL
├── vendor/                 # Dependências do Composer
├── .env                    # Variáveis de ambiente
├── composer.json           # Configuração do Composer
└── docker-compose.yml      # Definição dos serviços Docker


## 🤝 Contribuindo

Se você quiser contribuir com este projeto, sinta-se à vontade para abrir "issues" ou enviar "pull requests".

## 📄 Licença

Este projeto está licenciado sob a Licença MIT.

## ✉️ Contato

Desenvolvido por: **Wagner Ramos Lima**
wagnerramosl@yahoo.com.br | https://www.linkedin.com/in/wagnerramoslima/