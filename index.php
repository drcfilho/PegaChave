<?php
// index.php (Front Controller e Roteador)

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/api/db.php';

use App\Core\Router;
use eftec\bladeone\BladeOne;

$views = __DIR__ . '/src/Views';
$cache = __DIR__ . '/cache';
if (!is_dir($cache)) {
    mkdir($cache, 0777, true);
}

$blade = new BladeOne($views, $cache, BladeOne::MODE_AUTO);
// Adicionar diretiva personalizada de CSRF
$blade->directive('csrf', function () {
    return '<?php renderizar_csrf_input(); ?>';
});

$router = new Router();
$router->setDependencies([
    'pdo' => $pdo,
    'blade' => $blade,
    'config' => [
        'nome_escola' => $nome_escola,
        'cor_primaria' => $cor_primaria,
        'cor_secundaria' => $cor_secundaria,
        'limite_chaves' => $limite_chaves
    ]
]);

// === Rotas Públicas ===
$router->get('/', 'QuiosqueController', 'index');
$router->get('/consulta', 'ConsultaController', 'index');
$router->get('/regras', 'RegrasController', 'index');
$router->post('/api/processar_scan', 'ScanController', 'processar');

// === Rota de Instalação (Somente acessível se o banco estiver vazio) ===
$router->get('/install', 'InstallController', 'index');
$router->post('/install/run', 'InstallController', 'runInstall');

// === Rotas de Cron Jobs (Servidor Web) ===
$router->get('/api/cron/alertas', 'CronController', 'processarAlertas');

// === Middlewares ===
$auth = ['AuthMiddleware'];
$authCsrf = ['AuthMiddleware', 'CsrfMiddleware'];

// === Rotas Administrativas ===
$router->get('/admin', 'AdminDashboardController', 'index', $auth);
$router->get('/admin/chaves', 'AdminChavesController', 'index', $auth);
$router->post('/admin/chaves/create', 'AdminChavesController', 'store', $authCsrf);
$router->post('/admin/chaves/update', 'AdminChavesController', 'update', $authCsrf);
$router->post('/admin/chaves/delete', 'AdminChavesController', 'destroy', $authCsrf);
$router->get('/admin/chaves_arquivadas', 'AdminChavesArquivadasController', 'index', $auth);
$router->post('/admin/chaves_arquivadas', 'AdminChavesArquivadasController', 'index', $authCsrf);
$router->get('/admin/usuarios', 'AdminUsuariosController', 'index', $auth);
$router->post('/admin/usuarios/create', 'AdminUsuariosController', 'store', $authCsrf);
$router->post('/admin/usuarios/update', 'AdminUsuariosController', 'update', $authCsrf);
$router->post('/admin/usuarios/delete', 'AdminUsuariosController', 'destroy', $authCsrf);
$router->get('/admin/usuarios_arquivados', 'AdminUsuariosArquivadosController', 'index', $auth);
$router->post('/admin/usuarios_arquivados', 'AdminUsuariosArquivadosController', 'index', $authCsrf);
$router->get('/admin/reservas', 'AdminReservasController', 'index', $auth);
$router->post('/admin/reservas', 'AdminReservasController', 'index', $authCsrf);
$router->get('/admin/restricoes', 'AdminRestricoesController', 'index', $auth);
$router->post('/admin/restricoes', 'AdminRestricoesController', 'index', $authCsrf);
$router->get('/admin/relatorio', 'AdminRelatorioController', 'index', $auth);
$router->get('/admin/config', 'AdminConfigController', 'index', $auth);
$router->post('/admin/config', 'AdminConfigController', 'index', $authCsrf);
$router->get('/admin/logs', 'AdminLogsController', 'index', $auth);
$router->get('/admin/gerar_qr', 'AdminGerarQrController', 'index', $auth);
$router->get('/admin/consulta', 'AdminConsultaController', 'index', $auth);
$authOperadores = ['AuthMiddleware:gerenciar_operadores'];
$authCsrfOperadores = ['AuthMiddleware:gerenciar_operadores', 'CsrfMiddleware'];

$router->get('/admin/operadores', 'AdminOperadoresController', 'index', $authOperadores);
$router->post('/admin/operadores/create', 'AdminOperadoresController', 'create', $authCsrfOperadores);
$router->post('/admin/operadores/update', 'AdminOperadoresController', 'update', $authCsrfOperadores);
$router->post('/admin/operadores/delete', 'AdminOperadoresController', 'delete', $authCsrfOperadores);

// Despacha a requisição baseada na URL
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
