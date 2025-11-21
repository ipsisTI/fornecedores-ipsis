# Arquitetura do Sistema - Ipsis Fornecedores

## Visão Geral
Sistema web para cadastro e qualificação de fornecedores com integração ao Google Sheets, desenvolvido seguindo princípios de Clean Code e arquitetura em camadas.

## Stack Tecnológica

### Backend
- PHP 7.4+ (linguagem principal)
- Composer (gerenciador de dependências)
- Google Sheets API v4 (armazenamento)
- PHPMailer (envio de emails)
- phpdotenv (variáveis de ambiente)

### Frontend
- HTML5 (estrutura semântica)
- CSS3 (estilização responsiva)
- JavaScript Vanilla (interatividade)
- Canvas API (assinatura digital)

### Infraestrutura
- Nginx (servidor web)
- Ubuntu Server (sistema operacional)
- Let's Encrypt (certificados SSL)
- AWS EC2 (hospedagem)

### Segurança
- Google reCAPTCHA v3
- CSRF Tokens
- Input Sanitization
- HTTPS obrigatório

## Arquitetura em Camadas

```
┌─────────────────────────────────────────┐
│    Camada de Apresentação               │
│    (HTML/CSS/JS - Interface)            │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│    Camada de Controle                   │
│    (FormHandler - Processamento)        │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│    Camada de Serviços                   │
│    (Validação, Email, Google Sheets)    │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│    Camada de Dados                      │
│    (Google Sheets, Filesystem)          │
└─────────────────────────────────────────┘
```

## Componentes Principais

### FormHandler (src/handlers/FormHandler.php)
**Responsabilidade:** Orquestrar o processamento do formulário

**Métodos:**
- `process()`: Processa submissão
- `sanitizeData()`: Limpa dados
- `uploadDocument()`: Gerencia uploads
- `saveSignature()`: Salva assinatura

### ValidationService (src/services/ValidationService.php)
**Responsabilidade:** Validar dados do formulário

**Métodos:**
- `validateForm()`: Valida todos os campos
- `validateFile()`: Valida arquivo
- `verifyRecaptcha()`: Verifica reCAPTCHA
- `checkDuplicateCNPJ()`: Verifica duplicidade

### GoogleSheetsService (src/services/GoogleSheetsService.php)
**Responsabilidade:** Integração com Google Sheets API

**Métodos:**
- `initializeClient()`: Configura cliente
- `appendRow()`: Adiciona linha
- `getValues()`: Lê valores
- `ensureHeader()`: Cria cabeçalho

### EmailService (src/services/EmailService.php)
**Responsabilidade:** Envio de emails

**Métodos:**
- `sendConfirmationEmail()`: Email para fornecedor
- `sendAdminNotification()`: Notificação admin

## Fluxo de Dados

```
Usuário → Frontend (form.js)
    ↓
Validação Client-Side
    ↓
reCAPTCHA v3
    ↓
POST → FormHandler.php
    ↓
Validação CSRF
    ↓
Sanitização
    ↓
ValidationService
    ↓
Upload + Assinatura
    ↓
GoogleSheetsService
    ↓
EmailService
    ↓
Resposta JSON
```

## Segurança

### Proteções Implementadas
- CSRF Protection (token por sessão)
- Input Sanitization (htmlspecialchars, strip_tags)
- XSS Prevention (sanitização de outputs)
- File Upload Security (validação tipo/tamanho)
- reCAPTCHA v3 (score mínimo 0.5)
- HTTPS obrigatório
- Rate Limiting (10 req/s)

## Validações

### Client-Side
- Campos obrigatórios
- Máscaras (CNPJ, telefone)
- Formato de email
- Tamanho/tipo de arquivo
- Assinatura presente

### Server-Side
- CNPJ válido (dígitos verificadores)
- Email válido (filter_var)
- Telefone válido (10-11 dígitos)
- Arquivo válido
- reCAPTCHA válido
- CNPJ não duplicado

## Integração Google Sheets

### Estrutura da Planilha
| Coluna | Campo | Tipo |
|--------|-------|------|
| A | Data/Hora | Timestamp |
| B | Razão Social | Texto |
| C | Nome Fantasia | Texto |
| D | CNPJ | Formatado |
| E | Endereço | Texto |
| F | Telefone | Formatado |
| G | Email | Email |
| H | Tipo de Serviço | Texto |
| I | Documento | Arquivo |
| J | Assinatura | Tipo + ref |
| K | Status | Pendente/Aprovado |

## Performance
- Autoloader otimizado (Composer)
- Gzip compression (Nginx)
- Cache de assets (1 ano)
- Upload máximo: 5MB
- Timeout PHP: 300s

## Logs
- Nginx: /var/log/nginx/fornecedores-ipsis-*.log
- Aplicação: logs/error_YYYY-MM-DD.log

## Manutenção
- Backup semanal da planilha
- Limpeza de logs (30 dias)
- Atualização trimestral de dependências
