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
3. ✅ Criar um link público para o PDF
4. ✅ Salvar o link na planilha (como hyperlink clicável)
5. ✅ Permitir que você acesse todos os PDFs organizados no Drive

## Vantagens

- 📁 Todos os PDFs organizados em uma pasta
- 🔗 Links clicáveis na planilha
- ☁️ Backup automático no Google Drive
- 🔒 Controle de acesso centralizado
- 📊 Fácil de compartilhar com equipe

---

**Pronto para configurar?** Siga os passos acima e me avise quando terminar!
