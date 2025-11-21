# Guia Rápido - Cadastro de Fornecedores Ipsis

## Para Desenvolvedores

### 1. Clonar/Upload do Projeto
```bash
cd /var/www
git clone seu-repo fornecedores-ipsis
# ou fazer upload via SCP
```

### 2. Instalar Dependências
```bash
cd fornecedores-ipsis
composer install
```

### 3. Configurar Ambiente
```bash
cp .env.example .env
nano .env
```

Preencher:
- GOOGLE_SHEET_ID
- RECAPTCHA_SITE_KEY e RECAPTCHA_SECRET_KEY
- Credenciais SMTP
- ADMIN_EMAIL

### 4. Upload credentials.json
Colocar em: `src/config/credentials.json`

### 5. Configurar Permissões
```bash
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis
sudo chmod -R 755 /var/www/fornecedores-ipsis
sudo chmod -R 775 uploads/
sudo chmod 600 .env
sudo chmod 600 src/config/credentials.json
```

### 6. Configurar Nginx
```bash
sudo cp nginx.conf.example /etc/nginx/sites-available/fornecedores-ipsis
sudo nano /etc/nginx/sites-available/fornecedores-ipsis
# Ajustar domínio e caminhos SSL
sudo ln -s /etc/nginx/sites-available/fornecedores-ipsis /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. Testar
```bash
php tests/test-connection.php
```

### 8. Baixar Logo
```bash
cd public/assets/images
wget https://ipsis.com.br/caminho/logo.png -O logo.png
```

## Para Administradores

### Acessar Planilha
https://docs.google.com/spreadsheets/d/SEU_SHEET_ID

### Monitorar Logs
```bash
tail -f /var/log/nginx/fornecedores-ipsis-error.log
tail -f /var/www/fornecedores-ipsis/logs/error_$(date +%Y-%m-%d).log
```

### Backup
Exportar planilha regularmente do Google Sheets

## Para Usuários Finais

### Acessar Formulário
https://seu-dominio.com

### Preencher Cadastro
1. Dados da empresa
2. Contato
3. Tipo de serviço
4. Upload de documento
5. Assinatura digital
6. Enviar

### Confirmação
Email de confirmação será enviado automaticamente

## Troubleshooting Rápido

### Erro 500
```bash
sudo tail -f /var/log/nginx/fornecedores-ipsis-error.log
```

### Google Sheets não conecta
- Verificar se planilha foi compartilhada com Service Account
- Verificar credentials.json

### Upload não funciona
```bash
sudo chmod -R 775 /var/www/fornecedores-ipsis/uploads
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis/uploads
```

### Email não envia
- Usar senha de app do Gmail (não senha normal)
- Verificar porta 587 aberta

## Links Úteis

- **Documentação Completa**: README.md
- **Instalação Detalhada**: INSTALL.md
- **Deploy**: DEPLOY.md
- **Arquitetura**: ARCHITECTURE.md

## Suporte

Equipe de TI Ipsis
