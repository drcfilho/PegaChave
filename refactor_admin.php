<?php
$files = [
    'admin_chaves.php', 'admin_usuarios.php', 'admin_usuarios_arquivados.php',
    'admin_reservas.php', 'admin_restricoes.php', 'admin_gerar_qr.php',
    'admin_consulta.php', 'admin_relatorio.php', 'admin_logs.php', 'admin_config.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    if (strpos($content, 'use App\\Controllers') !== false) {
        continue;
    }

    $pos = strpos($content, '?>');
    if ($pos === false) continue;

    $phpBlock = substr($content, 0, $pos);
    $htmlBlock = substr($content, $pos + 2);

    $nameParts = explode('_', str_replace('.php', '', $file));
    $className = '';
    foreach ($nameParts as $part) {
        $className .= ucfirst($part);
    }
    $className .= 'Controller';

    $phpBlock = str_replace("session_start();", "", $phpBlock);
    $phpBlock = preg_replace('/if \(\!isset\(\$_SESSION.*?\}/s', '', $phpBlock);
    $phpBlock = str_replace("require_once __DIR__ . '/api/db.php';", "", $phpBlock);
    $phpBlock = str_replace("<?php", "", $phpBlock);
    
    $controllerContent = "<?php\nnamespace App\\Controllers;\n\nclass " . $className . " extends AdminBaseController {\n    public function index() {\n        global \$pdo, \$nome_escola, \$cor_primaria, \$cor_secundaria;\n        " . $phpBlock . "\n        require_once __DIR__ . '/../Views/" . $file . "';\n    }\n}\n";

    $viewContent = "<?php\n// View para " . $file . "\n?>\n" . ltrim($htmlBlock);

    $rootContent = "<?php\n// " . $file . " (Ponte para " . $className . ")\nrequire_once __DIR__ . '/vendor/autoload.php';\n\nuse App\\Controllers\\" . $className . ";\n\n\$controller = new " . $className . "();\n\$controller->index();\n";

    file_put_contents("src/Controllers/" . $className . ".php", $controllerContent);
    file_put_contents("src/Views/" . $file, $viewContent);
    file_put_contents($file, $rootContent);

    echo "Refatorado: " . $file . " -> " . $className . "\n";
}
