# Logo da Ipsis

## Como obter a logo

### Opção 1: Download do site oficial

```bash
cd public/assets/images
wget https://ipsis.com.br/wp-content/uploads/2021/03/logo-ipsis.png -O logo.png
```

### Opção 2: Inspecionar o site

1. Acesse https://ipsis.com.br
2. Clique com botão direito na logo
3. Selecione "Inspecionar" ou "Inspecionar elemento"
4. Encontre a tag `<img>` da logo
5. Copie a URL da imagem (atributo `src`)
6. Baixe a imagem

### Opção 3: Upload manual

1. Salve a logo do site da Ipsis
2. Renomeie para `logo.png`
3. Faça upload para esta pasta: `public/assets/images/logo.png`

## Formato recomendado

- **Formato:** PNG (com transparência)
- **Tamanho:** Largura máxima 200px
- **Nome:** logo.png

## Permissões (no servidor)

```bash
sudo chown www-data:www-data logo.png
sudo chmod 644 logo.png
```

## Nota

A logo será exibida no cabeçalho do formulário de cadastro de fornecedores.
