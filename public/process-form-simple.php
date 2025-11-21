<?php
/**
 * Processador de Formulário Simplificado
 * Apenas gera o PDF assinado, sem Google Sheets e Email
 */

// Desabilitar exibição de erros para não quebrar o JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Capturar erros fatais
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Erro fatal: ' . $error['message'] . ' em ' . $error['file'] . ':' . $error['line']
        ]);
    }
});

header('Content-Type: application/json');

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    // Log de debug
    $debug = [];
    $debug[] = 'Iniciando processamento';
    
    // Carregar autoload do Composer
    require_once __DIR__ . '/../vendor/autoload.php';
    $debug[] = 'Autoload carregado';
    
    // Importar classe do PDF
    use setasign\Fpdi\Fpdi;
    $debug[] = 'FPDI importado';
    
    // Sanitizar dados
    $razaoSocial = htmlspecialchars($_POST['razao_social'] ?? '');
    $cnpj = htmlspecialchars($_POST['cnpj'] ?? '');
    $assinatura = $_POST['assinatura'] ?? '';
    $assinaturaTipo = $_POST['assinatura_tipo'] ?? 'canvas';
    
    $debug[] = 'Dados recebidos: ' . $razaoSocial . ' - ' . $cnpj;
    
    // Validações básicas
    if (empty($razaoSocial) || empty($cnpj) || empty($assinatura)) {
        throw new Exception('Dados obrigatórios não preenchidos. Debug: ' . implode(' | ', $debug));
    }
    
    $debug[] = 'Validação OK';
    
    // Caminho do PDF original
    $pdfOriginal = __DIR__ . '/doc/Código de Relacionamento para Fornecedores de Bens e Serviços_2025.pdf';
    
    if (!file_exists($pdfOriginal)) {
        throw new Exception('PDF original não encontrado');
    }
    
    // Criar diretório de saída se não existir
    $outputDir = __DIR__ . '/../uploads/signed/';
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    // Criar instância do FPDI
    $pdf = new Fpdi();
    
    // Obter número de páginas
    $pageCount = $pdf->setSourceFile($pdfOriginal);
    
    // Importar todas as páginas
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);
        
        // Adicionar página
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
        
        // Se for a última página, adicionar assinatura
        if ($pageNo === $pageCount) {
            $pageWidth = $size['width'];
            $pageHeight = $size['height'];
            
            // Posição da assinatura (canto inferior direito)
            $signatureX = $pageWidth - 80;
            $signatureY = $pageHeight - 60;
            $signatureWidth = 70;
            $signatureHeight = 30;
            
            // Adicionar caixa de assinatura
            $pdf->SetDrawColor(0, 102, 204);
            $pdf->SetLineWidth(0.5);
            $pdf->Rect($signatureX - 5, $signatureY - 5, $signatureWidth + 10, $signatureHeight + 20);
            
            if ($assinaturaTipo === 'canvas') {
                // Assinatura desenhada (base64)
                try {
                    $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $assinatura);
                    $imageData = base64_decode($imageData);
                    
                    $tempFile = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
                    file_put_contents($tempFile, $imageData);
                    
                    $pdf->Image($tempFile, $signatureX, $signatureY, $signatureWidth, $signatureHeight, 'PNG');
                    unlink($tempFile);
                } catch (Exception $e) {
                    // Fallback
                    $pdf->SetFont('Arial', 'I', 10);
                    $pdf->SetXY($signatureX, $signatureY + 10);
                    $pdf->Cell($signatureWidth, 10, '[Assinatura Digital]', 0, 0, 'C');
                }
            } else {
                // Assinatura digitada
                $pdf->SetFont('Arial', 'I', 16);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetXY($signatureX, $signatureY + 8);
                $pdf->Cell($signatureWidth, 10, mb_convert_encoding($assinatura, 'ISO-8859-1', 'UTF-8'), 0, 0, 'C');
                
                // Linha abaixo do nome
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetLineWidth(0.3);
                $pdf->Line($signatureX, $signatureY + 20, $signatureX + $signatureWidth, $signatureY + 20);
            }
            
            // Adicionar informações do fornecedor
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetXY($signatureX - 5, $signatureY + $signatureHeight + 2);
            $pdf->Cell($signatureWidth + 10, 4, mb_convert_encoding($razaoSocial, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            
            $pdf->SetXY($signatureX - 5, $signatureY + $signatureHeight + 6);
            $pdf->Cell($signatureWidth + 10, 4, 'CNPJ: ' . $cnpj, 0, 1, 'C');
            
            $pdf->SetXY($signatureX - 5, $signatureY + $signatureHeight + 10);
            $pdf->Cell($signatureWidth + 10, 4, 'Data: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        }
    }
    
    // Gerar nome único para o arquivo
    $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);
    $timestamp = date('YmdHis');
    $filename = "codigo_relacionamento_assinado_{$cnpjLimpo}_{$timestamp}.pdf";
    $outputPath = $outputDir . $filename;
    
    // Salvar PDF
    $pdf->Output('F', $outputPath);
    
    $debug[] = 'PDF gerado com sucesso';
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Cadastro realizado com sucesso!',
        'pdf_assinado' => $filename,
        'debug' => $debug
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao processar: ' . $e->getMessage(),
        'debug' => isset($debug) ? $debug : []
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro fatal: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine(),
        'debug' => isset($debug) ? $debug : []
    ]);
}
