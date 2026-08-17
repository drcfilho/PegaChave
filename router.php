<?php
// router.php - Roteador personalizado para o Servidor PHP de Desenvolvimento
// Trata o conflito com a pasta física 'api/' no Windows/macOS/Linux.

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $url;

// Se for um arquivo físico existente, o PHP serve ele diretamente
if (is_file($file)) {
    return false;
}

// Endpoint temporário para depuração de rotas no servidor embutido
if ($url === '/api/debug_routing') {
    header('Content-Type: application/json');
    echo json_encode([
        'REQUEST_URI' => $_SERVER['REQUEST_URI'],
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
        'basePath' => rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'),
        'PHP_SELF' => $_SERVER['PHP_SELF'] ?? null
    ]);
    exit;
}

// Caso contrário, redireciona o fluxo para o Front Controller index.php
require_once __DIR__ . '/index.php';
