<?php
// index.php (Front Controller e Roteador)

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Router;

$router = new Router();

// === Rotas Públicas ===
$router->get('/', 'QuiosqueController', 'index');
$router->get('/consulta', 'ConsultaController', 'index');
$router->get('/regras', 'RegrasController', 'index');
$router->post('/api/processar_scan', 'ScanController', 'processar');

// === Rotas Administrativas ===
$router->get('/admin', 'AdminDashboardController', 'index');
$router->get('/admin/chaves', 'AdminChavesController', 'index');
$router->post('/admin/chaves', 'AdminChavesController', 'index'); // Para formulários
$router->get('/admin/usuarios', 'AdminUsuariosController', 'index');
$router->post('/admin/usuarios', 'AdminUsuariosController', 'index');
$router->get('/admin/usuarios_arquivados', 'AdminUsuariosArquivadosController', 'index');
$router->post('/admin/usuarios_arquivados', 'AdminUsuariosArquivadosController', 'index');
$router->get('/admin/reservas', 'AdminReservasController', 'index');
$router->post('/admin/reservas', 'AdminReservasController', 'index');
$router->get('/admin/restricoes', 'AdminRestricoesController', 'index');
$router->post('/admin/restricoes', 'AdminRestricoesController', 'index');
$router->get('/admin/relatorio', 'AdminRelatorioController', 'index');
$router->get('/admin/config', 'AdminConfigController', 'index');
$router->post('/admin/config', 'AdminConfigController', 'index');
$router->get('/admin/logs', 'AdminLogsController', 'index');
$router->get('/admin/gerar_qr', 'AdminGerarQrController', 'index');
$router->get('/admin/consulta', 'AdminConsultaController', 'index');

// Despacha a requisição baseada na URL
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
