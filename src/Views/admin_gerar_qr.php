<?php
// View para admin_gerar_qr.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php
// View para admin_gerar_qr.php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de QR Codes - <?php echo htmlspecialchars($nome_escola); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        </div>
    </div>

    <script>
        let currentTab = 'chaves';

        function switchTab(tab) {
            currentTab = tab;
            document.getElementById('tab-chaves').classList.toggle('active', tab === 'chaves');
            document.getElementById('tab-usuarios').classList.toggle('active', tab === 'usuarios');
            
            document.getElementById('table-chaves').style.display = tab === 'chaves' ? 'table' : 'none';
            document.getElementById('table-usuarios').style.display = tab === 'usuarios' ? 'table' : 'none';

            // Resetar busca
            document.getElementById('search-box').value = '';
            filterItems();
        }

        function filterItems() {
            const query = document.getElementById('search-box').value.toLowerCase().trim();
            const targetTbody = currentTab === 'chaves' ? 'tbody-chaves' : 'tbody-usuarios';
            const rows = document.querySelectorAll(`#${targetTbody} .item-row`);
            
            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                if (searchData.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function toggleSelectAll(tab) {
            const selectAllChk = document.getElementById(tab === 'chaves' ? 'select-all-chaves' : 'select-all-usuarios');
            const chkItems = document.querySelectorAll(tab === 'chaves' ? '.chk-chave' : '.chk-usuario');
            
            chkItems.forEach(chk => {
                // Selecionar apenas as visíveis pelo filtro de busca
                const row = chk.closest('tr');
                if (row.style.display !== 'none') {
                    chk.checked = selectAllChk.checked;
                }
            });
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const count = document.querySelectorAll('.chk-item:checked').length;
            document.getElementById('selected-count').textContent = count;
        }

        function generateSelectedPreview() {
            const checkedBoxes = document.querySelectorAll('.chk-item:checked');
            
            if (checkedBoxes.length === 0) {
                alert("Por favor, selecione pelo menos um item para gerar as etiquetas.");
                return;
            }

            const container = document.getElementById('preview-grid-container');
            container.innerHTML = ''; // Limpar anterior

            checkedBoxes.forEach(chk => {
                const name = chk.getAttribute('data-name');
                const bloco = chk.getAttribute('data-bloco') || '';
                const andar = chk.getAttribute('data-andar') || '';
                const id = chk.getAttribute('data-id');
                const hash = chk.getAttribute('data-hash');
                const cat = chk.getAttribute('data-cat');

                const card = document.createElement('div');
                card.className = 'label-card';
                card.innerHTML = `
                    <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 8px;">
                        ${cat === 'Chave' ? '🔑 CHAVE' : '👤 CRACHÁ'}
                    </div>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=${encodeURIComponent(hash)}" alt="QR Code">
                    <div class="no-print" style="margin-top: 6px; margin-bottom: 4px;">
                        <button onclick="downloadQRCode('${hash}', '${name.replace(/'/g, "\\'")}', '${id.replace(/'/g, "\\'")}', '${cat}', '${bloco.replace(/'/g, "\\'")}', '${andar.replace(/'/g, "\\'")}', '${cat}_${name.replace(/[^a-zA-Z0-9]/g, '_')}')" style="background: var(--primary); color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">⬇️ Baixar</button>
                    </div>
                    <div class="item-name">${name}</div>
                    ${bloco || andar ? `<div style="font-size: 11px; color: #475569; font-weight: 600; margin-bottom: 4px;">${bloco ? 'Bloco: ' + bloco : ''} ${bloco && andar ? '|' : ''} ${andar ? 'Andar: ' + andar : ''}</div>` : ''}
                    <div class="item-id">${id}</div>
                    <div class="item-hash">${hash}</div>
                `;
                container.appendChild(card);
            });

            document.getElementById('print-preview-section').style.display = 'block';
            window.scrollTo(0, 0);
        }

        async function downloadQRCode(hash, name, id, cat, bloco, andar, filename) {
            try {
                const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(hash)}`;
                
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.src = qrUrl;
                
                await new Promise((resolve, reject) => {
                    img.onload = resolve;
                    img.onerror = reject;
                });

                const canvas = document.createElement('canvas');
                canvas.width = 300;
                canvas.height = 410;
                const ctx = canvas.getContext('2d');

                // Fundo branco
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Desenhar QR Code (300x300)
                ctx.drawImage(img, 0, 0, 300, 300);

                // Textos
                ctx.fillStyle = '#1e293b';
                ctx.textAlign = 'center';

                // Categoria (🔑 CHAVE ou 👤 CRACHÁ)
                ctx.font = 'bold 11px sans-serif';
                ctx.fillText(cat === 'Chave' ? '🔑 CHAVE' : '👤 CRACHÁ', 150, 320);

                // Nome do item completo
                ctx.font = 'bold 14px sans-serif';
                let displayName = name;
                if (displayName.length > 28) {
                    displayName = displayName.substring(0, 26) + '...';
                }
                ctx.fillText(displayName, 150, 340);

                // Bloco e Andar (se existirem)
                ctx.font = 'bold 11px sans-serif';
                ctx.fillStyle = '#475569';
                if (bloco || andar) {
                    const locText = (bloco ? 'Bloco: ' + bloco : '') + (bloco && andar ? ' | ' : '') + (andar ? 'Andar: ' + andar : '');
                    ctx.fillText(locText, 150, 356);
                }

                // Identificador / Código
                ctx.font = '12px sans-serif';
                ctx.fillStyle = '#64748b';
                ctx.fillText(id, 150, 375);

                // Hash do QR
                ctx.font = '9px monospace';
                ctx.fillStyle = '#94a3b8';
                ctx.fillText(hash, 150, 395);

                canvas.toBlob(blob => {
                    const blobUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = `${filename}.png`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(blobUrl);
                }, 'image/png');

            } catch (err) {
                console.error("Erro ao gerar imagem composta do QR Code:", err);
                window.open(`https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(hash)}`, '_blank');
            }
        }

        function closePreview() {
            document.getElementById('print-preview-section').style.display = 'none';
        }

        function exportarPDF() {
            const checkedBoxes = document.querySelectorAll('.chk-item:checked');
            if (checkedBoxes.length === 0) return;

            const items = [];
            checkedBoxes.forEach(chk => {
                items.push({
                    name: chk.getAttribute('data-name'),
                    bloco: chk.getAttribute('data-bloco') || '',
                    andar: chk.getAttribute('data-andar') || '',
                    id: chk.getAttribute('data-id'),
                    hash: chk.getAttribute('data-hash'),
                    cat: chk.getAttribute('data-cat')
                });
            });

            // Criar um form oculto para enviar via POST e gerar o download
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href; // Mesma página

            const inputAction = document.createElement('input');
            inputAction.type = 'hidden';
            inputAction.name = 'action';
            inputAction.value = 'export_pdf';

            const inputItems = document.createElement('input');
            inputItems.type = 'hidden';
            inputItems.name = 'items';
            inputItems.value = JSON.stringify(items);

            form.appendChild(inputAction);
            form.appendChild(inputItems);
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }
    </script>
    <script src="<?= BASE_URL ?>/api/admin_responsive.js"></script>
</body>
</html>
