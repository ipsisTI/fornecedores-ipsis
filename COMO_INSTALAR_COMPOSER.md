# Como Instalar o Composer no Windows

## Passo 1: Baixar o Composer

1. Acesse: https://getcomposer.org/download/
2. Clique em "Composer-Setup.exe" (Windows Installer)
3. Execute o instalador

## Passo 2: Instalar

1. O instalador vai detectar o PHP automaticamente
2. Se não detectar, aponte para o `php.exe` (geralmente em `C:\php\php.exe`)
3. Clique em "Next" até finalizar
4. Reinicie o terminal/prompt de comando

## Passo 3: Verificar Instalação

Abra um novo terminal e digite:

```bash
composer --version
```

Deve mostrar algo como: `Composer version 2.x.x`

## Passo 4: Instalar Dependências do Projeto

Na pasta do projeto, execute:

```bash
composer install
```

Isso vai instalar:
- Google API Client (para Google Sheets)
- PHPMailer (para envio de emails)
- FPDF e FPDI (para gerar PDFs assinados)
- DotEnv (para variáveis de ambiente)

## Passo 5: Voltar para o FormHandler Real

Depois de instalar, edite o arquivo `public/assets/js/form.js` e mude:

```javascript
// De:
const response = await fetch('test-form.php', {

// Para:
const response = await fetch('process-form.php', {
```

## Passo 6: Testar Novamente

Agora o sistema vai:
1. ✅ Validar os dados
2. ✅ Gerar o PDF com a assinatura
3. ✅ Salvar no Google Sheets (se configurado)
4. ✅ Enviar emails (se configurado)
5. ✅ Retornar o link do PDF assinado real

---

## Alternativa: Testar Apenas a Geração de PDF

Se quiser testar só a parte do PDF sem configurar Google Sheets e Email, posso criar uma versão simplificada do FormHandler que só gera o PDF.

Quer que eu faça isso?
