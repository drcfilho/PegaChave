<?php
// View para admin_config.php
?>
<!DOCTYPE html>
<?php
// View para admin_config.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Sistema - PegaChave</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/api/admin_responsive.css?v=<?= time() ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        corePlugins: {
          preflight: true,
        },
        theme: {
          extend: {
            colors: {
              primary: 'var(--primary)',
              secondary: 'var(--sidebar-bg)'
            }
          }
        }
      }
    </script>
        document.getElementById('cor_primaria').addEventListener('input', function(e) {
            e.target.nextElementSibling.textContent = e.target.value.toUpperCase();
        });
        document.getElementById('cor_secundaria').addEventListener('input', function(e) {
            e.target.nextElementSibling.textContent = e.target.value.toUpperCase();
        });
    </script>
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
