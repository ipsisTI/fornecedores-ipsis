# Testes - Ipsis Fornecedores

## Scripts de Teste Disponíveis

### test-connection.php

Script para verificar se todas as configurações estão corretas antes de colocar o sistema em produção.

**Uso:**
```bash
php tests/test-connection.php
```

**O que testa:**
- ✓ Arquivo .env existe
- ✓ Configurações carregam corretamente
- ✓ Extensões PHP necessárias
- ✓ Arquivo credentials.json existe e é válido
- ✓ Conexão com Google Sheets
- ✓ Configurações de email
- ✓ Conexão SMTP
- ✓ Configurações reCAPTCHA
- ✓ Diretório de uploads
- ✓ Logo existe

**Saída esperada:**
```
=== TESTE DE CONFIGURAÇÃO - IPSIS FORNECEDORES ===

1. Verificando arquivo .env... OK
2. Carregando configurações... OK
3. Verificando extensões PHP...
   - curl: OK
   - mbstring: OK
   - zip: OK
   - gd: OK
4. Verificando credentials.json... OK
   JSON válido: OK
   Service Account: ipsis-fornecedores@project.iam.gserviceaccount.com
5. Testando conexão Google Sheets... OK
   Planilha ID: 1abc...xyz
   Link: https://docs.google.com/spreadsheets/d/1abc...xyz
6. Verificando configurações de email...
   SMTP Host: smtp.gmail.com
   SMTP Port: 587
   SMTP User: seu-email@ipsis.com.br
   From: noreply@ipsis.com.br
7. Testando conexão SMTP... OK
8. Verificando reCAPTCHA...
   Site Key: 6LeIxAcTAAAAAJcZVRq...
   Secret Key: 6LeIxAcTAAAAAGG-vFI1...
9. Verificando diretório de uploads... OK
10. Verificando logo... OK

=== RESUMO ===
✓ Todos os testes passaram!
Sistema pronto para uso.
```

## Testes Manuais

### Teste de Formulário

1. Acesse https://seu-dominio.com
2. Preencha todos os campos
3. Faça upload de um arquivo de teste
4. Desenhe ou digite uma assinatura
5. Envie o formulário
6. Verifique:
   - Mensagem de sucesso
   - Dados na planilha
   - Email de confirmação
   - Email de notificação admin

### Teste de Validação

**CNPJ Inválido:**
- Digite: 11.111.111/1111-11
- Esperado: Erro "CNPJ inválido"

**Email Inválido:**
- Digite: email-invalido
- Esperado: Erro "Email inválido"

**Arquivo Muito Grande:**
- Envie arquivo > 5MB
- Esperado: Erro "Arquivo muito grande"

**Tipo de Arquivo Inválido:**
- Envie arquivo .exe ou .zip
- Esperado: Erro "Tipo de arquivo não permitido"

**CNPJ Duplicado:**
- Envie mesmo CNPJ duas vezes
- Esperado: Erro "CNPJ já cadastrado"

**Sem Assinatura:**
- Não desenhe nem digite assinatura
- Esperado: Erro "Assinatura é obrigatória"

### Teste de Segurança

**HTTPS:**
```bash
curl -I http://seu-dominio.com
# Esperado: 301 redirect para https://
```

**Arquivos Sensíveis:**
```bash
curl https://seu-dominio.com/.env
# Esperado: 403 Forbidden

curl https://seu-dominio.com/src/config/credentials.json
# Esperado: 403 Forbidden
```

**CSRF:**
- Tente enviar formulário sem token CSRF
- Esperado: Erro 403

**reCAPTCHA:**
- Tente enviar formulário sem token reCAPTCHA
- Esperado: Erro de validação

## Testes de Performance

### Tempo de Carregamento

```bash
curl -o /dev/null -s -w "Time: %{time_total}s\n" https://seu-dominio.com
# Esperado: < 3 segundos
```

### Teste de Carga

```bash
# Instalar Apache Bench
sudo apt install apache2-utils

# Teste com 100 requisições, 10 concorrentes
ab -n 100 -c 10 https://seu-dominio.com/

# Analisar:
# - Requests per second
# - Time per request
# - Failed requests (deve ser 0)
```

## Testes Automatizados (Futuro)

### PHPUnit

Para implementar testes unitários no futuro:

```bash
composer require --dev phpunit/phpunit
```

Estrutura sugerida:
```
tests/
├── Unit/
│   ├── ValidationServiceTest.php
│   ├── GoogleSheetsServiceTest.php
│   └── EmailServiceTest.php
├── Integration/
│   └── FormHandlerTest.php
└── Feature/
    └── FormSubmissionTest.php
```

## Troubleshooting de Testes

### test-connection.php falha

**Erro: "Arquivo .env não encontrado"**
```bash
cp .env.example .env
nano .env
```

**Erro: "credentials.json não encontrado"**
```bash
# Fazer upload do arquivo
# Colocar em: src/config/credentials.json
chmod 600 src/config/credentials.json
```

**Erro: "Erro ao conectar com Google Sheets"**
```bash
# Verificar se planilha foi compartilhada
cat src/config/credentials.json | grep client_email
# Compartilhar planilha com esse email
```

**Erro: "Não foi possível conectar ao servidor SMTP"**
```bash
# Verificar firewall
sudo ufw status
sudo ufw allow 587/tcp

# Testar manualmente
telnet smtp.gmail.com 587
```

## Logs de Teste

Todos os testes devem ser documentados:

```
Data: ___/___/______
Responsável: _____________________
Ambiente: [ ] Desenvolvimento [ ] Produção

Testes Executados:
[ ] test-connection.php
[ ] Teste de formulário
[ ] Teste de validação
[ ] Teste de segurança
[ ] Teste de performance

Resultados:
[ ] Todos passaram
[ ] Alguns falharam (detalhar abaixo)

Observações:
_____________________________________________
_____________________________________________
```

## Contato

Para dúvidas sobre testes, entre em contato com a equipe de TI da Ipsis.
