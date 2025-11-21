<?php
$pdfFile = $_GET['pdf'] ?? '';
$pdfFile = basename($pdfFile); // Segurança
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Realizado - Ipsis</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Ipsis Logo" class="logo">
            </div>
            <h1>Cadastro Realizado com Sucesso!</h1>
        </header>

        <main class="main-content">
            <div class="success-container">
                <div class="success-icon">✓</div>
                <h2>Seu cadastro foi enviado com sucesso!</h2>
                <p>Recebemos suas informações e o termo de aceite assinado.</p>
                
                <?php if (!empty($pdfFile)): ?>
                <div class="success-actions">
                    <a href="download-pdf.php?file=<?php echo urlencode($pdfFile); ?>" class="btn-primary" download>
                        📄 Baixar Código de Relacionamento Assinado
                    </a>
                    
                    <a href="index.php" class="btn-secondary">
                        ← Enviar Novo Cadastro
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="success-info">
                    <p><strong>Próximos passos:</strong></p>
                    <ul>
                        <li>Você receberá um email de confirmação em breve</li>
                        <li>Nossa equipe analisará seu cadastro</li>
                        <li>Entraremos em contato caso necessário</li>
                    </ul>
                </div>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Ipsis. Todos os direitos reservados.</p>
            <p><a href="https://ipsis.com.br" target="_blank">www.ipsis.com.br</a></p>
        </footer>
    </div>
</body>
</html>
