# Guia de Deploy para Produção

## ✅ O que já está configurado e funcionando:

1. **Formulário completo** - Validação, campos, assinatura digital
2. **Geração de PDF** - Com todas as páginas + página de assinatura
3. **Google Sheets** - Salvando dados automaticamente
4. **Google Drive** - Upload automático (Shared Drive)
5. **Página de sucesso** - Redirecionamento após envio

## 📋 Checklist para Deploy

### 1. Servidor Web

**Requisitos:**
- PHP 8.0 ou superior
- Nginx ou Apache
- Composer instalado
- SSL/HTTPS configurado

**Instalar dependências:**
```bash
composer install --no-dev --optimize-autoloader
```

### 2. Configurar .env

Edite o arquivo `.env` com os dados de produção:

```env
# Google Sheets
GOOGLE_SHEET_ID=seu_sheet_id_real
GOOGLE_CREDENTIALS_PATH=src/config/credentials.json

# Google Drive
GOOGLE_DRIVE_FOLDER_ID=seu_drive_folder_id_real

# Google reCAPTCHA v3
RECAPTCHA_SITE_KEY=sua_site_key_real
RECAPTCHA_SECRET_KEY=sua_secret_key_real

# Email (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=seu-email@ipsis.com.br
SMTP_PASS=sua_senha_de_app
SMTP_FROM=noreply@ipsis.com.br
SMTP_FROM_NAME=Ipsis - Cadastro de Fornecedores
ADMIN_EMAIL=admin@ipsis.com.br

# Aplicação
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com.br

# Upload
MAX_FILE_SIZE=5242880
ALLOWED_FILE_TYPES=pdf,jpg,jpeg,png,doc,docx

# Segurança
SESSION_LIFETIME=120
```

### 3. Arquivos Necessários

**Copiar para o servidor:**
```
fornecedores-ipsis/
├── public/              # Document root do servidor
├── src/
├── vendor/              # Após rodar composer install
├── uploads/
│   └── signed/         # Criar com permissão 775
├── doc/
│   └── Código de Relacionamento...pdf
├── .env                # Configurado com dados reais
└── composer.json
```

**NÃO enviar:**
- `.git/`
- `node_modules/`
- `tests/`
- Arquivos de teste (`test-*.php`)

### 4. Permissões

```bash
# Proprietário
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis

# Permissões gerais
sudo find /var/www/fornecedores-ipsis -type d -exec chmod 755 {} \;
sudo find /var/www/fornecedores-ipsis -type f -exec chmod 644 {} \;

# Permissão especial para uploads
sudo chmod -R 775 /var/www/fornecedores-ipsis/uploads
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis/uploads

# Proteger arquivos sensíveis
sudo chmod 600 /var/www/fornecedores-ipsis/.env
sudo chmod 600 /var/www/fornecedores-ipsis/src/config/credentials.json
```

### 5. Configurar Nginx

Arquivo: `/etc/nginx/sites-available/fornecedores-ipsis`

```nginx
server {
    listen 80;
    server_name seu-dominio.com.br www.seu-dominio.com.br;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seu-dominio.com.br www.seu-dominio.com.br;
    
    root /var/www/fornecedores-ipsis/public;
    index index.php;
    
    # SSL
    ssl_certificate /etc/letsencrypt/live/seu-dominio.com.br/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/seu-dominio.com.br/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    # Logs
    access_log /var/log/nginx/fornecedores-access.log;
    error_log /var/log/nginx/fornecedores-error.log;
    
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
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }
    
    # Proteger arquivos sensíveis
    location ~ /\. {
        deny all;
    }
    
    location ~ /(vendor|src|tests)/ {
        deny all;
    }
    
    # Cache de assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

**Ativar site:**
```bash
sudo ln -s /etc/nginx/sites-available/fornecedores-ipsis /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 6. SSL/HTTPS (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d seu-dominio.com.br -d www.seu-dominio.com.br
```

### 7. Configurar Email (Gmail)

1. Acesse: https://myaccount.google.com/security
2. Ative "Verificação em duas etapas"
3. Vá em "Senhas de app"
4. Crie uma senha para "Email" > "Outro" > "Ipsis Fornecedores"
5. Use essa senha no `.env` (campo `SMTP_PASS`)

### 8. Testar em Produção

1. Acesse: https://seu-dominio.com.br
2. Preencha o formulário de teste
3. Verifique:
   - ✅ PDF gerado corretamente
   - ✅ Dados salvos no Google Sheets
   - ✅ PDF enviado para Google Drive
   - ✅ Redirecionamento para página de sucesso
   - ✅ Download do PDF funciona

### 9. Monitoramento

**Logs de erro:**
```bash
tail -f /var/log/nginx/fornecedores-error.log
```

**Logs do PHP:**
```bash
tail -f /var/log/php8.0-fpm.log
```

### 10. Backup

**Backup automático da planilha:**
- Configure exportação automática no Google Sheets
- Ou use Google Takeout periodicamente

**Backup dos PDFs:**
```bash
# Criar backup semanal
0 0 * * 0 tar -czf /backup/pdfs-$(date +\%Y\%m\%d).tar.gz /var/www/fornecedores-ipsis/uploads/signed/
```

## 🔒 Segurança

- ✅ HTTPS obrigatório
- ✅ Arquivos sensíveis protegidos (`.env`, `credentials.json`)
- ✅ Validação de entrada no backend
- ✅ reCAPTCHA v3 ativo
- ✅ CSRF token
- ✅ Sanitização de dados

## 📊 Manutenção

**Limpar PDFs antigos (opcional):**
```bash
# Manter apenas últimos 90 dias
find /var/www/fornecedores-ipsis/uploads/signed/ -name "*.pdf" -mtime +90 -delete
```

**Atualizar dependências:**
```bash
composer update
```

## 🆘 Troubleshooting

### Erro 500
```bash
sudo tail -f /var/log/nginx/fornecedores-error.log
```

### PDF não gera
- Verificar permissões da pasta `uploads/signed/`
- Verificar se o PDF original existe em `public/doc/`

### Google Sheets não salva
- Verificar se `credentials.json` está correto
- Verificar se planilha foi compartilhada com Service Account

### Google Drive não faz upload
- Verificar se Drive Compartilhado foi criado
- Verificar se Service Account foi adicionada como membro

## ✅ Checklist Final

- [ ] Composer install executado
- [ ] .env configurado com dados reais
- [ ] credentials.json no lugar correto
- [ ] Permissões configuradas
- [ ] Nginx configurado e testado
- [ ] SSL/HTTPS funcionando
- [ ] Google Sheets testado
- [ ] Google Drive testado
- [ ] Email SMTP testado
- [ ] Formulário testado end-to-end
- [ ] Página de sucesso funcionando
- [ ] Download de PDF funcionando

## 🎉 Pronto!

Seu sistema está pronto para produção. Todos os cadastros serão:
1. Salvos automaticamente no Google Sheets
2. PDFs enviados para o Google Drive
3. Disponíveis para download imediato
4. Com assinatura digital integrada

---

**Suporte:** Em caso de dúvidas, consulte os arquivos:
- `CONFIGURAR_GOOGLE_SHEETS.md`
- `CONFIGURAR_GOOGLE_DRIVE.md`
- `INSTALL.md`
