<?php
$dir = __DIR__ . '/src/Controllers';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    $basename = basename($file);
    
    // Replace require_once __DIR__ . '/../../api/db.php';
    $content = str_replace("require_once __DIR__ . '/../../api/db.php';", "", $content);
    
    if ($basename === 'AdminBaseController.php') {
        $content = str_replace(
            "public function __construct() {",
            "protected \$pdo;\n    protected \$config;\n\n    public function __construct(\$pdo, \$config) {\n        \$this->pdo = \$pdo;\n        \$this->config = \$config;\n",
            $content
        );
    } else {
        // If it extends AdminBaseController, it shouldn't define __construct if it doesn't have one, 
        // wait, if we don't define __construct, it inherits from AdminBaseController, which is fine!
        // But what about controllers that don't extend AdminBaseController?
        if (strpos($content, 'extends AdminBaseController') === false && $basename !== 'AdminBaseController.php') {
            // Need to add constructor
            $classDec = "class " . str_replace('.php', '', $basename) . " {";
            $constructor = "\n    protected \$pdo;\n    protected \$config;\n\n    public function __construct(\$pdo, \$config) {\n        \$this->pdo = \$pdo;\n        \$this->config = \$config;\n    }\n";
            $content = str_replace($classDec, $classDec . $constructor, $content);
        }
        
        // Remove global declarations
        $content = preg_replace('/global \$pdo.*?;\s*/s', '', $content);
        
        // Inside index() or other methods, they might use $pdo or $nome_escola.
        // To keep views working, we can extract config at the start of methods.
        // We'll replace `public function index() {` with `public function index() { $pdo = $this->pdo; extract($this->config);`
        $content = preg_replace('/public function ([a-zA-Z0-9_]+)\((.*?)\)\s*\{/', "public function $1($2) {\n        \$pdo = \$this->pdo;\n        extract(\$this->config);", $content);
    }
    
    file_put_contents($file, $content);
}
echo "Controllers refatorados.";
