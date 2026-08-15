<?php
$dir = __DIR__ . '/src/Controllers';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Fix __construct
    $content = preg_replace(
        '/public function __construct\(\$pdo, \$config\)\s*\{\s*\$pdo = \$this->pdo;\s*extract\(\$this->config\);\s*\$this->pdo = \$pdo;\s*\$this->config = \$config;\s*\}/',
        "public function __construct(\$pdo, \$config) {\n        \$this->pdo = \$pdo;\n        \$this->config = \$config;\n    }",
        $content
    );
    
    file_put_contents($file, $content);
}
echo "Fix aplicado.";
