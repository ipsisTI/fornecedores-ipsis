<?php
namespace Ipsis\handlers;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/helpers.php';
require_once __DIR__ . '/../services/ValidationService.php';
require_once __DIR__ . '/../services/GoogleSheetsService.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../services/PDFSignatureService.php';

use Ipsis\services\ValidationService;
use Ipsis\services\GoogleSheetsService;
use Ipsis\services\EmailService;
use Ipsis\services\PDFSignatureService;

/**
 * Handler do Formulário
 * 
 * Processa o envio do formulário de cadastro
 */
class FormHandler {
    
    private $validator;
    private $sheetsService;
    private $emailService;
    private $pdfService;
    
    public function __construct() {
        $this->validator = new ValidationService();
        $this->sheetsService = new GoogleSheetsService();
        $this->emailService = new EmailService();
        $this->pdfService = new PDFSignatureService();
    }
    
    /**
     * Processa o formulário
     */
    public function process() {
        try {
            // Verificar método
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['success' => false, 'message' => 'Método não permitido'], 405);
            }
            
            // Verificar CSRF token
            if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
                jsonResponse(['success' => false, 'message' => 'Token de segurança inválido'], 403);
            }
            
            // Sanitizar dados
            $data = $this->sanitizeData($_POST);
            
            // Validar formulário
            if (!$this->validator->validateForm($data, $_FILES)) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Erro de validação',
                    'errors' => $this->validator->getErrors()
                ], 422);
            }
            
            // Verificar reCAPTCHA
            if (!$this->validator->verifyRecaptcha($data['recaptcha_token'])) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Falha na verificação reCAPTCHA'
                ], 422);
            }
            
            // Verificar CNPJ duplicado
            if ($this->validator->checkDuplicateCNPJ($data['cnpj'], $this->sheetsService)) {
                jsonResponse([
                    'success' => false,
                    'message' => 'CNPJ já cadastrado em nosso sistema'
                ], 422);
            }
            
            // Fazer upload do documento
            $documentPath = $this->uploadDocument($_FILES['documento']);
            if (!$documentPath) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Erro ao fazer upload do documento'
                ], 500);
            }
            
            // Salvar assinatura
            $signaturePath = $this->saveSignature($data);
            
            // Preparar dados do fornecedor
            $dadosFornecedor = [
                'razao_social' => $data['razao_social'],
                'nome_fantasia' => $data['nome_fantasia'],
                'cnpj' => formatCNPJ($data['cnpj']),
                'endereco' => $data['endereco'],
                'telefone' => formatPhone($data['telefone']),
                'email' => $data['email']
            ];
            
            // Gerar PDF assinado
            $pdfAssinadoPath = $this->pdfService->adicionarAssinatura(
                $data['assinatura'],
                $data['assinatura_tipo'],
                $dadosFornecedor
            );
            
            // Preparar dados para salvar
            $saveData = array_merge($dadosFornecedor, [
                'tipo_servico' => $data['tipo_servico'],
                'documento_url' => $documentPath,
                'assinatura_tipo' => $signaturePath,
                'pdf_assinado' => $pdfAssinadoPath ? basename($pdfAssinadoPath) : 'Erro ao gerar'
            ]);
            
            // Salvar no Google Sheets
            $this->sheetsService->ensureHeader();
            $saved = $this->sheetsService->appendRow($saveData);
            
            if (!$saved) {
                jsonResponse([
                    'success' => false,
                    'message' => 'Erro ao salvar dados'
                ], 500);
            }
            
            // Enviar emails
            $this->emailService->sendConfirmationEmail($saveData);
            $this->emailService->sendAdminNotification($saveData);
            
            // Resposta de sucesso
            jsonResponse([
                'success' => true,
                'message' => 'Cadastro realizado com sucesso! Você receberá um email de confirmação.',
                'pdf_assinado' => $pdfAssinadoPath ? basename($pdfAssinadoPath) : null
            ]);
            
        } catch (\Exception $e) {
            logError('Erro ao processar formulário: ' . $e->getMessage());
            jsonResponse([
                'success' => false,
                'message' => 'Erro ao processar cadastro. Tente novamente.'
            ], 500);
        }
    }
    
    /**
     * Sanitiza dados do formulário
     */
    private function sanitizeData($data) {
        return [
            'razao_social' => sanitizeString($data['razao_social'] ?? ''),
            'nome_fantasia' => sanitizeString($data['nome_fantasia'] ?? ''),
            'cnpj' => sanitizeString($data['cnpj'] ?? ''),
            'endereco' => sanitizeString($data['endereco'] ?? ''),
            'telefone' => sanitizeString($data['telefone'] ?? ''),
            'email' => sanitizeString($data['email'] ?? ''),
            'tipo_servico' => sanitizeString($data['tipo_servico'] ?? ''),
            'assinatura' => $data['assinatura'] ?? '',
            'assinatura_tipo' => sanitizeString($data['assinatura_tipo'] ?? ''),
            'recaptcha_token' => sanitizeString($data['recaptcha_token'] ?? '')
        ];
    }
    
    /**
     * Faz upload do documento
     */
    private function uploadDocument($file) {
        try {
            $fileName = generateUniqueFileName($file['name']);
            $destination = UPLOAD_DIR . $fileName;
            
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                return $fileName;
            }
            
            return false;
        } catch (\Exception $e) {
            logError('Erro ao fazer upload: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Salva assinatura
     */
    private function saveSignature($data) {
        try {
            $signatureDir = UPLOAD_DIR . 'assinaturas/';
            
            if (!is_dir($signatureDir)) {
                mkdir($signatureDir, 0755, true);
            }
            
            if ($data['assinatura_tipo'] === 'canvas') {
                // Assinatura desenhada (base64)
                $imageData = str_replace('data:image/png;base64,', '', $data['assinatura']);
                $imageData = str_replace(' ', '+', $imageData);
                $decodedImage = base64_decode($imageData);
                
                $fileName = 'assinatura_' . uniqid() . '_' . time() . '.png';
                $filePath = $signatureDir . $fileName;
                
                file_put_contents($filePath, $decodedImage);
                
                return 'Canvas: assinaturas/' . $fileName;
            } else {
                // Assinatura digitada
                return 'Digitada: ' . sanitizeString($data['assinatura']);
            }
        } catch (\Exception $e) {
            logError('Erro ao salvar assinatura: ' . $e->getMessage());
            return 'Erro ao salvar';
        }
    }
}

// Processar formulário se for requisição POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $handler = new FormHandler();
    $handler->process();
}
