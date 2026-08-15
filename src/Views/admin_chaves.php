<?php
// View para admin_chaves.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php
// View para admin_chaves.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Chaves - PegaChave</title>
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
              primary: '<?php echo $cor_primaria; ?>',
              secondary: '#0f172a'
            }
          }
        }
      }
    </script>
                    <button type="submit" class="btn-modal save">Salvar Chave</button>
                </div>
            </form>
        </div>
    </div>

    <form id="delete-form" method="POST" action="<?= BASE_URL ?>/admin/chaves" style="display: none;">
        <?php renderizar_csrf_input(); ?>
        <input type="hidden" name="action" value="delete_chave">
        <input type="hidden" name="id" id="delete-id">
    </form>

    <script>
        function openKeyModal() {
            document.getElementById('key-modal-title').textContent = "Cadastrar Nova Chave";
            document.getElementById('key-action').value = "add_chave";
            document.getElementById('key-id').value = "";
            document.getElementById('nome_sala').value = "";
            document.getElementById('bloco').value = "";
            document.getElementById('andar').value = "";
            document.getElementById('matriculas_permitidas').value = "";
            document.getElementById('codigo_sala').value = "";
            document.getElementById('key_qr_code_hash').value = "";
            document.getElementById('descricao').value = "";
            document.getElementById('key-modal').classList.add('active');
        }

        function openEditKeyModal(chave) {
            document.getElementById('key-modal-title').textContent = "Editar Chave";
            document.getElementById('key-action').value = "edit_chave";
            document.getElementById('key-id').value = chave.id;
            document.getElementById('nome_sala').value = chave.nome_sala;
            document.getElementById('bloco').value = chave.bloco || "";
            document.getElementById('andar').value = chave.andar || "";
            document.getElementById('matriculas_permitidas').value = chave.matriculas_permitidas || "";
            document.getElementById('codigo_sala').value = chave.codigo_sala;
            document.getElementById('key_qr_code_hash').value = chave.qr_code_hash;
            document.getElementById('descricao').value = chave.descricao;
            document.getElementById('key-modal').classList.add('active');
        }

        function closeKeyModal() {
            document.getElementById('key-modal').classList.remove('active');
        }

        function confirmDeleteChave(id) {
            if (confirm("Deseja realmente deletar esta chave? Todas as movimentações dela serão deletadas.")) {
                document.getElementById('delete-id').value = id;
                document.getElementById('delete-form').submit();
            }
        }

        // Preenchimento de Hash Automático
        document.getElementById('codigo_sala').addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-Z0-9_-]/g, '');
            document.getElementById('key_qr_code_hash').value = this.value ? 'chaves_' + this.value : '';
        });

        // Ordenação Interativa de Tabela
        let sortDirections = {};
        function sortTable(colIndex) {
            const table = document.querySelector("table");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));
            
            if (rows.length === 1 && rows[0].cells.length === 1) return;

            const currentDir = sortDirections[colIndex] || 'desc';
            const nextDir = currentDir === 'desc' ? 'asc' : 'desc';
            sortDirections[colIndex] = nextDir;

            // Ordenar linhas
            rows.sort((a, b) => {
                const cellA = a.cells[colIndex].textContent.trim();
                const cellB = b.cells[colIndex].textContent.trim();

                const numA = parseFloat(cellA);
                const numB = parseFloat(cellB);
                if (!isNaN(numA) && !isNaN(numB)) {
                    return nextDir === 'asc' ? numA - numB : numB - numA;
                }

                return nextDir === 'asc' 
                    ? cellA.localeCompare(cellB, 'pt-BR', { numeric: true, sensitivity: 'base' })
                    : cellB.localeCompare(cellA, 'pt-BR', { numeric: true, sensitivity: 'base' });
            });

            // Re-inserir ordenadas
            rows.forEach(row => tbody.appendChild(row));
        }

        // Filtro/Pesquisa Rápida na Tabela
        function filterTable() {
            const query = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll("table tbody tr");
            
            if (rows.length === 1 && rows[0].cells.length === 1) return;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
