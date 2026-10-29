# Como Configurar Google Drive API

## Passo 1: Ativar Google Drive API

1. Acesse: https://console.cloud.google.com/
2. Selecione o projeto **"Ipsis Fornecedores"** (o mesmo que você criou antes)
3. No menu lateral, vá em **"APIs e Serviços"** > **"Biblioteca"**
4. Busque por **"Google Drive API"**
5. Clique na API
6. Clique em **"Ativar"**

## Passo 2: Atualizar Permissões da Service Account

A mesma Service Account que você criou antes já tem acesso. Não precisa criar outra!

## Passo 3: Criar Drive Compartilhado (Shared Drive)

### Opção A: Usar Drive Compartilhado (Recomendado para Google Workspace)

1. Acesse: https://drive.google.com/
2. No menu lateral esquerdo, clique em **"Drives compartilhados"**
3. Clique em **"Novo"** (botão + no canto superior esquerdo)
4. Nome: **"Fornecedores Ipsis"**
5. Clique em **"Criar"**

### Opção B: Usar Pasta Normal (Para contas pessoais)

1. Acesse: https://drive.google.com/
2. Clique em **"Novo"** > **"Nova pasta"**
3. Nome da pasta: **"PDFs Assinados - Fornecedores Ipsis"**
4. Clique em **"Criar"**

## Passo 4: Adicionar Service Account ao Drive

### Se usar Drive Compartilhado:

1. Abra o Drive Compartilhado criado
2. Clique no ícone de **"Gerenciar membros"** (ícone de pessoa no topo)
3. Clique em **"Adicionar membros"**
4. Cole o email da Service Account:
   - Abra `src/config/credentials.json`
   - Copie o valor do campo `client_email`
   - Exemplo: `ipsis-fornecedores-service@projeto-123456.iam.gserviceaccount.com`
5. Selecione permissão: **"Gerente de conteúdo"** ou **"Colaborador"**
6. **DESMARQUE** "Notificar pessoas"
7. Clique em **"Enviar"**

### Se usar Pasta Normal:

1. Clique com botão direito na pasta criada
2. Clique em **"Compartilhar"**
3. Cole o email da Service Account
4. Selecione permissão: **"Editor"**
5. **DESMARQUE** "Notificar pessoas"
6. Clique em **"Compartilhar"**

## Passo 5: Copiar ID do Drive/Pasta

### Se usar Drive Compartilhado:

1. Abra o Drive Compartilhado
2. Na URL, copie o ID:
   ```
   https://drive.google.com/drive/folders/[ESTE_É_O_ID]
   ```
   Exemplo: `0AI785jSYztkWUk9PVA`

### Se usar Pasta Normal:

1. Abra a pasta no Google Drive
2. Na URL, copie o ID da pasta:
   ```
   https://drive.google.com/drive/folders/[ESTE_É_O_ID]
   ```
   Exemplo: `1dyUEebJaFnWa3Z4n0BFMVAXQ7mfUH6Xw`

3. Abra o arquivo `.env` na raiz do projeto
4. Adicione uma nova linha:
   ```
   GOOGLE_DRIVE_FOLDER_ID=cole_o_id_aqui
   ```

## Estrutura Final do .env

```env
# Google Sheets
GOOGLE_SHEET_ID=seu_sheet_id_aqui

# Google Drive
GOOGLE_DRIVE_FOLDER_ID=seu_folder_id_aqui

# Credenciais (mesmo arquivo para ambos)
GOOGLE_CREDENTIALS_PATH=src/config/credentials.json
```

## Como Funciona

Depois de configurar, o sistema vai:

1. ✅ Gerar o PDF com assinatura
2. ✅ Fazer upload do PDF para a pasta do Google Drive
3. ✅ Salvar o link na planilha (como hyperlink clicável) — o acesso ao PDF é herdado da pasta, sem link público
4. ✅ Permitir que você acesse todos os PDFs organizados no Drive

## Vantagens

- 📁 Todos os PDFs organizados em uma pasta
- 🔗 Links clicáveis na planilha
- ☁️ Backup automático no Google Drive
- 🔒 Controle de acesso centralizado
- 📊 Fácil de compartilhar com equipe

---

**Pronto para configurar?** Siga os passos acima e me avise quando terminar!

## Alternativa: pasta comum (Meu Drive) em vez de Shared Drive

Se a empresa não usa Google Workspace (ou você prefere não criar um Drive Compartilhado), é possível usar uma pasta comum no Meu Drive:

1. **Crie a pasta no Meu Drive**: acesse https://drive.google.com/, clique em **"Novo"** > **"Nova pasta"** e dê um nome (ex.: "PDFs Assinados - Fornecedores Ipsis").
2. **Compartilhe com a service account**: abra `src/config/credentials.json`, copie o valor do campo `client_email` e compartilhe a pasta com esse e-mail usando o papel de **Editor** (desmarque "Notificar pessoas").
3. **Copie o ID da pasta**: abra a pasta no Drive e copie o trecho da URL após `/folders/`:
   ```
   https://drive.google.com/drive/folders/[ESTE_É_O_ID]
   ```
   Coloque esse ID em `GOOGLE_DRIVE_FOLDER_ID` do `.env`. O código funciona igual — o parâmetro `supportsAllDrives` usado no upload não interfere em pastas comuns.

**Observações:**

- Os arquivos enviados passam a ter a **service account como proprietária**.
- O **acesso aos PDFs é herdado da pasta**: para que a equipe do RH veja os documentos, compartilhe a pasta com eles.
- A **cota de armazenamento** passa a ser da conta proprietária da pasta.
- Para retenção institucional (arquivos que não pertencem a uma única pessoa), o **Shared Drive continua sendo o recomendado**.
