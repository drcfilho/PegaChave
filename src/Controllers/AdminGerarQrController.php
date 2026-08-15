<?php
namespace App\Controllers;

use App\Models\ChaveRepository;
use App\Models\UsuarioRepository;

class AdminGerarQrController extends AdminBaseController {
    public function index() {
        $pdo = $this->pdo;
        extract($this->config);
        // admin_gerar_qr.php

try {
    $chaveRepo = new ChaveRepository($pdo);
    $usuarioRepo = new UsuarioRepository($pdo);
    
    // Buscar todas as chaves
    $chaves = $chaveRepo->buscarParaQrCode();
    
    // Buscar todos os usuários
    $usuarios = $usuarioRepo->buscarParaQrCode();
} catch (\PDOException $e) {
    echo "Erro de Banco de Dados: " . $e->getMessage();
    exit;
}

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export_pdf') {
            $items_json = $_POST['items'] ?? '[]';
            $items = json_decode($items_json, true);

            if (ob_get_level()) ob_end_clean();
            require_once __DIR__ . '/../../vendor/autoload.php';

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);

            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
                body { font-family: Helvetica, sans-serif; margin: 0; padding: 0; }
                .grid { width: 100%; text-align: center; }
                .label { 
                    display: inline-block; 
                    width: 30%; /* 3 por linha */
                    margin: 1%;
                    border: 1px solid #000; 
                    padding: 10px; 
                    text-align: center;
                    box-sizing: border-box;
                    page-break-inside: avoid;
                }
                .label img { width: 120px; height: 120px; margin: 5px 0; }
                .label .cat { font-size: 10px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-bottom: 5px; text-transform: uppercase; }
                .label .name { font-size: 13px; font-weight: bold; margin-bottom: 3px; }
                .label .loc { font-size: 11px; color: #444; margin-bottom: 3px; }
                .label .id { font-size: 12px; }
                .label .hash { font-size: 9px; font-family: monospace; color: #666; margin-top: 5px; }
            </style></head><body>';
            
            $html .= '<div class="grid">';
            foreach ($items as $item) {
                $locText = '';
                if (!empty($item['bloco'])) $locText .= 'Bloco: ' . htmlspecialchars($item['bloco']);
                if (!empty($item['bloco']) && !empty($item['andar'])) $locText .= ' | ';
                if (!empty($item['andar'])) $locText .= 'Andar: ' . htmlspecialchars($item['andar']);

                $imgUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($item['hash']);

                $html .= '<div class="label">';
                $html .= '<div class="cat">' . htmlspecialchars($item['cat']) . '</div>';
                $html .= '<img src="' . $imgUrl . '" />';
                $html .= '<div class="name">' . htmlspecialchars($item['name']) . '</div>';
                if ($locText) $html .= '<div class="loc">' . $locText . '</div>';
                $html .= '<div class="id">' . htmlspecialchars($item['id']) . '</div>';
                $html .= '<div class="hash">' . htmlspecialchars($item['hash']) . '</div>';
                $html .= '</div>';
            }
            $html .= '</div></body></html>';

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("etiquetas_qr_" . date('Ymd_His') . ".pdf", ["Attachment" => true]);
            exit;
        }

        $this->render('admin_gerar_qr', get_defined_vars());
    }
}
