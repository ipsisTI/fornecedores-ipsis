# Instalação - Funcionalidade de PDF Assinado

## O que foi implementado

O sistema agora gera automaticamente um PDF do "Código de Relacionamento para Fornecedores" com a assinatura digital do fornecedor na última página.

## Como funciona

1. **Fornecedor visualiza o PDF** - O documento é exibido no formulário
2. **Aguarda 30 segundos** - Timer para garantir visualização
3. **Aceita os termos** - Checkbox é liberado após o timer
4. **Assina digitalmente** - Pode desenhar ou digitar a assinatura
5. **PDF é gerado** - Sistema cria uma cópia do PDF com a assinatura na última página
6. **Download disponível** - Link para baixar o PDF assinado aparece após o envio

## Instalação das Dependências

### Passo 1: Instalar Composer (se ainda não tiver)

Baixe e instale o Composer: https://getcomposer.org/download/

### Passo 2: Instalar as bibliotecas PHP

No terminal, na pasta do projeto, execute:

```bash
composer install
```

Ou se já tiver as dependências instaladas:

```bash
composer update
```

Isso vai instalar:
- `setasign/fpdf` - Biblioteca para criar PDFs
- `setasign/fpdi` - Biblioteca para importar e manipular PDFs existentes

### Passo 3: Verificar permissões

Certifique-se que a pasta `uploads/signed/` tem permissão de escrita:

**Windows:**
```bash
icacls uploads\signed /grant Users:F
```

**Linux/Mac:**
```bash
chmod -R 775 uploads/signed
chown -R www-data:www-data uploads/signed
```

## Estrutura de Arquivos

```
uploads/
├── signed/                          # PDFs assinados gerados
│   └── codigo_relacionamento_assinado_[CNPJ]_[TIMESTAMP].pdf
└── assinaturas/                     # Imagens das assinaturas
    └── assinatura_[ID]_[TIMESTAMP].png

doc/
└── Código de Relacionamento para Fornecedores de Bens e Serviços_2025.pdf

public/
├── doc/                             # Cópia do PDF para visualização
│   └── Código de Relacionamento para Fornecedores de Bens e Serviços_2025.pdf
└── download-pdf.php                 # Endpoint para download seguro

src/
└── services/
    └── PDFSignatureService.php      # Serviço de geração de PDF assinado
```

## Como o PDF é gerado

1. **Importa o PDF original** - Usa FPDI para ler todas as páginas
2. **Copia todas as páginas** - Mantém o documento original intacto
3. **Adiciona assinatura na última página**:
   - Se for assinatura desenhada: converte base64 para imagem PNG
   - Se for assinatura digitada: adiciona texto estilizado
4. **Adiciona informações do fornecedor**:
   - Razão Social
   - CNPJ
   - Data e hora da assinatura
5. **Salva com nome único** - `codigo_relacionamento_assinado_[CNPJ]_[TIMESTAMP].pdf`

## Exemplo de Assinatura no PDF

```
┌─────────────────────────────────────┐
│                                     │
│     [Assinatura Digital/Imagem]     │
│     ___________________________     │
│                                     │
│     Nome da Empresa Ltda            │
│     CNPJ: 12.345.678/0001-90       │
│     Data: 19/11/2025 16:45:30      │
│                                     │
└─────────────────────────────────────┘
```

## Limpeza Automática

O sistema inclui um método para limpar PDFs antigos:

```php
$pdfService = new PDFSignatureService();
$pdfService->limparPDFsAntigos(); // Remove PDFs com mais de 30 dias
```

Você pode configurar um cron job para executar isso periodicamente.

## Segurança

- ✅ Download apenas de arquivos PDF
- ✅ Sanitização do nome do arquivo
- ✅ Arquivos armazenados fora do diretório público
- ✅ Validação de extensão de arquivo
- ✅ Nomes únicos para evitar sobrescrita

## Troubleshooting

### Erro: "Class 'setasign\Fpdi\Fpdi' not found"

**Solução:** Execute `composer install` para instalar as dependências.

### Erro: "Permission denied" ao salvar PDF

**Solução:** Verifique as permissões da pasta `uploads/signed/`:
```bash
chmod -R 775 uploads/signed
```

### PDF não está sendo gerado

**Solução:** Verifique os logs de erro:
```bash
tail -f logs/error_*.log
```

### Assinatura não aparece no PDF

**Solução:** Verifique se a fonte está disponível. O sistema usa fallback para texto simples se houver erro.

## Próximos Passos

1. **Enviar PDF por email** - Anexar o PDF assinado no email de confirmação
2. **Armazenar no Google Drive** - Fazer upload automático para o Drive
3. **Adicionar QR Code** - Incluir QR Code no PDF para validação
4. **Certificado digital** - Integrar com certificado digital A1/A3

## Suporte

Para dúvidas sobre a implementação, consulte:
- Documentação FPDI: https://www.setasign.com/products/fpdi/
- Documentação FPDF: http://www.fpdf.org/
