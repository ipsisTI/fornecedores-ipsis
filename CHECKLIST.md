# Checklist de Deploy - Ipsis Fornecedores

## Pré-Deploy

### Servidor
- [ ] Ubuntu Server 18.04+ instalado
- [ ] Nginx instalado e rodando
- [ ] PHP 8.1+ instalado
- [ ] Extensões PHP instaladas (curl, mbstring, zip, gd)
- [ ] Composer instalado
- [ ] SSL/HTTPS configurado (Let's Encrypt)
- [ ] Domínio apontando para o servidor
- [ ] Acesso SSH configurado

### Google Cloud
- [ ] Projeto criado no Google Cloud Console
- [ ] Google Sheets API ativada
- [ ] Service Account criada
- [ ] Arquivo credentials.json baixado
- [ ] Planilha criada no Google Sheets
- [ ] Planilha compartilhada com Service Account (permissão Editor)
- [ ] ID da planilha copiado

### Google reCAPTCHA
- [ ] Site registrado no reCAPTCHA Admin
- [ ] Tipo: reCAPTCHA v3 selecionado
- [ ] Domínio adicionado
- [ ] Site Key copiada
- [ ] Secret Key copiada

### Email (Gmail)
- [ ] Conta Gmail configurada
- [ ] Verificação em 2 etapas ativada
- [ ] Senha de app gerada
- [ ] Senha de app copiada

## Deploy

### 1. Upload do Projeto
- [ ] Arquivos enviados para /var/www/fornecedores-ipsis/
- [ ] Estrutura de diretórios verificada

### 2. Dependências
- [ ] `composer install` executado
- [ ] Pasta vendor/ criada
- [ ] Sem erros de instalação

### 3. Configuração
- [ ] Arquivo .env criado (cp .env.example .env)
- [ ] GOOGLE_SHEET_ID preenchido
- [ ] RECAPTCHA_SITE_KEY preenchida
- [ ] RECAPTCHA_SECRET_KEY preenchida
- [ ] SMTP_HOST configurado
- [ ] SMTP_USER configurado
- [ ] SMTP_PASS configurado (senha de app)
- [ ] ADMIN_EMAIL configurado
- [ ] APP_URL configurado
- [ ] credentials.json em src/config/

### 4. Permissões
- [ ] Proprietário: www-data:www-data
- [ ] Diretórios: 755
- [ ] Arquivos: 644
- [ ] uploads/: 775
- [ ] .env: 600
- [ ] credentials.json: 600

### 5. Nginx
- [ ] Arquivo de configuração criado
- [ ] Domínio ajustado
- [ ] Caminhos SSL ajustados
- [ ] Link simbólico criado (sites-enabled)
- [ ] `nginx -t` sem erros
- [ ] Nginx recarregado

### 6. Assets
- [ ] Logo baixada e colocada em public/assets/images/logo.png
- [ ] Logo com permissões corretas (644)

## Testes

### Testes Técnicos
- [ ] `php tests/test-connection.php` executado
- [ ] Todos os testes passaram
- [ ] Conexão Google Sheets OK
- [ ] Conexão SMTP OK
- [ ] Arquivo credentials.json válido
- [ ] Diretório uploads/ com permissão de escrita

### Testes Funcionais
- [ ] Página carrega corretamente (https://seu-dominio.com)
- [ ] Logo aparece
- [ ] Formulário renderiza corretamente
- [ ] Máscaras funcionam (CNPJ, telefone)
- [ ] Canvas de assinatura funciona
- [ ] Assinatura digitada funciona
- [ ] Upload de arquivo funciona
- [ ] Validações client-side funcionam
- [ ] reCAPTCHA carrega

### Teste de Submissão
- [ ] Formulário enviado com dados de teste
- [ ] Sem erros no console do navegador
- [ ] Mensagem de sucesso exibida
- [ ] Dados aparecem na planilha Google Sheets
- [ ] Email de confirmação recebido (fornecedor)
- [ ] Email de notificação recebido (admin)
- [ ] Arquivo salvo em uploads/
- [ ] Assinatura salva em uploads/assinaturas/

### Testes de Validação
- [ ] Campos obrigatórios validados
- [ ] CNPJ inválido rejeitado
- [ ] Email inválido rejeitado
- [ ] Telefone inválido rejeitado
- [ ] Arquivo muito grande rejeitado
- [ ] Tipo de arquivo inválido rejeitado
- [ ] CNPJ duplicado rejeitado
- [ ] Assinatura obrigatória validada

### Testes de Segurança
- [ ] HTTPS funcionando
- [ ] HTTP redirecionando para HTTPS
- [ ] Arquivos sensíveis protegidos (.env, credentials.json)
- [ ] Diretórios sensíveis bloqueados (vendor/, src/)
- [ ] reCAPTCHA validando corretamente
- [ ] CSRF token validando
- [ ] Headers de segurança presentes

### Testes de Performance
- [ ] Página carrega em < 3 segundos
- [ ] Assets com cache configurado
- [ ] Gzip funcionando
- [ ] Sem erros 404

## Pós-Deploy

### Monitoramento
- [ ] Logs configurados
- [ ] Acesso aos logs verificado
- [ ] Sem erros nos logs iniciais

### Documentação
- [ ] README.md revisado
- [ ] Credenciais documentadas (local seguro)
- [ ] Equipe informada sobre novo sistema
- [ ] Processo de suporte definido

### Backup
- [ ] Planilha Google Sheets com backup automático
- [ ] Processo de backup de uploads/ definido
- [ ] Processo de backup de .env definido

### Comunicação
- [ ] Fornecedores informados sobre novo sistema
- [ ] Link do formulário divulgado
- [ ] Equipe treinada para gerenciar cadastros

## Manutenção Contínua

### Diário
- [ ] Verificar novos cadastros na planilha
- [ ] Responder fornecedores

### Semanal
- [ ] Revisar logs de erro
- [ ] Verificar espaço em disco (uploads/)
- [ ] Backup da planilha

### Mensal
- [ ] Limpar logs antigos (> 30 dias)
- [ ] Revisar uploads antigos
- [ ] Verificar certificado SSL (renovação automática)

### Trimestral
- [ ] Atualizar dependências (composer update)
- [ ] Revisar segurança
- [ ] Otimizar planilha (se necessário)

## Troubleshooting

### Se algo der errado:

1. **Verificar logs:**
   ```bash
   tail -f /var/log/nginx/fornecedores-ipsis-error.log
   tail -f /var/www/fornecedores-ipsis/logs/error_$(date +%Y-%m-%d).log
   ```

2. **Verificar permissões:**
   ```bash
   ls -la /var/www/fornecedores-ipsis
   ```

3. **Testar conexões:**
   ```bash
   php tests/test-connection.php
   ```

4. **Verificar Nginx:**
   ```bash
   sudo nginx -t
   sudo systemctl status nginx
   ```

5. **Verificar PHP-FPM:**
   ```bash
   sudo systemctl status php8.1-fpm
   ```

## Contatos de Emergência

- **Equipe TI Ipsis:** [email/telefone]
- **Suporte Google Cloud:** https://cloud.google.com/support
- **Suporte Nginx:** Documentação oficial
- **Suporte PHP:** Documentação oficial

---

**Data do Deploy:** ___/___/______

**Responsável:** _____________________

**Status:** [ ] Concluído [ ] Pendente [ ] Com problemas

**Observações:**
_____________________________________________
_____________________________________________
_____________________________________________
