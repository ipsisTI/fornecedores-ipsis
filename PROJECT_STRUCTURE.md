# Estrutura do Projeto - Ipsis Fornecedores

## Árvore de Diretórios

```
fornecedores-ipsis/
│
├── 📁 public/                          # Arquivos públicos (Document Root do Nginx)
│   ├── 📄 index.php                   # Página principal do formulário
│   └── 📁 assets/
│       ├── 📁 css/
│       │   └── 📄 style.css           # Estilos customizados
│       ├── 📁 js/
│       │   └── 📄 form.js             # JavaScript do formulário
│       └── 📁 images/
│           ├── 📄 logo.png            # Logo da Ipsis (baixar do site)
│           └── 📄 README.md           # Instruções para obter logo
│
├── 📁 src/                             # Código-fonte da aplicação
│   ├── 📁 config/
│   │   ├── 📄 config.php              # Configurações gerais
│   │   └── 🔒 credentials.json        # Credenciais Google (não versionado)
│   │
│   ├── 📁 services/                    # Camada de serviços
│   │   ├── 📄 ValidationService.php   # Validação de dados
│   │   ├── 📄 GoogleSheetsService.php # Integração Google Sheets
│   │   └── 📄 EmailService.php        # Envio de emails
│   │
│   ├── 📁 handlers/                    # Controladores
│   │   └── 📄 FormHandler.php         # Processamento do formulário
│   │
│   └── 📁 utils/                       # Utilitários
│       └── 📄 helpers.php              # Funções auxiliares
│
├── 📁 uploads/                         # Arquivos enviados pelos usuários
│   ├── 📁 assinaturas/                # Assinaturas digitais (canvas)
│   └── 📄 .gitkeep                    # Manter pasta no Git
│
├── 📁 vendor/                          # Dependências do Composer (não versionado)
│
├── 📁 logs/                            # Logs da aplicação (criado automaticamente)
│
├── 📁 tests/                           # Scripts de teste
│   ├── 📄 test-connection.php         # Teste de configuração
│   └── 📄 README.md                   # Documentação de testes
│
├── 🔒 .env                             # Variáveis de ambiente (não versionado)
├── 📄 .env.example                    # Exemplo de variáveis de ambiente
├── 📄 .gitignore                      # Arquivos ignorados pelo Git
├── 📄 composer.json                   # Dependências PHP
├── 📄 composer.lock                   # Lock de versões (não versionado)
│
├── 📄 README.md                       # Documentação principal
├── 📄 INSTALL.md                      # Guia de instalação detalhado
├── 📄 DEPLOY.md                       # Guia de deploy rápido
├── 📄 QUICK_START.md                  # Guia rápido
├── 📄 ARCHITECTURE.md                 # Documentação da arquitetura
├── 📄 MAINTENANCE.md                  # Guia de manutenção
├── 📄 CHECKLIST.md                    # Checklist de deploy
├── 📄 PROJECT_STRUCTURE.md            # Este arquivo
│
├── 📄 nginx.conf.example              # Exemplo de configuração Nginx
└── 📄 setup.sh                        # Script de setup automático
```

## Descrição dos Componentes

### 📁 public/
**Propósito:** Arquivos acessíveis publicamente via web

**Arquivos principais:**
- `index.php`: Interface do formulário de cadastro
- `assets/css/style.css`: Estilos baseados no design da Ipsis
- `assets/js/form.js`: Validações, máscaras, canvas de assinatura
- `assets/images/logo.png`: Logo oficial da Ipsis

### 📁 src/
**Propósito:** Código-fonte da aplicação (não acessível via web)

**Estrutura:**
- `config/`: Configurações e credenciais
- `services/`: Lógica de negócio (validação, email, sheets)
- `handlers/`: Controladores de requisições
- `utils/`: Funções auxiliares reutilizáveis

### 📁 uploads/
**Propósito:** Armazenamento de arquivos enviados

**Conteúdo:**
- Documentos dos fornecedores (PDF, DOC, imagens)
- Assinaturas digitais em formato PNG
- Protegido no Nginx (acesso interno apenas)

### 📁 tests/
**Propósito:** Scripts de teste e validação

**Conteúdo:**
- `test-connection.php`: Verifica configurações antes do deploy

### 📁 vendor/
**Propósito:** Dependências PHP instaladas via Composer

**Bibliotecas:**
- google/apiclient: Google Sheets API
- phpmailer/phpmailer: Envio de emails
- vlucas/phpdotenv: Gerenciamento de .env

### 📁 logs/
**Propósito:** Logs da aplicação

**Conteúdo:**
- `error_YYYY-MM-DD.log`: Erros por data
- Criado automaticamente quando necessário

## Arquivos de Configuração

### .env
**Propósito:** Variáveis de ambiente (credenciais sensíveis)

**Conteúdo:**
- IDs e chaves do Google (Sheets, reCAPTCHA)
- Credenciais SMTP
- Configurações da aplicação

**⚠️ IMPORTANTE:** Nunca versionar este arquivo!

### composer.json
**Propósito:** Definição de dependências PHP

**Dependências:**
- PHP >= 7.4
- Google API Client
- PHPMailer
- phpdotenv

### nginx.conf.example
**Propósito:** Configuração do servidor web

**Recursos:**
- HTTPS obrigatório
- Headers de segurança
- Proteção de arquivos sensíveis
- Cache de assets
- Rate limiting

## Arquivos de Documentação

| Arquivo | Propósito |
|---------|-----------|
| README.md | Visão geral do projeto |
| INSTALL.md | Instalação passo a passo (detalhada) |
| DEPLOY.md | Deploy rápido (resumido) |
| QUICK_START.md | Guia rápido para começar |
| ARCHITECTURE.md | Arquitetura técnica |
| MAINTENANCE.md | Guia de manutenção |
| CHECKLIST.md | Checklist de deploy |
| PROJECT_STRUCTURE.md | Estrutura do projeto (este arquivo) |

## Fluxo de Arquivos

### Requisição do Usuário
```
Usuário → Nginx → public/index.php
                      ↓
                  form.js (validação client-side)
                      ↓
                  FormHandler.php
                      ↓
            ┌─────────┴─────────┐
            ↓                   ↓
    ValidationService    GoogleSheetsService
            ↓                   ↓
      EmailService         Planilha Google
```

### Upload de Arquivo
```
Usuário → Formulário → FormHandler.php
                            ↓
                    Validação (tipo, tamanho)
                            ↓
                    uploads/documento_*.pdf
                            ↓
                    Nome salvo na planilha
```

### Assinatura Digital
```
Canvas/Input → JavaScript → Base64/Texto
                                ↓
                        FormHandler.php
                                ↓
                    uploads/assinaturas/*.png
                                ↓
                    Referência na planilha
```

## Permissões Recomendadas

```bash
# Proprietário
chown -R www-data:www-data /var/www/fornecedores-ipsis

# Diretórios
chmod 755 /var/www/fornecedores-ipsis
chmod 755 public/
chmod 755 src/

# Arquivos
chmod 644 public/index.php
chmod 644 src/**/*.php

# Uploads (escrita)
chmod 775 uploads/
chmod 775 uploads/assinaturas/

# Arquivos sensíveis (leitura restrita)
chmod 600 .env
chmod 600 src/config/credentials.json

# Executável
chmod +x setup.sh
```

## Tamanhos Aproximados

| Componente | Tamanho |
|------------|---------|
| Código-fonte (src/) | ~50 KB |
| Frontend (public/) | ~30 KB |
| Documentação | ~100 KB |
| Dependências (vendor/) | ~15 MB |
| Logo | ~50 KB |
| **Total (sem uploads)** | **~15 MB** |

## Crescimento Esperado

### uploads/
- Documento médio: 500 KB
- Assinatura média: 50 KB
- Por cadastro: ~550 KB
- 100 cadastros: ~55 MB
- 1000 cadastros: ~550 MB

### logs/
- Log diário: ~1-5 MB
- Manter 30 dias: ~30-150 MB

## Backup Recomendado

### Essencial (diário)
- `.env`
- `src/config/credentials.json`
- Planilha Google Sheets

### Importante (semanal)
- `uploads/`

### Opcional (mensal)
- Código-fonte completo
- Logs

## Segurança

### Arquivos Protegidos no Nginx
```
❌ /.env
❌ /src/
❌ /vendor/
❌ /logs/
❌ /tests/
❌ *.json
❌ *.md
```

### Arquivos Públicos
```
✅ /public/index.php
✅ /public/assets/css/
✅ /public/assets/js/
✅ /public/assets/images/
```

## Integração com Git

### Versionado
- Código-fonte (src/, public/)
- Documentação (*.md)
- Configurações de exemplo (.env.example, nginx.conf.example)
- Scripts (setup.sh, tests/)

### Não Versionado (.gitignore)
- `.env`
- `src/config/credentials.json`
- `vendor/`
- `uploads/`
- `logs/`
- `composer.lock`

## Próximos Passos

1. ✅ Estrutura criada
2. ⏳ Configurar .env
3. ⏳ Upload credentials.json
4. ⏳ Instalar dependências (composer install)
5. ⏳ Baixar logo
6. ⏳ Configurar Nginx
7. ⏳ Testar (test-connection.php)
8. ⏳ Deploy em produção

## Contato

Para dúvidas sobre a estrutura do projeto, entre em contato com a equipe de desenvolvimento da Ipsis.
