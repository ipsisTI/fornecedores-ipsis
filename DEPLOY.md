# Guia Rápido de Deploy

## Checklist Pré-Deploy

- [ ] Servidor Ubuntu com Nginx configurado
- [ ] PHP 8.1+ instalado
- [ ] Composer instalado
- [ ] SSL/HTTPS configurado
- [ ] Domínio apontando para o servidor
- [ ] Acesso SSH ao servidor

## Deploy Rápido (5 passos)

### 1. Upload dos Arquivos

```bash
# Via SCP (do seu computador local)
scp -r fornecedores-ipsis/* usuario@servidor:/var/www/fornecedores-ipsis/

# Ou via Git
ssh usuario@servidor
cd /var/www
git clone seu-repositorio fornecedores-ipsis
```

### 2. Instalar Dependências

```bash
cd /var/www/fornecedores-ipsis
composer install --no-dev --optimize-autoloader
```

### 3. Configurar Credenciais

```bash
# Copiar .env
cp .env.example .env
nano .env

# Upload credentials.json do Google
# Coloque em: src/config/credentials.json
```

**Preencher no .env:**
- GOOGLE_SHEET_ID (da URL da planilha)
- RECAPTCHA_SITE_KEY e RECAPTCHA_SECRET_KEY
- Credenciais SMTP (Gmail)
- ADMIN_EMAIL

### 4. Configurar Permissões

```bash
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis
sudo chmod -R 755 /var/www/fornecedores-ipsis
sudo chmod -R 775 /var/www/fornecedores-ipsis/uploads
sudo chmod 600 /var/www/fornecedores-ipsis/.env
sudo chmod 600 /var/www/fornecedores-ipsis/src/config/credentials.json
```

### 5. Configurar Nginx

```bash
# Copiar configuração
sudo nano /etc/nginx/sites-available/fornecedores-ipsis

# Ativar site
sudo ln -s /etc/nginx/sites-available/fornecedores-ipsis /etc/nginx/sites-enabled/

# Testar e recarregar
sudo nginx -t
sudo systemctl reload nginx
```

## Configurações Externas Necessárias

### Google Cloud Console

1. **Criar projeto** em https://console.cloud.google.com/
2. **Ativar Google Sheets API**
3. **Criar Service Account** e baixar credentials.json
4. **Criar planilha** no Google Sheets
5. **Compartilhar planilha** com email da Service Account (permissão Editor)

### Google reCAPTCHA

1. Registrar site em https://www.google.com/recaptcha/admin
2. Tipo: reCAPTCHA v3
3. Copiar Site Key e Secret Key

### Gmail (SMTP)

1. Ativar verificação em 2 etapas
2. Criar senha de app em https://myaccount.google.com/apppasswords
3. Usar senha de app no .env

## Baixar Logo

```bash
cd /var/www/fornecedores-ipsis/public/assets/images

# Opção 1: Download direto
wget https://ipsis.com.br/caminho/logo.png -O logo.png

# Opção 2: Upload manual via SCP
# scp logo.png usuario@servidor:/var/www/fornecedores-ipsis/public/assets/images/
```

## Teste Final

1. Acesse https://seu-dominio.com
2. Preencha formulário de teste
3. Verifique dados na planilha
4. Confirme recebimento de emails

## Comandos Úteis

```bash
# Ver logs de erro
tail -f /var/log/nginx/fornecedores-ipsis-error.log
tail -f /var/www/fornecedores-ipsis/logs/error_$(date +%Y-%m-%d).log

# Reiniciar serviços
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm

# Verificar status
sudo systemctl status nginx
sudo systemctl status php8.1-fpm

# Testar PHP
php -v
php -m | grep curl

# Verificar permissões
ls -la /var/www/fornecedores-ipsis/uploads
```

## Problemas Comuns

**Erro 500:** Verificar logs e permissões
**Upload falha:** Verificar permissões da pasta uploads/
**Email não envia:** Verificar credenciais SMTP e senha de app
**Google Sheets erro:** Verificar se planilha foi compartilhada com Service Account

## Segurança

- ✅ HTTPS obrigatório
- ✅ Arquivos sensíveis protegidos (.env, credentials.json)
- ✅ Diretórios vendor/ e src/ bloqueados no Nginx
- ✅ reCAPTCHA v3 ativo
- ✅ Validação de CNPJ e duplicidade
- ✅ Sanitização de inputs
- ✅ CSRF protection

## Próximos Passos

1. Testar formulário em produção
2. Monitorar logs por 24h
3. Configurar backup da planilha
4. Documentar processo interno
5. Treinar equipe

---

**Tempo estimado de deploy:** 30-45 minutos

**Documentação completa:** Ver INSTALL.md
