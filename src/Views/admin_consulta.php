<?php
// View para admin_consulta.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php
// View para admin_consulta.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Chaves - Administrador</title>
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
                    <div>
                        <div class="card-header-flex">
                            <div class="card-title-text">
                                <h3><?php echo htmlspecialchars($c['nome_sala']); ?></h3>
                                <span>Código: <?php echo htmlspecialchars($c['codigo_sala']); ?></span>
                            </div>
                            <span class="status-badge <?php echo $c['status_disponivel'] ? 'disponivel' : 'ocupada'; ?>">
                                <?php echo $c['status_disponivel'] ? 'Disponível' : 'Em Uso'; ?>
                            </span>
                        </div>

                        <?php if ($c['descricao']): ?>
                            <p style="font-size: 12px; color: #64748b; margin-top: -5px; margin-bottom: 10px; font-style: italic;">
                                <?php echo htmlspecialchars($c['descricao']); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <?php if (!$c['status_disponivel']): ?>
                        <div class="card-details">
                            <div class="detail-row">
                                <span>👤</span> <strong>Com:</strong> <?php echo htmlspecialchars($c['reservado_por_nome']); ?> (<?php echo htmlspecialchars($c['reservado_por_perfil']); ?>)
                            </div>
                            <div class="detail-row">
                                <span>⏰</span> <strong>Retirada:</strong> <?php echo htmlspecialchars($c['data_retirada_formatada']); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function filterCards() {
            const query = document.getElementById('search').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.card-chave');
            
            cards.forEach(card => {
                const searchData = card.getAttribute('data-name');
                if (searchData.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
