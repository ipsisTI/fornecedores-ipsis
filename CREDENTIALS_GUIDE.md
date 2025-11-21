# Guia de Obtenção de Credenciais

Este guia detalha como obter todas as credenciais necessárias para o sistema.

## 1. Google Sheets API

### Passo 1: Criar Projeto no Google Cloud

1. Acesse: https://console.cloud.google.com/
2. Faça login com sua conta Google Workspace
3. Clique em "Selecionar projeto" no topo
4. Clique em "Novo Projeto"
5. Preencha:
   - **Nome do projeto:** Ipsis Fornecedores
   - **Organização:** Selecione sua organização
6. Clique em "Criar"
7. Aguarde a criação (alguns segundos)

### Passo 2: Ativar Google Sheets API

1. No menu lateral, vá em: **APIs e Serviços** > **Biblioteca**
2. Na busca, digite: "Google Sheets API"
3. Clique em "Google Sheets API"
4. Clique em "Ativar"
5. Aguarde a ativação

### Passo 3: Criar Service Account

1. No menu lateral, vá em: **APIs e Serviços** > **Credenciais**
2. Clique em "Criar Credenciais" no topo
3. Selecione "Conta de Serviço"
4. Preencha:
   - **Nome:** Ipsis Fornecedores Service
   - **ID:** ipsis-fornecedores-service (gerado automaticamente)
   - **Descrição:** Service Account para cadastro de fornecedores
5. Clique em "Criar e Continuar"
6. Em "Conceder acesso ao projeto":
   - **Papel:** Editor
7. Clique em "Continuar"
8. Clique em "Concluir"

### Passo 4: Baixar Credenciais JSON

1. Na lista de contas de serviço, clique na conta recém-criada
2. Vá na aba "Chaves"
3. Clique em "Adicionar Chave" > "Criar Nova Chave"
4. Selecione tipo: **JSON**
5. Clique em "Criar"
6. O arquivo será baixado automaticamente
7. Renomeie o arquivo para: `credentials.json`
8. **Guarde este arquivo em local seguro!**

### Passo 5: Criar Planilha Google Sheets

1. Acesse: https://sheets.google.com/
2. Clique em "+" para criar nova planilha
3. Renomeie para: "Fornecedores Ipsis"
4. Copie o ID da planilha da URL:
   ```
   https://docs.google.com/spreadsheets/d/[COPIE_ESTE_ID]/edit
   ```
5. Guarde este ID (você vai precisar no .env)

### Passo 6: Compartilhar Planilha com Service Account

1. Na planilha, clique em "Compartilhar" (canto superior direito)
2. Abra o arquivo `credentials.json` baixado
3. Procure o campo `client_email`, exemplo:
   ```json
   "client_email": "ipsis-fornecedores-service@projeto-123456.iam.gserviceaccount.com"
   ```
4. Copie este email completo
5. Cole no campo "Adicionar pessoas e grupos"
6. Selecione permissão: **Editor**
7. **IMPORTANTE:** Desmarque "Notificar pessoas"
8. Clique em "Compartilhar"

✅ **Google Sheets API configurado!**

---

## 2. Google reCAPTCHA v3

### Passo 1: Acessar Admin Console

1. Acesse: https://www.google.com/recaptcha/admin
2. Faça login com sua conta Google

### Passo 2: Registrar Novo Site

1. Clique no botão "+" no topo
2. Preencha o formulário:

**Rótulo:**
```
Ipsis Fornecedores
```

**Tipo de reCAPTCHA:**
- Selecione: ☑️ **reCAPTCHA v3**

**Domínios:**
```
seu-dominio.com
www.seu-dominio.com
```

**Proprietários:**
- Adicione emails dos administradores (opcional)

**Aceitar os Termos de Serviço do reCAPTCHA:**
- ☑️ Marque a caixa

3. Clique em "Enviar"

### Passo 3: Copiar Chaves

Após criar, você verá duas chaves:

**Chave do site (Site Key):**
```
6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
```
→ Esta vai no frontend (JavaScript)
→ Copie para o .env: `RECAPTCHA_SITE_KEY`

**Chave secreta (Secret Key):**
```
6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
```
→ Esta vai no backend (PHP)
→ Copie para o .env: `RECAPTCHA_SECRET_KEY`

✅ **reCAPTCHA configurado!**

---

## 3. Gmail SMTP (Envio de Emails)

### Passo 1: Ativar Verificação em 2 Etapas

1. Acesse: https://myaccount.google.com/security
2. Role até "Como fazer login no Google"
3. Clique em "Verificação em duas etapas"
4. Siga as instruções para ativar
5. Use seu telefone para receber códigos

### Passo 2: Gerar Senha de App

1. Após ativar 2FA, volte para: https://myaccount.google.com/security
2. Role até "Como fazer login no Google"
3. Clique em "Senhas de app"
4. Pode pedir para fazer login novamente
5. Em "Selecionar app":
   - Escolha: **Email**
6. Em "Selecionar dispositivo":
   - Escolha: **Outro (nome personalizado)**
   - Digite: **Ipsis Fornecedores**
7. Clique em "Gerar"
8. Uma senha de 16 caracteres será exibida:
   ```
   abcd efgh ijkl mnop
   ```
9. **Copie esta senha** (sem espaços)
10. Guarde em local seguro

### Passo 3: Configurar no .env

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=seu-email@ipsis.com.br
SMTP_PASS=abcdefghijklmnop
SMTP_FROM=noreply@ipsis.com.br
SMTP_FROM_NAME=Ipsis - Cadastro de Fornecedores
ADMIN_EMAIL=admin@ipsis.com.br
```

**Notas:**
- Use a senha de app (16 caracteres), NÃO a senha normal
- `SMTP_USER`: Email que vai enviar
- `SMTP_FROM`: Email que aparece como remetente
- `ADMIN_EMAIL`: Email que recebe notificações

✅ **Gmail SMTP configurado!**

---

## 4. Resumo das Credenciais

### Arquivo .env

```env
# Google Sheets
GOOGLE_SHEET_ID=1abc...xyz
GOOGLE_CREDENTIALS_PATH=src/config/credentials.json

# reCAPTCHA
RECAPTCHA_SITE_KEY=6LeIxAcTAAAAA...
RECAPTCHA_SECRET_KEY=6LeIxAcTAAAAA...

# Email
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=seu-email@ipsis.com.br
SMTP_PASS=abcdefghijklmnop
SMTP_FROM=noreply@ipsis.com.br
SMTP_FROM_NAME=Ipsis - Cadastro de Fornecedores
ADMIN_EMAIL=admin@ipsis.com.br

# App
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com
```

### Arquivo credentials.json

Fazer upload para: `src/config/credentials.json`

---

## 5. Checklist de Credenciais

Antes de fazer deploy, verifique:

- [ ] Projeto criado no Google Cloud
- [ ] Google Sheets API ativada
- [ ] Service Account criada
- [ ] credentials.json baixado
- [ ] Planilha criada no Google Sheets
- [ ] ID da planilha copiado
- [ ] Planilha compartilhada com Service Account
- [ ] reCAPTCHA v3 registrado
- [ ] Site Key copiada
- [ ] Secret Key copiada
- [ ] Verificação em 2 etapas ativada no Gmail
- [ ] Senha de app gerada
- [ ] Senha de app copiada
- [ ] Arquivo .env configurado
- [ ] credentials.json no servidor

---

## 6. Teste de Credenciais

Após configurar tudo, teste:

```bash
php tests/test-connection.php
```

Deve exibir:
```
✓ Todos os testes passaram!
Sistema pronto para uso.
```

---

## 7. Segurança das Credenciais

### ⚠️ IMPORTANTE

**NUNCA:**
- ❌ Commitar .env no Git
- ❌ Commitar credentials.json no Git
- ❌ Compartilhar senhas publicamente
- ❌ Usar senha normal do Gmail (use senha de app)
- ❌ Deixar arquivos com permissões abertas

**SEMPRE:**
- ✅ Usar .gitignore para .env e credentials.json
- ✅ Guardar backup das credenciais em local seguro
- ✅ Usar permissões 600 para arquivos sensíveis
- ✅ Usar HTTPS em produção
- ✅ Renovar senhas periodicamente

### Permissões Corretas

```bash
chmod 600 .env
chmod 600 src/config/credentials.json
```

---

## 8. Troubleshooting

### Google Sheets não conecta

**Erro:** "Error calling GET https://sheets.googleapis.com/..."

**Solução:**
1. Verificar se API está ativada
2. Verificar se credentials.json está correto
3. Verificar se planilha foi compartilhada com Service Account
4. Copiar email do credentials.json:
   ```bash
   cat src/config/credentials.json | grep client_email
   ```
5. Compartilhar planilha com esse email

### reCAPTCHA falha

**Erro:** "Falha na verificação reCAPTCHA"

**Solução:**
1. Verificar se domínio está registrado no reCAPTCHA Admin
2. Verificar se chaves estão corretas no .env
3. Verificar se está usando reCAPTCHA v3 (não v2)
4. Limpar cache do navegador

### Email não envia

**Erro:** "SMTP Error: Could not authenticate"

**Solução:**
1. Verificar se está usando senha de app (não senha normal)
2. Verificar se verificação em 2 etapas está ativa
3. Gerar nova senha de app
4. Verificar se porta 587 está aberta:
   ```bash
   telnet smtp.gmail.com 587
   ```

---

## 9. Contatos Úteis

- **Google Cloud Support:** https://cloud.google.com/support
- **reCAPTCHA Help:** https://support.google.com/recaptcha
- **Gmail Help:** https://support.google.com/mail

---

## 10. Backup de Credenciais

Recomendamos guardar backup das credenciais em:

1. **Gerenciador de senhas** (1Password, LastPass, etc.)
2. **Documento criptografado** (local seguro)
3. **Cofre da empresa** (físico ou digital)

**Informações para backup:**
- Google Cloud Project ID
- Service Account Email
- Planilha ID
- reCAPTCHA Site Key
- reCAPTCHA Secret Key
- Gmail senha de app
- Arquivo credentials.json

---

**Tempo estimado para obter todas as credenciais: 20-30 minutos**

**Dificuldade: Fácil (seguindo este guia)**

**Custo: Grátis (todas as APIs são gratuitas)**
