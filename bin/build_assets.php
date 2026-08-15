<?php
// bin/build_assets.php
$binDir = __DIR__;
$rootDir = dirname(__DIR__);

// Arquivo executável standalone do Tailwind
$tailwindExe = $binDir . DIRECTORY_SEPARATOR . 'tailwindcss.exe';

if (!file_exists($tailwindExe)) {
    die("Erro: O compilador '$tailwindExe' não foi encontrado.\n");
}

$inputFile = $rootDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'input.css';
$outputFile = $rootDir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'app.min.css';

// Garantir que a pasta de destino exista
if (!is_dir(dirname($outputFile))) {
    mkdir(dirname($outputFile), 0777, true);
}

// Verifica se quer rodar em modo watch (desenvolvimento) ou minify (produção)
$isWatch = in_array('--watch', $argv);

$command = escapeshellarg($tailwindExe) . " -i " . escapeshellarg($inputFile) . " -o " . escapeshellarg($outputFile);

if ($isWatch) {
    $command .= " --watch";
    echo "Iniciando Tailwind em modo Watch...\n";
} else {
    $command .= " --minify";
    echo "Compilando e minificando CSS para produção...\n";
}

// Executar o comando no terminal em tempo real
passthru($command, $return_var);

if ($return_var !== 0) {
    echo "\nErro ao compilar o CSS.\n";
} else {
    echo "\nCSS compilado com sucesso em: assets/dist/app.min.css\n";
}
