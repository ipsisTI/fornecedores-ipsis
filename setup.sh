#!/bin/bash

# Script de Setup Automático - Ipsis Fornecedores
# Execute: bash setup.sh

set -e

echo "=========================================="
echo "  Setup - Cadastro de Fornecedores Ipsis"
echo "=========================================="
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar se está rodando como root
if [ "$EUID" -eq 0 ]; then 
    echo -e "${RED}Não execute este script como root!${NC}"
    echo "Execute como usuário normal: bash setup.sh"
    exit 1
fi

# 1. Verificar PHP
echo -e "${YELLOW}[1/8]${NC} Verificando PHP..."
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    echo -e "${GREEN}✓${NC} PHP $PHP_VERSION instalado"
else
    echo -e "${RED}✗${NC} PHP não encontrado"
    echo "Instale com: sudo apt install php8.1-fpm php8.1-curl php8.1-mbstring php8.1-zip php8.1-gd"
    exit 1
fi

# 2. Verificar Composer
echo -e "${YELLOW}[2/8]${NC} Verificando Composer..."
if command -v composer &> /dev/null; then
    echo -e "${GREEN}✓${NC} Composer instalado"
else
    echo -e "${RED}✗${NC} Composer não encontrado"
    echo "Instale com: curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer"
    exit 1
fi

# 3. Instalar dependências
echo -e "${YELLOW}[3/8]${NC} Instalando dependências..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✓${NC} Dependências instaladas"
else
    echo -e "${RED}✗${NC} composer.json não encontrado"
    exit 1
fi

# 4. Criar arquivo .env
echo -e "${YELLOW}[4/8]${NC} Configurando .env..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${GREEN}✓${NC} Arquivo .env criado"
    echo -e "${YELLOW}⚠${NC}  IMPORTANTE: Edite o arquivo .env com suas credenciais!"
    echo "   nano .env"
else
    echo -e "${YELLOW}⚠${NC}  Arquivo .env já existe"
fi

# 5. Criar diretórios necessários
echo -e "${YELLOW}[5/8]${NC} Criando diretórios..."
mkdir -p uploads/assinaturas
mkdir -p logs
mkdir -p public/assets/images
echo -e "${GREEN}✓${NC} Diretórios criados"

# 6. Configurar permissões
echo -e "${YELLOW}[6/8]${NC} Configurando permissões..."
chmod -R 755 .
chmod -R 775 uploads
chmod -R 775 logs
if [ -f ".env" ]; then
    chmod 600 .env
fi
if [ -f "src/config/credentials.json" ]; then
    chmod 600 src/config/credentials.json
fi
echo -e "${GREEN}✓${NC} Permissões configuradas"

# 7. Verificar Nginx
echo -e "${YELLOW}[7/8]${NC} Verificando Nginx..."
if command -v nginx &> /dev/null; then
    echo -e "${GREEN}✓${NC} Nginx instalado"
    echo -e "${YELLOW}⚠${NC}  Configure o Nginx:"
    echo "   sudo cp nginx.conf.example /etc/nginx/sites-available/fornecedores-ipsis"
    echo "   sudo nano /etc/nginx/sites-available/fornecedores-ipsis"
    echo "   sudo ln -s /etc/nginx/sites-available/fornecedores-ipsis /etc/nginx/sites-enabled/"
    echo "   sudo nginx -t"
    echo "   sudo systemctl reload nginx"
else
    echo -e "${YELLOW}⚠${NC}  Nginx não encontrado"
fi

# 8. Resumo
echo ""
echo -e "${YELLOW}[8/8]${NC} Resumo do Setup"
echo "=========================================="
echo ""
echo -e "${GREEN}✓${NC} Setup básico concluído!"
echo ""
echo "Próximos passos:"
echo ""
echo "1. Editar .env com suas credenciais:"
echo "   nano .env"
echo ""
echo "2. Fazer upload do credentials.json:"
echo "   Coloque em: src/config/credentials.json"
echo ""
echo "3. Baixar logo da Ipsis:"
echo "   cd public/assets/images"
echo "   wget https://ipsis.com.br/caminho/logo.png -O logo.png"
echo ""
echo "4. Configurar Nginx (ver acima)"
echo ""
echo "5. Testar conexões:"
echo "   php tests/test-connection.php"
echo ""
echo "6. Acessar: https://seu-dominio.com"
echo ""
echo "=========================================="
echo ""
echo "Documentação:"
echo "  - README.md (visão geral)"
echo "  - INSTALL.md (instalação detalhada)"
echo "  - DEPLOY.md (deploy rápido)"
echo "  - QUICK_START.md (guia rápido)"
echo ""
echo "Suporte: Equipe TI Ipsis"
echo ""
