<?php
namespace Ipsis\services;

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Serviço de Email
 * 
 * Gerencia envio de emails usando PHPMailer
 */
class EmailService {
    
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }
    
    /**
     * Configura PHPMailer
     */
    private function configure() {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USER;
            $this->mailer->Password = SMTP_PASS;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = SMTP_PORT;
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        } catch (Exception $e) {
            logError('Erro ao configurar email: ' . $e->getMessage());
        }
    }
    
    /**
     * Envia email de confirmação para o fornecedor
     */
    public function sendConfirmationEmail($data) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($data['email'], $data['razao_social']);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Cadastro Recebido - Ipsis';
            $this->mailer->Body = $this->getConfirmationTemplate($data);
            $this->mailer->AltBody = $this->getConfirmationTextTemplate($data);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            logError('Erro ao enviar email de confirmação: ' . $e->getMessage(), $data);
            return false;
        }
    }
    
    /**
     * Envia notificação para o admin
     */
    public function sendAdminNotification($data) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress(ADMIN_EMAIL);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Novo Cadastro de Fornecedor - ' . $data['razao_social'];
            $this->mailer->Body = $this->getAdminTemplate($data);
            $this->mailer->AltBody = $this->getAdminTextTemplate($data);
            
            return $this->mailer->send();
        } catch (Exception $e) {
            logError('Erro ao enviar notificação admin: ' . $e->getMessage(), $data);
            return false;
        }
    }
    
    /**
     * Template HTML de confirmação
     */
    private function getConfirmationTemplate($data) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0066cc; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                .button { display: inline-block; padding: 12px 30px; background: #0066cc; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .info { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #0066cc; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Cadastro Recebido com Sucesso!</h1>
                </div>
                <div class="content">
                    <p>Olá, <strong>' . htmlspecialchars($data['razao_social']) . '</strong>!</p>
                    
                    <p>Recebemos seu cadastro como fornecedor da Ipsis. Seus dados estão sendo analisados por nossa equipe.</p>
                    
                    <div class="info">
                        <h3>Dados Cadastrados:</h3>
                        <p><strong>Razão Social:</strong> ' . htmlspecialchars($data['razao_social']) . '</p>
                        <p><strong>Nome Fantasia:</strong> ' . htmlspecialchars($data['nome_fantasia']) . '</p>
                        <p><strong>CNPJ:</strong> ' . formatCNPJ($data['cnpj']) . '</p>
                        <p><strong>Email:</strong> ' . htmlspecialchars($data['email']) . '</p>
                        <p><strong>Telefone:</strong> ' . formatPhone($data['telefone']) . '</p>
                        <p><strong>Tipo de Serviço:</strong> ' . htmlspecialchars($data['tipo_servico']) . '</p>
                    </div>
                    
                    <p>Em breve entraremos em contato para dar continuidade ao processo de qualificação.</p>
                    
                    <p>Se você tiver alguma dúvida, não hesite em nos contatar.</p>
                    
                    <p style="margin-top: 30px;">Atenciosamente,<br><strong>Equipe Ipsis</strong></p>
                </div>
                <div class="footer">
                    <p>Este é um email automático, por favor não responda.</p>
                    <p>&copy; ' . date('Y') . ' Ipsis. Todos os direitos reservados.</p>
                    <p><a href="https://ipsis.com.br">www.ipsis.com.br</a></p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
    
    /**
     * Template texto de confirmação
     */
    private function getConfirmationTextTemplate($data) {
        return "
Cadastro Recebido com Sucesso!

Olá, {$data['razao_social']}!

Recebemos seu cadastro como fornecedor da Ipsis. Seus dados estão sendo analisados por nossa equipe.

Dados Cadastrados:
- Razão Social: {$data['razao_social']}
- Nome Fantasia: {$data['nome_fantasia']}
- CNPJ: " . formatCNPJ($data['cnpj']) . "
- Email: {$data['email']}
- Telefone: " . formatPhone($data['telefone']) . "
- Tipo de Serviço: {$data['tipo_servico']}

Em breve entraremos em contato para dar continuidade ao processo de qualificação.

Atenciosamente,
Equipe Ipsis

www.ipsis.com.br
        ";
    }
    
    /**
     * Template HTML para admin
     */
    private function getAdminTemplate($data) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #0066cc; color: white; padding: 20px; }
                .content { background: #f9f9f9; padding: 30px; }
                .info { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #0066cc; }
                table { width: 100%; border-collapse: collapse; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                td:first-child { font-weight: bold; width: 40%; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Novo Cadastro de Fornecedor</h2>
                </div>
                <div class="content">
                    <p>Um novo fornecedor se cadastrou no sistema.</p>
                    
                    <div class="info">
                        <table>
                            <tr><td>Data/Hora:</td><td>' . date('d/m/Y H:i:s') . '</td></tr>
                            <tr><td>Razão Social:</td><td>' . htmlspecialchars($data['razao_social']) . '</td></tr>
                            <tr><td>Nome Fantasia:</td><td>' . htmlspecialchars($data['nome_fantasia']) . '</td></tr>
                            <tr><td>CNPJ:</td><td>' . formatCNPJ($data['cnpj']) . '</td></tr>
                            <tr><td>Endereço:</td><td>' . htmlspecialchars($data['endereco']) . '</td></tr>
                            <tr><td>Telefone:</td><td>' . formatPhone($data['telefone']) . '</td></tr>
                            <tr><td>Email:</td><td>' . htmlspecialchars($data['email']) . '</td></tr>
                            <tr><td>Tipo de Serviço:</td><td>' . htmlspecialchars($data['tipo_servico']) . '</td></tr>
                            <tr><td>Documento:</td><td>' . htmlspecialchars($data['documento_url']) . '</td></tr>
                            <tr><td>Assinatura:</td><td>' . htmlspecialchars($data['assinatura_tipo']) . '</td></tr>
                        </table>
                    </div>
                    
                    <p><a href="https://docs.google.com/spreadsheets/d/' . GOOGLE_SHEET_ID . '" style="display: inline-block; padding: 12px 30px; background: #0066cc; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0;">Ver na Planilha</a></p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
    
    /**
     * Template texto para admin
     */
    private function getAdminTextTemplate($data) {
        return "
Novo Cadastro de Fornecedor

Data/Hora: " . date('d/m/Y H:i:s') . "
Razão Social: {$data['razao_social']}
Nome Fantasia: {$data['nome_fantasia']}
CNPJ: " . formatCNPJ($data['cnpj']) . "
Endereço: {$data['endereco']}
Telefone: " . formatPhone($data['telefone']) . "
Email: {$data['email']}
Tipo de Serviço: {$data['tipo_servico']}
Documento: {$data['documento_url']}
Assinatura: {$data['assinatura_tipo']}

Ver planilha: https://docs.google.com/spreadsheets/d/" . GOOGLE_SHEET_ID . "
        ";
    }
}
