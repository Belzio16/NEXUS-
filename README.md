NEXUS — Sistema de Gestão de Produtos
Desenvolvido por Jaraujo | Laravel 11 · Dark Mode · Glassmorphism · Neon UI · REST API © 2024

📁 Estrutura do Projecto
nexus/
│
├── app/
│   ├── Exceptions/
│   │   └── Handler.php                  ← Tratamento global de erros
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php  ← Analytics + gráficos
│   │   │   ├── ProductController.php    ← CRUD web + AJAX search
│   │   │   └── Api/
│   │   │       └── ProductApiController.php ← REST API v1
│   │   └── Requests/
│   │       └── ProductRequest.php       ← Validação robusta (Form Request)
│   │
│   ├── Models/
│   │   ├── Product.php                  ← Scopes, Accessors, SoftDeletes
│   │   ├── Category.php
│   │   └── ProductActivity.php          ← Feed de actividade
│   │
│   └── Services/
│       └── ProductService.php           ← Lógica de negócio isolada
│
├── database/
│   ├── migrations/
│   │   └── ..._create_products_table.php ← categories + products + activities
│   └── seeders/
│       └── DatabaseSeeder.php           ← 12 produtos + 5 categorias reais
│
├── public/
│   ├── css/nexus.css                    ← ~800 linhas: Dark/Glass/Neon
│   └── js/nexus.js                      ← Sidebar, AJAX Search, Toasts, Notifs
│
├── resources/views/
│   ├── layouts/app.blade.php            ← Layout master (sidebar + topbar)
│   ├── errors/404.blade.php
│   └── pages/
│       ├── dashboard/index.blade.php    ← Charts + stats + activity
│       └── products/
│           ├── index.blade.php          ← Grid com filtros
│           ├── create.blade.php         ← Formulário criação
│           ├── edit.blade.php           ← Formulário edição
│           └── show.blade.php           ← Detalhe do produto
│
└── routes/
    ├── web.php                          ← Rotas web + AJAX endpoints
    └── api.php                          ← REST API /api/v1/products


🚀 Instalação Rápida
# 1. Clonar / extrair o projecto
cd nexus

# 2. Instalar dependências PHP
composer install

# 3. Configurar ambiente
cp .env.example .env
php artisan key:generate

# 4. Base de dados (SQLite — zero config)
touch database/database.sqlite
php artisan migrate --seed

# 5. Storage público
php artisan storage:link

# 6. Iniciar servidor
php artisan serve

Abrir → http://localhost:8000

🔌 API REST
Base URL: http://localhost:8000/api/v1

Método	Endpoint	Descrição
GET	/products	Listar produtos (paginado)
POST	/products	Criar produto
GET	/products/{id}	Detalhe de um produto
PUT	/products/{id}	Actualizar produto
DELETE	/products/{id}	Remover produto (soft delete)
GET	/products-stats	Estatísticas do dashboard

Exemplo de request:
curl http://localhost:8000/api/v1/products \
  -H "Accept: application/json"

curl http://localhost:8000/api/v1/products-stats \
  -H "Accept: application/json"

Filtros disponíveis (GET /products):
?search=iphone
?category=3
?status=active
?per_page=10


✨ Funcionalidades
🎨 UI/UX
·	Dark Mode por padrão com variáveis CSS
·	Glassmorphism — backdrop-filter em toda a UI
·	Neon accents — gradientes e glows em cyan/purple
·	Animações — fade-up staggered no carregamento
·	Sidebar responsiva — colapsa em mobile com overlay
·	Toggle personalizado para produto em destaque
⚡ Interactividade AJAX
·	Live Search — pesquisa em tempo real com debounce 300ms
·	Highlight de termos — destaca o texto pesquisado
·	Atalho CMD+K / CTRL+K — foca a pesquisa
·	Toggle Featured — sem reload de página
·	Toasts animados — feedback visual contextual
·	Painel de notificações — deslizante com polling automático (60s)
🏗 Arquitectura
·	Service Layer — ProductService separa lógica do controller
·	Form Requests — validação e autorização encapsuladas
·	Eloquent Scopes — active(), lowStock(), search(), filter()
·	Soft Deletes — produtos recuperáveis após eliminação
·	Activity Log — registo automático de acções no produto
·	API REST paralela — mesmos dados via JSON
📊 Dashboard
·	Gráfico de barras + linha (Chart.js) — vendas/receita 7 dias
·	Doughnut por categoria
·	Cards de métricas com trends
·	Tabela de produtos recentes
·	Alertas de stock crítico com barra de progresso
·	Feed de actividade em tempo real

🛡 Segurança
·	CSRF — protecção em todos os formulários (@csrf)
·	Validação — Form Requests com regras robustas
·	SoftDeletes — sem perda permanente de dados
·	Sanitização — esc() e Blade auto-escaping

🔧 Tecnologias
Camada	Tecnologia
Framework	Laravel 11
Linguagem	PHP 8.2+
ORM	Eloquent
Frontend	Bootstrap 5 + CSS Custom
Charts	Chart.js 4
Icons	Bootstrap Icons
Fonts	Syne (display) + DM Sans
DB padrão	SQLite (suporta MySQL/Postgres)


📐 Padrões Aplicados
·	MVC — separação clara de responsabilidades
·	Service Layer — ProductService concentra regras de negócio
·	Repository Pattern — via Eloquent Scopes
·	SOLID — Single Responsibility no Service e Controller
·	DRY — layout Blade reutilizável, componentes partilhados
·	Clean Code — métodos pequenos, nomes descritivos

Desenvolvido por Jaraujo com ❤ · NEXUS Product Management System © 2024
