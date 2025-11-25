# Guia de Manutenção - Ipsis Fornecedores

## Tarefas de Rotina

### Diárias

#### Verificar Novos Cadastros
```bash
# Acessar planilha
https://docs.google.com/spreadsheets/d/SEU_SHEET_ID
```

#### Monitorar Logs de Erro
```bash
# Ver últimos erros
tail -n 50 /var/log/nginx/fornecedores-ipsis-error.log
tail -n 50 /var/www/fornecedores-ipsis/logs/error_$(date +%Y-%m-%d).log
```

### Semanais

#### Backup da Planilha
1. Acessar Google Sheets
2. Arquivo > Fazer download > Excel (.xlsx)
3. Salvar em local seguro

#### Verificar Espaço em Disco
```bash
df -h
du -sh /var/www/fornecedores-ipsis/uploads/
```

#### Revisar Cadastros Pendentes
- Verificar coluna "Status" na planilha
- Aprovar ou rejeitar fornecedores
- Entrar em contato se necessário

### Mensais

#### Limpar Logs Antigos
```bash
# Manter últimos 30 dias
find /var/www/fornecedores-ipsis/logs -name "*.log" -mtime +30 -delete
```

#### Revisar Uploads
```bash
# Listar arquivos grandes
find /var/www/fornecedores-ipsis/uploads -type f -size +5M -ls

# Verificar espaço total
du -sh /var/www/fornecedores-ipsis/uploads/
```

#### Verificar Certificado SSL
```bash
# Ver data de expiração
sudo certbot certificates
```

### Trimestrais

#### Atualizar Dependências
```bash
cd /var/www/fornecedores-ipsis
composer update
composer audit
```

#### Revisar Segurança
- Verificar atualizações de PHP
- Verificar atualizações de Nginx
- Revisar logs de acesso suspeito
- Testar validações de segurança

#### Otimizar Planilha
- Arquivar cadastros antigos (se necessário)
- Criar nova aba para novo período
- Manter planilha organizada

### Anuais

#### Auditoria Completa
- Revisar todos os processos
- Atualizar documentação
- Treinar equipe
- Revisar políticas de retenção de dados

## Comandos Úteis

### Logs

```bash
# Ver logs em tempo real
tail -f /var/log/nginx/fornecedores-ipsis-error.log

# Buscar erros específicos
grep "error" /var/log/nginx/fornecedores-ipsis-error.log

# Contar erros por dia
grep "$(date +%Y-%m-%d)" /var/log/nginx/fornecedores-ipsis-error.log | wc -l

# Ver logs da aplicação
tail -f /var/www/fornecedores-ipsis/logs/error_$(date +%Y-%m-%d).log
```

### Serviços

```bash
# Status dos serviços
sudo systemctl status nginx
sudo systemctl status php8.1-fpm

# Reiniciar serviços
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm

# Recarregar configuração (sem downtime)
sudo systemctl reload nginx
```

### Permissões

```bash
# Verificar permissões
ls -la /var/www/fornecedores-ipsis/

# Corrigir permissões
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis
sudo chmod -R 755 /var/www/fornecedores-ipsis
sudo chmod -R 775 /var/www/fornecedores-ipsis/uploads
```

### Backup

```bash
# Backup completo (exceto uploads)
tar -czf backup-$(date +%Y%m%d).tar.gz \
  --exclude='uploads' \
  --exclude='vendor' \
  --exclude='logs' \
  /var/www/fornecedores-ipsis

# Backup apenas uploads
tar -czf uploads-backup-$(date +%Y%m%d).tar.gz \
  /var/www/fornecedores-ipsis/uploads
```

## Problemas Comuns

### Erro 500 - Internal Server Error

**Causa:** Erro no código PHP ou configuração

**Solução:**
```bash
# Ver logs
tail -f /var/log/nginx/fornecedores-ipsis-error.log

# Verificar permissões
ls -la /var/www/fornecedores-ipsis

# Verificar PHP
php -v
sudo systemctl status php8.1-fpm
```

### Erro 502 - Bad Gateway

**Causa:** PHP-FPM não está rodando

**Solução:**
```bash
sudo systemctl status php8.1-fpm
sudo systemctl restart php8.1-fpm
```

### Erro 504 - Gateway Timeout

**Causa:** Script PHP demorando muito

**Solução:**
```bash
# Aumentar timeout no Nginx
sudo nano /etc/nginx/sites-available/fornecedores-ipsis
# Adicionar: fastcgi_read_timeout 300;

sudo systemctl reload nginx
```

### Upload de Arquivo Falha

**Causa:** Permissões ou limite de tamanho

**Solução:**
```bash
# Verificar permissões
sudo chmod -R 775 /var/www/fornecedores-ipsis/uploads
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis/uploads

# Verificar limite PHP
sudo nano /etc/php/8.1/fpm/php.ini
# Ajustar: upload_max_filesize = 10M
# Ajustar: post_max_size = 10M

sudo systemctl restart php8.1-fpm
```

### Google Sheets Não Conecta

**Causa:** Credenciais inválidas ou planilha não compartilhada

**Solução:**
```bash
# Verificar arquivo
cat /var/www/fornecedores-ipsis/src/config/credentials.json | grep client_email

# Copiar email da Service Account
# Compartilhar planilha com esse email (permissão Editor)

# Testar conexão
php /var/www/fornecedores-ipsis/tests/test-connection.php
```

### CNPJ Duplicado Não Detecta

**Causa:** Erro na leitura da planilha

**Solução:**
```bash
# Verificar logs
tail -f /var/www/fornecedores-ipsis/logs/error_$(date +%Y-%m-%d).log

# Testar conexão Google Sheets
php /var/www/fornecedores-ipsis/tests/test-connection.php
```

### reCAPTCHA Falha

**Causa:** Chaves incorretas ou domínio não autorizado

**Solução:**
```bash
# Verificar chaves no .env
cat /var/www/fornecedores-ipsis/.env | grep RECAPTCHA

# Verificar domínio no reCAPTCHA Admin
# https://www.google.com/recaptcha/admin
```

## Monitoramento

### Métricas Importantes

1. **Taxa de Submissão**
   - Quantos cadastros por dia/semana
   - Tendências de crescimento

2. **Taxa de Erro**
   - Erros 500/502/504
   - Erros de validação

3. **Tempo de Resposta**
   - Tempo de carregamento da página
   - Tempo de submissão do formulário

4. **Score reCAPTCHA**
   - Média dos scores
   - Detecção de bots

5. **Espaço em Disco**
   - Tamanho da pasta uploads/
   - Crescimento mensal

### Alertas Recomendados

- Erro 500 > 10 por hora
- Espaço em disco > 80%
- Certificado SSL expira em < 30 dias
- Taxa de erro > 5%

## Atualizações

### Atualizar PHP

```bash
# Verificar versão atual
php -v

# Atualizar (exemplo para 8.2)
sudo apt update
sudo apt install php8.2-fpm php8.2-curl php8.2-mbstring php8.2-zip php8.2-gd

# Atualizar configuração Nginx
sudo nano /etc/nginx/sites-available/fornecedores-ipsis
# Mudar: fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;

sudo systemctl reload nginx
```

### Atualizar Dependências

```bash
cd /var/www/fornecedores-ipsis

# Ver dependências desatualizadas
composer outdated

# Atualizar
composer update

# Verificar vulnerabilidades
composer audit
```

### Atualizar Nginx

```bash
# Verificar versão
nginx -v

# Atualizar
sudo apt update
sudo apt upgrade nginx

# Testar configuração
sudo nginx -t

# Recarregar
sudo systemctl reload nginx
```

## Backup e Restore

### Backup Completo

```bash
#!/bin/bash
# Script de backup

BACKUP_DIR="/backup/fornecedores-ipsis"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup código
tar -czf $BACKUP_DIR/code-$DATE.tar.gz \
  --exclude='vendor' \
  --exclude='logs' \
  --exclude='uploads' \
  /var/www/fornecedores-ipsis

# Backup uploads
tar -czf $BACKUP_DIR/uploads-$DATE.tar.gz \
  /var/www/fornecedores-ipsis/uploads

# Backup .env
cp /var/www/fornecedores-ipsis/.env $BACKUP_DIR/env-$DATE

# Backup credentials
cp /var/www/fornecedores-ipsis/src/config/credentials.json $BACKUP_DIR/credentials-$DATE.json

# Manter últimos 30 dias
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup concluído: $DATE"
```

### Restore

```bash
# Restaurar código
tar -xzf code-YYYYMMDD_HHMMSS.tar.gz -C /

# Restaurar uploads
tar -xzf uploads-YYYYMMDD_HHMMSS.tar.gz -C /

# Restaurar .env
cp env-YYYYMMDD_HHMMSS /var/www/fornecedores-ipsis/.env

# Restaurar credentials
cp credentials-YYYYMMDD_HHMMSS.json /var/www/fornecedores-ipsis/src/config/credentials.json

# Corrigir permissões
sudo chown -R www-data:www-data /var/www/fornecedores-ipsis
sudo chmod -R 755 /var/www/fornecedores-ipsis
sudo chmod -R 775 /var/www/fornecedores-ipsis/uploads

# Reinstalar dependências
cd /var/www/fornecedores-ipsis
composer install

# Reiniciar serviços
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
```

## Contato

Para dúvidas sobre manutenção, entre em contato com a equipe de TI da Ipsis.
