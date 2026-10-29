<?php
date_default_timezone_set('America/Sao_Paulo');
ob_start();
$statusCode = 500;
$warnings = [];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $statusCode = 405;
        throw new Exception('Metodo nao permitido');
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../src/config/config.php';
    require_once __DIR__ . '/../src/utils/helpers.php';
    require_once __DIR__ . '/../src/services/GoogleDriveService.php';

    // Proteção CSRF: token obrigatório e validado no servidor
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $statusCode = 403;
        throw new Exception('Sua sessao expirou. Recarregue a pagina e tente novamente.');
    }

    // reCAPTCHA v3 verificado no servidor (fail-closed)
    if (!verifyRecaptcha($_POST['recaptcha_token'] ?? '')) {
        $statusCode = 422;
        throw new Exception('Falha na verificacao anti-spam. Recarregue a pagina e tente novamente.');
    }

    // Rate limit por sessão: intervalo mínimo de 30s entre envios
    if (isset($_SESSION['last_submit_at']) && (time() - $_SESSION['last_submit_at']) < 30) {
        $statusCode = 429;
        throw new Exception('Aguarde alguns instantes antes de enviar novamente.');
    }

    // Coleta e sanitização dos campos de texto
    $razaoSocial = trim(strip_tags($_POST['razao_social'] ?? ''));
    $cnpj = trim(strip_tags($_POST['cnpj'] ?? ''));
    $telefone = trim(strip_tags($_POST['telefone'] ?? ''));
    $email = trim(strip_tags($_POST['email'] ?? ''));
    $tipoServico = trim(strip_tags($_POST['tipo_servico'] ?? ''));
    $assinatura = $_POST['assinatura'] ?? '';
    $assinaturaTipo = $_POST['assinatura_tipo'] ?? 'canvas';

    if (!in_array($assinaturaTipo, ['canvas', 'typed'], true)) {
        $statusCode = 422;
        throw new Exception('Tipo de assinatura invalido.');
    }

    if ($assinaturaTipo === 'canvas') {
        // Assinatura desenhada: deve ser um data URI de imagem com no máximo 2MB
        if (strpos($assinatura, 'data:image') !== 0 || strlen($assinatura) > 2000000) {
            $statusCode = 422;
            throw new Exception('Assinatura invalida. Assine o documento novamente.');
        }
    } else {
        // Assinatura digitada: texto sanitizado com no máximo 150 caracteres
        $assinatura = trim(strip_tags($assinatura));
        if ($assinatura === '') {
            $statusCode = 422;
            throw new Exception('Assinatura obrigatoria. Digite seu nome para assinar.');
        }
        $assinatura = mb_substr($assinatura, 0, 150);
    }

    // Validações específicas do formulário
    if ($razaoSocial === '' || strlen($razaoSocial) < 3) {
        $statusCode = 422;
        throw new Exception('Informe a razão social');
    }

    // Limites de tamanho para os campos que vão para a planilha
    if (mb_strlen($razaoSocial) > 150) {
        $statusCode = 422;
        throw new Exception('Razão social deve ter no máximo 150 caracteres.');
    }

    if (!isValidCNPJ($cnpj)) {
        $statusCode = 422;
        throw new Exception('CNPJ inválido. Verifique os números informados.');
    }

    if (!isValidPhone($telefone)) {
        $statusCode = 422;
        throw new Exception('Telefone inválido');
    }

    if (!isValidEmail($email)) {
        $statusCode = 422;
        throw new Exception('E-mail inválido');
    }

    if (mb_strlen($email) > 150) {
        $statusCode = 422;
        throw new Exception('E-mail deve ter no máximo 150 caracteres.');
    }

    // Whitelist do select "Tipo de Serviço" (opções reais do formulário)
    $tiposServicoValidos = ['', 'Consultoria', 'Desenvolvimento', 'Suporte Técnico', 'Infraestrutura', 'Treinamento', 'Manutenção', 'Outros'];
    if (!in_array($tipoServico, $tiposServicoValidos, true)) {
        $statusCode = 422;
        throw new Exception('Valor inválido para tipo de serviço.');
    }

    $isBase64Image = (strpos($assinatura, 'data:image') === 0);

    $pdf = new \setasign\Fpdi\Fpdi();
    $pdfPath = __DIR__ . '/doc/Código de Relacionamento para Fornecedores de Bens e Serviços_2025.pdf';

    if (!file_exists($pdfPath)) {
        // Caminho completo só no log do servidor; nunca na resposta ao cliente
        error_log('PDF original nao encontrado: ' . $pdfPath);
        throw new Exception('Documento base nao encontrado. Contate o suporte.');
    }

    $pageCount = $pdf->setSourceFile($pdfPath);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
    }

    $pdf->AddPage('P', 'A4');

    $logoPath = __DIR__ . '/assets/images/logo.png';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 85, 15, 40, 0, 'PNG');
    }

    $pdf->SetFont('Arial', 'B', 18);
    $pdf->SetTextColor(0, 102, 204);
    $pdf->SetY(45);
    $pdf->Cell(0, 10, 'TERMO DE ACEITE E ASSINATURA', 0, 1, 'C');

    $pdf->SetDrawColor(0, 102, 204);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(20, 60, 190, 60);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetY(70);
    $pdf->Cell(0, 10, 'DADOS DA EMPRESA', 0, 1, 'L');

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 8, 'Razao Social:', 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, utf8_decode($razaoSocial), 0, 1);

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 8, 'CNPJ:', 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, $cnpj, 0, 1);

    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'DADOS DE CONTATO', 0, 1, 'L');

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 8, 'Telefone:', 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, $telefone, 0, 1);

    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(50, 8, 'Email:', 0, 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 8, $email, 0, 1);

    if (!empty($tipoServico)) {
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(50, 8, 'Tipo de Servico:', 0, 0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, utf8_decode($tipoServico), 0, 1);
    }

    $pdf->Ln(15);

    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 6, 'Declaro que li e concordo integralmente com o Codigo de Relacionamento para Fornecedores de Bens e Servicos, comprometendo-me a cumprir todas as diretrizes, politicas e procedimentos estabelecidos neste documento.', 0, 'J');

    $pdf->Ln(20);
    $yAssinatura = $pdf->GetY();

    $pdf->SetDrawColor(0, 102, 204);
    $pdf->SetLineWidth(1);
    $pdf->Rect(55, $yAssinatura, 100, 40);

    // Adicionar assinatura
    if ($assinaturaTipo === 'canvas' && $isBase64Image) {
        // Assinatura desenhada
        try {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $assinatura);
            $imageData = base64_decode($imageData);

            if ($imageData && strlen($imageData) > 0) {
                $tempFile = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
                file_put_contents($tempFile, $imageData);

                if (file_exists($tempFile) && filesize($tempFile) > 0) {
                    $pdf->Image($tempFile, 60, $yAssinatura + 5, 90, 30, 'PNG');
                    unlink($tempFile);
                } else {
                    throw new Exception('Arquivo vazio');
                }
            } else {
                throw new Exception('Dados vazios');
            }
        } catch (Exception $e) {
            // Fallback
            $pdf->SetFont('Arial', 'I', 16);
            $pdf->SetTextColor(0, 102, 204);
            $pdf->SetXY(55, $yAssinatura + 12);
            $pdf->Cell(100, 10, '[Assinatura Digital]', 0, 0, 'C');
        }
    } else {
        // Assinatura digitada ou fallback
        $pdf->SetFont('Arial', 'I', 20);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(55, $yAssinatura + 12);
        $textoAssinatura = strlen($assinatura) < 100 ? $assinatura : '[Assinatura Digital]';
        $pdf->Cell(100, 10, $textoAssinatura, 0, 0, 'C');
    }

    $pdf->SetDrawColor(0, 0, 0);
    $pdf->SetLineWidth(0.3);
    $yLinha = $yAssinatura + 40;
    $pdf->Line(55, $yLinha, 155, $yLinha);

    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(55, $yLinha + 2);
    $pdf->Cell(100, 5, utf8_decode($razaoSocial), 0, 1, 'C');
    $pdf->SetX(55);
    $pdf->Cell(100, 5, 'CNPJ: ' . $cnpj, 0, 1, 'C');
    $pdf->SetX(55);
    $pdf->Cell(100, 5, 'Data: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    $outputDir = __DIR__ . '/../uploads/signed/';
    if (!file_exists($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);
    $timestamp = date('YmdHis');
    $filename = "codigo_relacionamento_assinado_{$cnpjLimpo}_{$timestamp}.pdf";

    $pdf->Output('F', $outputDir . $filename);

    // Autoriza o download deste PDF apenas para a sessão que o gerou (últimos 10)
    $_SESSION['downloadable_pdfs'][] = $filename;
    if (count($_SESSION['downloadable_pdfs']) > 10) {
        array_shift($_SESSION['downloadable_pdfs']);
    }

    $driveLink = '';
    try {
        $driveService = new \Ipsis\services\GoogleDriveService();
        $uploadResult = $driveService->uploadFile($outputDir . $filename, $filename);
        $driveLink = $uploadResult['link'];
    } catch (Exception $e) {
        error_log('Erro Drive: ' . $e->getMessage());
        $warnings[] = 'drive';
        logIntegration('drive_upload_failed', ['arquivo' => $filename, 'documento' => $cnpj, 'erro' => $e->getMessage()]);
    }

    $rowData = null;
    try {
        $client = new Google_Client();
        $client->setApplicationName('Ipsis Fornecedores');
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig(GOOGLE_CREDENTIALS_PATH);
        $client->setAccessType('offline');

        $service = new Google_Service_Sheets($client);

        if (!empty($driveLink)) {
            // Célula de link construída pelo servidor (intencional): aponta para o
            // webViewLink do Drive; o rótulo (nome do arquivo) é sanitizado por
            // consistência com os demais valores
            $pdfCell = '=HYPERLINK("' . $driveLink . '"; "' . sanitizeSheetsValue($filename) . '")';
        } else {
            // Sem Drive, o link de download-pdf.php ficaria 403 para a equipe
            // (gate de sessão): grava apenas o nome do arquivo como texto puro
            $pdfCell = sanitizeSheetsValue($filename);
        }

        // Todo dado do usuário gravado na planilha passa pela sanitização
        // anti-injeção de fórmula (a célula =HYPERLINK acima é a exceção)
        $rowData = [[
            date('d/m/Y H:i:s'),
            sanitizeSheetsValue($razaoSocial),
            sanitizeSheetsValue($cnpj),
            sanitizeSheetsValue($telefone),
            sanitizeSheetsValue($email),
            sanitizeSheetsValue($tipoServico),
            $pdfCell
        ]];

        $body = new Google_Service_Sheets_ValueRange(['values' => $rowData]);
        $params = ['valueInputOption' => 'USER_ENTERED'];

        $service->spreadsheets_values->append(GOOGLE_SHEET_ID, 'A:G', $body, $params);
    } catch (Exception $e) {
        error_log('Erro Sheets: ' . $e->getMessage());
        $warnings[] = 'sheets';
        logIntegration('sheets_append_failed', ['arquivo' => $filename, 'linha' => $rowData, 'drive_link' => $driveLink, 'erro' => $e->getMessage()]);
    }

    logIntegration('assinatura_registrada', [
        'arquivo' => $filename,
        'drive_ok' => !in_array('drive', $warnings),
        'sheets_ok' => !in_array('sheets', $warnings),
        'dados' => [
            'razao_social' => $razaoSocial,
            'cnpj' => $cnpj,
            'telefone' => $telefone,
            'email' => $email,
            'tipo_servico' => $tipoServico,
            'assinatura_tipo' => $assinaturaTipo
        ]
    ]);

    // Marca o momento do envio apenas aqui, imediatamente antes da resposta de
    // sucesso: se qualquer integração (PDF/Drive/Sheets) falhar antes disso, o
    // usuário pode reenviar sem esperar os 30s do rate limit.
    $_SESSION['last_submit_at'] = time();

    // Consome o token CSRF após o sucesso (token de uso único)
    unset($_SESSION['csrf_token']);

    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Cadastro realizado com sucesso!',
        'pdf_assinado' => $filename,
        'warnings' => $warnings
    ]);

} catch (Throwable $e) {
    // Throwable (e não apenas Exception): erros do PHP (TypeError, Error etc.)
    // também caem aqui e retornam JSON em vez de HTML quebrado
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    // Em erros 5xx a mensagem bruta nunca vai ao cliente: exceções de
    // bibliotecas (FPDF/FPDI, Google API, Dotenv) podem vazar caminhos
    // absolutos e detalhes internos do servidor. O detalhe fica no error_log.
    if ($statusCode >= 500) {
        error_log('submit-form (' . date('Y-m-d H:i:s') . '): ' . $e->getMessage());
    }
    $mensagemErro = ($statusCode >= 500 && !defined('APP_DEBUG')) || ($statusCode >= 500 && !APP_DEBUG)
        ? 'Erro interno ao processar a assinatura. Tente novamente.'
        : $e->getMessage();
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'message' => $mensagemErro
    ]);
}
