<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/utils/helpers.php';

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Fornecedores - Ipsis</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo RECAPTCHA_SITE_KEY; ?>"></script>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="Ipsis Logo" class="logo">
            </div>
            <h1>Cadastro de Fornecedores</h1>
            <p class="subtitle">Preencha o formulário abaixo para se cadastrar como fornecedor da Ipsis</p>
        </header>

        <main class="main-content">
            <form id="fornecedorForm" class="form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="recaptcha_token" id="recaptchaToken">
                <input type="hidden" name="assinatura_tipo" id="assinaturaTipo" value="canvas">
                <input type="hidden" name="assinatura" id="assinatura">
                
                <!-- Dados da Empresa -->
                <section class="form-section">
                    <h2 class="section-title">Dados da Empresa</h2>
                    
                    <div class="form-group">
                        <label for="razao_social" class="required">Razão Social</label>
                        <input type="text" id="razao_social" name="razao_social" required>
                        <span class="error-message" id="error-razao_social"></span>
                    </div>

                    <div class="form-group">
                        <label for="cnpj" class="required">CNPJ</label>
                        <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" required>
                        <span class="error-message" id="error-cnpj"></span>
                    </div>
                </section>

                <!-- Contato -->
                <section class="form-section">
                    <h2 class="section-title">Contato</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefone" class="required">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                            <span class="error-message" id="error-telefone"></span>
                        </div>

                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" required>
                            <span class="error-message" id="error-email"></span>
                        </div>
                    </div>
                </section>

                <!-- Serviços -->
                <section class="form-section">
                    <h2 class="section-title">Serviços</h2>
                    
                    <div class="form-group">
                        <label for="tipo_servico" class="required">Tipo de Serviço Prestado</label>
                        <select id="tipo_servico" name="tipo_servico" required>
                            <option value="">Selecione...</option>
                            <option value="Consultoria">Consultoria</option>
                            <option value="Desenvolvimento">Desenvolvimento</option>
                            <option value="Suporte Técnico">Suporte Técnico</option>
                            <option value="Infraestrutura">Infraestrutura</option>
                            <option value="Treinamento">Treinamento</option>
                            <option value="Manutenção">Manutenção</option>
                            <option value="Outros">Outros</option>
                        </select>
                        <span class="error-message" id="error-tipo_servico"></span>
                    </div>

                    <div class="form-group">
                        <label for="documento" class="required">Documento (PDF, DOC, DOCX, JPG, PNG - Máx: 5MB)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="documento" name="documento" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            <label for="documento" class="file-input-label">
                                <span class="file-input-text">Escolher arquivo</span>
                                <span class="file-input-name" id="fileName">Nenhum arquivo selecionado</span>
                            </label>
                        </div>
                        <span class="error-message" id="error-documento"></span>
                    </div>
                </section>

                <!-- Código de Relacionamento -->
                <section class="form-section">
                    <h2 class="section-title">Código de Relacionamento para Fornecedores</h2>
                    <p class="section-description">Por favor, leia o documento completo abaixo. Role até o final para habilitar a assinatura.</p>
                    
                    <div class="pdf-viewer-container">
                        <iframe 
                            id="pdfViewer" 
                            src="doc/Código de Relacionamento para Fornecedores de Bens e Serviços_2025.pdf"
                            type="application/pdf"
                            class="pdf-iframe"
                        ></iframe>
                        <div class="pdf-mobile-fallback">
                            <p>📱 Visualizando no celular?</p>
                            <a href="doc/Código de Relacionamento para Fornecedores de Bens e Serviços_2025.pdf" target="_blank" class="btn-open-pdf">
                                📄 Abrir Documento em Nova Aba
                            </a>
                        </div>
                        <div class="pdf-scroll-indicator" id="scrollIndicator">
                            <span>📄 Visualize o documento acima</span>
                        </div>
                    </div>
                    
                    <div class="pdf-confirm-container" id="pdfConfirmContainer">
                        <button type="button" class="btn-confirm-read" id="btnConfirmRead">
                            ✓ Li todo o documento e desejo continuar
                        </button>
                    </div>
                    
                    <div class="form-group" id="termosGroup" style="display: none;">
                        <label class="checkbox-container">
                            <input type="checkbox" id="termos_aceitos" name="termos_aceitos" required>
                            <span class="checkmark"></span>
                            <span class="checkbox-label">Li e concordo com o Código de Relacionamento para Fornecedores</span>
                        </label>
                        <span class="error-message" id="error-termos_aceitos"></span>
                    </div>
                </section>

                <!-- Assinatura Digital -->
                <section class="form-section">
                    <h2 class="section-title">Assinatura Digital</h2>
                    
                    <div class="signature-tabs">
                        <button type="button" class="tab-button active" data-tab="canvas">Desenhar</button>
                        <button type="button" class="tab-button" data-tab="typed">Digitar</button>
                    </div>

                    <div class="signature-content">
                        <!-- Canvas para desenhar -->
                        <div class="signature-tab active" id="tab-canvas">
                            <canvas id="signatureCanvas" width="600" height="200"></canvas>
                            <button type="button" class="btn-secondary" id="clearCanvas">Limpar</button>
                        </div>

                        <!-- Input para digitar -->
                        <div class="signature-tab" id="tab-typed">
                            <input type="text" id="signatureTyped" placeholder="Digite seu nome completo">
                            <div class="signature-preview" id="signaturePreview"></div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="assinatura" id="assinatura">
                    <span class="error-message" id="error-assinatura"></span>
                </section>

                <!-- Botões -->
                <div class="form-actions">
                    <button type="submit" class="btn-primary" id="submitBtn">
                        <span class="btn-text">Enviar Cadastro</span>
                        <span class="btn-loader" style="display: none;">Enviando...</span>
                    </button>
                </div>
            </form>

            <!-- Mensagem de sucesso/erro -->
            <div id="formMessage" class="form-message" style="display: none;"></div>
        </main>

        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Ipsis. Todos os direitos reservados.</p>
            <p><a href="https://ipsis.com.br" target="_blank">www.ipsis.com.br</a></p>
        </footer>
    </div>

    <script>
        const RECAPTCHA_SITE_KEY = '<?php echo RECAPTCHA_SITE_KEY; ?>';
    </script>
    <script src="assets/js/form.js"></script>
</body>
</html>
