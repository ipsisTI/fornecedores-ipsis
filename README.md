# Sistema de Cadastro de Fornecedores - Ipsis

Sistema web para cadastro e qualificação de fornecedores com assinatura digital e integração com Google Sheets.

## 📋 Funcionalidades

- Formulário completo de cadastro de fornecedores
- Validação de CNPJ e verificação de duplicidade
- Assinatura digital (canvas ou digitada)
- Upload de documentos
- Proteção contra spam (reCAPTCHA v3)
- Integração com Google Sheets API
- Design responsivo baseado no site ipsis.com.br

## 🚀 Tecnologias

- PHP 7.4+
- HTML5/CSS3/JavaScript
- Google Sheets API v4
- Google reCAPTCHA v3
- Nginx (servidor web)

## 📁 Estrutura do Projeto

```
fornecedores-ipsis/
├── public/
│   ├── index.php              # Página principal do formulário
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css      # Estilos customizados
│   │   ├── js/
│   │   │   └── form.js        # Validações e interações
│   │   └── images/
│   │       └── logo.png       # Logo da Ipsis
├── src/
│   ├── config/
│   │   └── config.php         # Configurações gerais
│   ├── services/
│   │   ├── GoogleSheetsService.php
│   │   └── ValidationService.php
│   ├── handlers/
│   │   └── FormHandler.php    # Processamento do formulário
│   └── utils/
│       └── helpers.php         # Funções auxiliares
├── uploads/                    # Diretório para arquivos enviados
├── vendor/                     # Dependências do Composer
├── .env.example               # Exemplo de variáveis de ambiente
├── .gitignore
├── composer.json
└── README.md
```

## ⚙️ Configuração

### 1. Requisitos do Servidor

```bash
# Instalar PHP e extensões necessárias
sudo apt update
sudo apt install php8.1-fpm php8.1-curl php8.1-mbstring php8.1-zip php8.1-gd

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Configurar Google Sheets API

1. Acesse [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um novo projeto ou selecione um existente
3. Ative a **Google Sheets API**
4. Crie credenciais (Service Account):
   - Vá em "Credenciais" > "Criar Credenciais" > "Conta de Serviço"
   - Baixe o arquivo JSON das credenciais
   - Salve como `credentials.json` na pasta `src/config/`
5. Crie uma planilha no Google Sheets
6. Compartilhe a planilha com o email da Service Account (permissão de editor)
7. Copie o ID da planilha da URL

### 3. Configurar Google reCAPTCHA

1. Acesse [Google reCAPTCHA](https://www.google.com/recaptcha/admin)
2. Registre um novo site (reCAPTCHA v3)
3. Adicione seu domínio
4. Copie as chaves (Site Key e Secret Key)

### 4. Configurar Variáveis de Ambiente

```bash
# Copiar arquivo de exemplo
cp .env.example .env

# Editar com suas credenciais
nano .env
```

### 5. Instalar Dependências

```bash
composer install
```

### 6. Configurar Permissões

```bash
# Dar permissão de escrita para uploads
sudo chown -R www-data:www-data uploads/
sudo chmod -R 755 uploads/
```

### 7. Configurar Nginx

```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /var/www/fornecedores-ipsis/public;
    index index.php;

    # Redirecionar para HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seu-dominio.com;
    root /var/www/fornecedores-ipsis/public;
    index index.php;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location /uploads {
        internal;
    }

    location ~ /\. {
        deny all;
    }
}
```

## 📊 Estrutura da Planilha Google Sheets

A planilha deve ter as seguintes colunas (Linha 1):

| A | B | C | D | E | F | G | H | I | J | K |
|---|---|---|---|---|---|---|---|---|---|---|
| Data/Hora | Razão Social | Nome Fantasia | CNPJ | Endereço | Telefone | Email | Tipo de Serviço | Documento | Assinatura | Status |

## 🔒 Segurança

- Validação de CNPJ no backend
- Sanitização de inputs
- Proteção contra SQL Injection (não usa SQL diretamente)
- Proteção contra XSS
- reCAPTCHA v3 para prevenir bots
- Validação de tipos de arquivo no upload
- Limite de tamanho de arquivo (5MB)
- HTTPS obrigatório em produção

## 🎨 Design

O design segue o padrão visual do site ipsis.com.br:
- Cores: Azul (#0066cc), Branco, Cinza
- Tipografia: Moderna e clean
- Layout responsivo (mobile-first)
- Logo oficial da Ipsis

## 📝 Uso

1. Acesse a página do formulário
2. Preencha todos os campos obrigatórios
3. Faça upload do documento
4. Assine digitalmente (desenhe ou digite)
5. Complete o reCAPTCHA
6. Envie o formulário
7. Receba confirmação na tela

## 🐛 Troubleshooting

### Erro ao conectar com Google Sheets
- Verifique se a API está ativada
- Confirme que o arquivo credentials.json está correto
- Verifique se a planilha foi compartilhada com a Service Account

### Upload de arquivos não funciona
- Verifique permissões da pasta uploads/
- Confirme o limite de upload no php.ini

## 📄 Licença

Propriedade da Ipsis - Todos os direitos reservados

## 👨‍💻 Suporte

Para dúvidas ou problemas, entre em contato com a equipe de TI da Ipsis.
