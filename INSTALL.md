# Guia de Instalação - Sistema de Cadastro de Fornecedores Ipsis

## Pré-requisitos

- Ubuntu Server (18.04 ou superior)
- Nginx instalado
- PHP 7.4 ou superior
- Composer
- Acesso SSH ao servidor
- Domínio configurado com SSL

## Passo 1: Preparar o Servidor

```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar PHP e extensões
sudo apt install -y php8.1-fpm php8.1-curl php8.1-mbstring php8.1-zip php8.1-gd php8.1-xml

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verificar instalação
php -v
composer --version
```

## Passo 2: Fazer Upload do Projeto

```bash
# Conectar via SSH
ssh usuario@seu-servidor

# Criar diretório do projeto
sudo mkdir -p /var/www/fornecedores-ipsis
sudo chown -R $USER:$USER /var/www/fornecedores-ipsis

# Fazer upload dos arquivos (do seu computador local)
# Opção 1: Via SCP
scp -r /caminho/local/fornecedores-ipsis/* usuario@seu-servidor:/var/www/fornecedores-ipsis/

# Opção 2: Via Git
cd /var/www/fornecedores-ipsis
git clone seu-repositorio .
```

## Passo 3: Instalar Dependências

```bash
cd /var/www/fornecedores-ipsis
composer install --no-dev --optimize-autoloader
```

## Passo 4: Configurar Google Sheets API

### 4.1 Criar Projeto no Google Cloud

1. Acesse: https://console.cloud.google.com/
2. Crie um novo projeto: "Ipsis Fornecedores"
3. Ative a **Google Sheets API**:
   - Menu > APIs e Serviços > Biblioteca
   - Busque "Google Sheets API"
   - Clique em "Ativar"

### 4.2 Criar Service Account

1. Menu > APIs e Serviços > Credenciais
2. Clique em "Criar Credenciais" > "Conta de Serviço"
3. Preencha:
   - Nome: "Ipsis Fornecedores Service"
   - ID: ipsis-fornecedores-service
   - Clique em "Criar e Continuar"
4. Conceder papel: "Editor"
5. Clique em "Concluir"

### 4.3 Baixar Credenciais

1. Na lista de contas de serviço, clique na conta criada
2. Vá em "Chaves" > "Adicionar Chave" > "Criar Nova Chave"
3. Selecione "JSON"
4. Baixe o arquivo
5. Renomeie para `credentials.json`
6. Faça upload para: `/var/www/fornecedores-ipsis/src/config/credentials.json`

```bash
# Definir permissões
chmod 600 /var/www/fornecedores-ipsis/src/config/credentials.json
```

### 4.4 Criar e Configurar Planilha

1. Acesse: https://sheets.google.com/
2. Crie uma nova planilha: "Fornecedores Ipsis"
3. Copie o ID da planilha da URL:
   ```
   https://docs.google.com/spreadsheets/d/[ESTE_É_O_ID]/edit
   ```
4. Compartilhe a planilha:
   - Clique em "Compartilhar"
   - Cole o email da Service Account (está no arquivo credentials.json, campo "client_email")
   - Conceda permissão de "Editor"
   - Desmarque "Notificar pessoas"
   - Clique em "Compartilhar"

## Passo 5: Configurar Google reCAPTCHA

1. Acesse: https://www.google.com/recaptcha/admin
2. Clique em "+" para registrar novo site
3. Preencha:
   - Rótulo: "Ipsis Fornecedores"
   - Tipo: reCAPTCHA v3
   - Domínios: seu-dominio.com
4. Aceite os termos
5. Clique em "Enviar"
6. Copie as chaves:
   - Chave do site (Site Key)
   - Chave secreta (Secret Key)

## Passo 6: Configurar Variáveis de Ambiente

```bash
cd /var/www/fornecedores-ipsis

# Copiar arquivo de exemplo
cp .env.example .env

# Editar arquivo
nano .env
```

Preencha com suas credenciais:

```env
# Google Sheets
GOOGLE_SHEET_ID=seu_id_da_planilha_aqui
GOOGLE_CREDENTIALS_PATH=src/config/credentials.json

# reCAPTCHA
RECAPTCHA_SITE_KEY=sua_site_key_aqui
RECAPTCHA_SECRET_KEY=sua_secret_key_aqui

# Aplicação
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com

# Upload
MAX_FILE_SIZE=5242880
ALLOWED_FILE_TYPES=pdf,jpg,jpeg,png,doc,docx

# Segurança
SESSION_LIFETIME=120
```

## Passo 7: Configurar Permissões

```bash
# Definir proprietário
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis

# Permissões de diretórios
sudo find /var/www/fornecedores-ipsis -type d -exec chmod 755 {} \;

# Permissões de arquivos
sudo find /var/www/fornecedores-ipsis -type f -exec chmod 644 {} \;

# Permissão especial para uploads
sudo chmod -R 775 /var/www/fornecedores-ipsis/uploads
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis/uploads

# Proteger arquivos sensíveis
sudo chmod 600 /var/www/fornecedores-ipsis/.env
sudo chmod 600 /var/www/fornecedores-ipsis/src/config/credentials.json
```

## Passo 8: Configurar Nginx

```bash
# Criar arquivo de configuração
sudo nano /etc/nginx/sites-available/fornecedores-ipsis
```

Cole a configuração:

```nginx
server {
    listen 80;
    server_name seu-dominio.com www.seu-dominio.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seu-dominio.com www.seu-dominio.com;
    
    root /var/www/fornecedores-ipsis/public;
    index index.php index.html;
    
    # SSL (ajuste os caminhos dos certificados)
    ssl_certificate /etc/letsencrypt/live/seu-dominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/seu-dominio.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Logs
    access_log /var/log/nginx/fornecedores-ipsis-access.log;
    error_log /var/log/nginx/fornecedores-ipsis-error.log;
    
    # Segurança
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Upload size
    client_max_body_size 10M;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }
    
    # Proteger arquivos sensíveis
    location ~ /\. {
        deny all;
    }
    
    location ~ /(vendor|src|logs)/ {
        deny all;
    }
    
    location /uploads {
        internal;
    }
    
    # Cache de assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Ativar site:

```bash
# Criar link simbólico
sudo ln -s /etc/nginx/sites-available/fornecedores-ipsis /etc/nginx/sites-enabled/

# Testar configuração
sudo nginx -t

# Recarregar Nginx
sudo systemctl reload nginx
```

## Passo 9: Baixar Logo da Ipsis

```bash
# Criar diretório de imagens
mkdir -p /var/www/fornecedores-ipsis/public/assets/images

# Baixar logo do site (ajuste a URL se necessário)
cd /var/www/fornecedores-ipsis/public/assets/images
wget https://ipsis.com.br/wp-content/uploads/2021/03/logo-ipsis.png -O logo.png

# Ou faça upload manual da logo
# Defina permissões
sudo chown www-data:www-data logo.png
sudo chmod 644 logo.png
```

## Passo 10: Testar a Instalação

1. Acesse: https://seu-dominio.com
2. Verifique se a página carrega corretamente
3. Teste o formulário com dados fictícios
4. Verifique se os dados aparecem na planilha
5. Confirme recebimento dos emails

## Troubleshooting

### Erro 500 - Internal Server Error

```bash
# Verificar logs
sudo tail -f /var/log/nginx/fornecedores-ipsis-error.log
sudo tail -f /var/log/php8.1-fpm.log

# Verificar permissões
ls -la /var/www/fornecedores-ipsis
```

### Erro ao conectar com Google Sheets

```bash
# Verificar se o arquivo existe
ls -la /var/www/fornecedores-ipsis/src/config/credentials.json

# Verificar se a planilha foi compartilhada
# Confirme o email da Service Account no arquivo credentials.json
cat /var/www/fornecedores-ipsis/src/config/credentials.json | grep client_email
```

### Upload de arquivos não funciona

```bash
# Verificar permissões
sudo chmod -R 775 /var/www/fornecedores-ipsis/uploads
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis/uploads

# Verificar limite de upload no PHP
sudo nano /etc/php/8.1/fpm/php.ini
# Procure e ajuste:
# upload_max_filesize = 10M
# post_max_size = 10M

# Reiniciar PHP-FPM
sudo systemctl restart php8.1-fpm
```

## Manutenção

### Backup da Planilha

Configure backup automático no Google Drive ou exporte periodicamente.

### Logs

```bash
# Ver logs de erro
tail -f /var/www/fornecedores-ipsis/logs/error_$(date +%Y-%m-%d).log

# Limpar logs antigos (manter últimos 30 dias)
find /var/www/fornecedores-ipsis/logs -name "*.log" -mtime +30 -delete
```

### Atualizar Dependências

```bash
cd /var/www/fornecedores-ipsis
composer update
```

## Suporte

Para problemas ou dúvidas, entre em contato com a equipe de TI da Ipsis.
