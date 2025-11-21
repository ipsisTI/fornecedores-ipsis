# Como Configurar Google Sheets API

## Passo 1: Criar Projeto no Google Cloud Console

1. Acesse: https://console.cloud.google.com/
2. Clique em **"Selecionar projeto"** no topo
3. Clique em **"Novo Projeto"**
4. Nome do projeto: **"Ipsis Fornecedores"**
5. Clique em **"Criar"**

## Passo 2: Ativar a Google Sheets API

1. No menu lateral, vá em **"APIs e Serviços"** > **"Biblioteca"**
2. Busque por **"Google Sheets API"**
3. Clique na API
4. Clique em **"Ativar"**

## Passo 3: Criar Service Account (Conta de Serviço)

1. No menu lateral, vá em **"APIs e Serviços"** > **"Credenciais"**
2. Clique em **"Criar Credenciais"** > **"Conta de Serviço"**
3. Preencha:
   - **Nome**: `ipsis-fornecedores-service`
   - **ID**: (será preenchido automaticamente)
   - **Descrição**: `Service account para cadastro de fornecedores`
4. Clique em **"Criar e Continuar"**
5. Em **"Conceder acesso ao projeto"**, selecione o papel: **"Editor"**
6. Clique em **"Concluir"**

## Passo 4: Baixar Credenciais JSON

1. Na lista de **"Contas de Serviço"**, clique na conta que você acabou de criar
2. Vá na aba **"Chaves"**
3. Clique em **"Adicionar Chave"** > **"Criar Nova Chave"**
4. Selecione o tipo **"JSON"**
5. Clique em **"Criar"**
6. O arquivo JSON será baixado automaticamente
7. **Renomeie o arquivo para**: `credentials.json`
8. **Mova o arquivo para**: `src/config/credentials.json`

## Passo 5: Criar Planilha no Google Sheets

1. Acesse: https://sheets.google.com/
2. Clique em **"Em branco"** para criar nova planilha
3. Nomeie a planilha: **"Cadastro de Fornecedores Ipsis"**
4. Na primeira linha (cabeçalho), adicione as seguintes colunas:

| A | B | C | D | E | F | G |
|---|---|---|---|---|---|---|
| Data/Hora | Razão Social | CNPJ | Telefone | Email | Tipo de Serviço | PDF Assinado |

## Passo 6: Compartilhar Planilha com Service Account

1. Na planilha, clique em **"Compartilhar"** (canto superior direito)
2. Abra o arquivo `src/config/credentials.json` que você baixou
3. Procure o campo **"client_email"** (algo como: `ipsis-fornecedores-service@projeto-123456.iam.gserviceaccount.com`)
4. **Copie esse email**
5. Cole no campo de compartilhamento da planilha
6. Selecione permissão: **"Editor"**
7. **DESMARQUE** a opção "Notificar pessoas"
8. Clique em **"Compartilhar"**

## Passo 7: Copiar ID da Planilha

1. Na URL da planilha, copie o ID:
   ```
   https://docs.google.com/spreadsheets/d/[ESTE_É_O_ID]/edit
   ```
   Exemplo: `1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms`

2. Abra o arquivo `.env` na raiz do projeto
3. Cole o ID no campo:
   ```
   GOOGLE_SHEET_ID=cole_o_id_aqui
   ```

## Passo 8: Testar Conexão

Execute o arquivo de teste:

```bash
php tests/test-connection.php
```

Se tudo estiver correto, você verá:
```
✓ Conexão com Google Sheets estabelecida!
✓ Planilha encontrada: Cadastro de Fornecedores Ipsis
```

## Estrutura Final

```
src/
└── config/
    └── credentials.json  ← Arquivo baixado do Google Cloud

.env
GOOGLE_SHEET_ID=seu_id_aqui  ← ID copiado da URL
```

## Troubleshooting

### Erro: "The caller does not have permission"
- Verifique se você compartilhou a planilha com o email correto da Service Account
- Verifique se a permissão é "Editor"

### Erro: "Unable to parse response"
- Verifique se o arquivo credentials.json está no local correto
- Verifique se o arquivo não está corrompido

### Erro: "Requested entity was not found"
- Verifique se o GOOGLE_SHEET_ID no .env está correto
- Verifique se você copiou o ID completo da URL

## Próximos Passos

Depois de configurar, o sistema vai:
1. ✅ Salvar cada cadastro automaticamente na planilha
2. ✅ Incluir data/hora, dados da empresa e link do PDF
3. ✅ Permitir que você acompanhe todos os cadastros em tempo real

---

**Precisa de ajuda?** Me avise em qual passo você está com dúvida!
