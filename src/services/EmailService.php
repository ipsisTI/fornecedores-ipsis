<?php

namespace Ipsis\services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Serviço de envio de emails
 */
class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        
        // Configuração SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USER;
        $this->mailer->Password = SMTP_PASS;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = SMTP_PORT;
        $this->mailer->CharSet = 'UTF-8';
        
        // Remetente padrão
        $this->mailer->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    }

    /**
     * Envia email de confirmação para o fornecedor
     */
    public function enviarConfirmacaoFornecedor($dados)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($dados['email'], $dados['razao_social']);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Cadastro Recebido - Ipsis';
            
            $this->mailer->Body = $this->templateConfirmacaoFornecedor($dados);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log('Erro ao enviar email para fornecedor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia notificação para o admin
     */
    public function enviarNotificacaoAdmin($dados)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress(ADMIN_EMAIL);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Novo Cadastro de Fornecedor - ' . $dados['razao_social'];
            
            $this->mailer->Body = $this->templateNotificacaoAdmin($dados);
            $this->mailer->AltBody = strip_tags($this->mailer->Body);
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log('Erro ao enviar email para admin: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Template de email para o fornecedor
     */
    private function templateConfirmacaoFornecedor($dados)
    {
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
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .button { display: inline-block; padding: 12px 24px; background: #0066cc; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cadastro Recebido com Sucesso!</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>' . htmlspecialchars($dados['razao_social']) . '</strong>!</p>
            
            <p>Recebemos seu cadastro como fornecedor da Ipsis. Obrigado por seu interesse em fazer parte da nossa rede de parceiros!</p>
            
            <h3>Dados Recebidos:</h3>
            <ul>
                <li><strong>Razão Social:</strong> ' . htmlspecialchars($dados['razao_social']) . '</li>
                <li><strong>CNPJ:</strong> ' . htmlspecialchars($dados['cnpj']) . '</li>
                <li><strong>Email:</strong> ' . htmlspecialchars($dados['email']) . '</li>
                <li><strong>Telefone:</strong> ' . htmlspecialchars($dados['telefone']) . '</li>
            </ul>
            
            <h3>Próximos Passos:</h3>
            <ol>
                <li>Nossa equipe analisará seu cadastro</li>
                <li>Entraremos em contato em até 5 dias úteis</li>
                <li>Caso necessário, solicitaremos documentação adicional</li>
            </ol>
            
            <p><strong>Importante:</strong> Este é um email automático. Não responda a esta mensagem.</p>
        </div>
        <div class="footer">
            <p>&copy; ' . date('Y') . ' Ipsis. Todos os direitos reservados.</p>
            <p><a href="https://ipsis.com.br">www.ipsis.com.br</a></p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Template de email para o admin
     */
    private function templateNotificacaoAdmin($dados)
    {
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
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Novo Cadastro de Fornecedor</h2>
        </div>
        <div class="content">
            <p>Um novo fornecedor se cadastrou no sistema.</p>
            
            <table>
                <tr>
                    <th>Campo</th>
                    <th>Valor</th>
                </tr>
                <tr>
                    <td>Data/Hora</td>
                    <td>' . date('d/m/Y H:i:s') . '</td>
                </tr>
                <tr>
                    <td>Razão Social</td>
                    <td>' . htmlspecialchars($dados['razao_social']) . '</td>
                </tr>
                <tr>
                    <td>CNPJ</td>
                    <td>' . htmlspecialchars($dados['cnpj']) . '</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>' . htmlspecialchars($dados['email']) . '</td>
                </tr>
                <tr>
                    <td>Telefone</td>
                    <td>' . htmlspecialchars($dados['telefone']) . '</td>
                </tr>
                <tr>
                    <td>Tipo de Serviço</td>
                    <td>' . htmlspecialchars($dados['tipo_servico']) . '</td>
                </tr>
            </table>
            
            <p><strong>Ações:</strong></p>
            <ul>
                <li>Verificar dados na planilha do Google Sheets</li>
                <li>Baixar PDF assinado do Google Drive</li>
                <li>Entrar em contato com o fornecedor</li>
            </ul>
        </div>
    </div>
</body>
</html>';
    }
}
