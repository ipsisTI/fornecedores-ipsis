/**
 * Form Handler - Cadastro de Fornecedores Ipsis
 */

// Estado da aplicação
const state = {
    isDrawing: false,
    signatureType: 'canvas',
    canvas: null,
    ctx: null
};

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    initializeCanvas();
    initializeMasks();
    initializeFileInput();
    initializeSignatureTabs();
    initializeForm();
});

/**
 * Inicializa canvas de assinatura
 */
function initializeCanvas() {
    state.canvas = document.getElementById('signatureCanvas');
    state.ctx = state.canvas.getContext('2d');
    
    // Configurar canvas
    state.ctx.strokeStyle = '#0066cc';
    state.ctx.lineWidth = 2;
    state.ctx.lineCap = 'round';
    state.ctx.lineJoin = 'round';
    
    // Eventos de desenho
    state.canvas.addEventListener('mousedown', startDrawing);
    state.canvas.addEventListener('mousemove', draw);
    state.canvas.addEventListener('mouseup', stopDrawing);
    state.canvas.addEventListener('mouseout', stopDrawing);
    
    // Eventos touch para mobile
    state.canvas.addEventListener('touchstart', handleTouchStart);
    state.canvas.addEventListener('touchmove', handleTouchMove);
    state.canvas.addEventListener('touchend', stopDrawing);
    
    // Botão limpar
    document.getElementById('clearCanvas').addEventListener('click', clearCanvas);
}

/**
 * Inicializa máscaras de input
 */
function initializeMasks() {
    // Máscara CNPJ
    const cnpjInput = document.getElementById('cnpj');
    cnpjInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
        e.target.value = value;
    });
    
    // Máscara Telefone
    const telefoneInput = document.getElementById('telefone');
    telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 10) {
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        }
        e.target.value = value;
    });
}

/**
 * Inicializa input de arquivo
 */
function initializeFileInput() {
    const fileInput = document.getElementById('documento');
    const fileName = document.getElementById('fileName');
    
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            const size = (file.size / 1024 / 1024).toFixed(2);
            fileName.textContent = `${file.name} (${size} MB)`;
        } else {
            fileName.textContent = 'Nenhum arquivo selecionado';
        }
    });
}

/**
 * Inicializa tabs de assinatura
 */
function initializeSignatureTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const signatureTyped = document.getElementById('signatureTyped');
    const signaturePreview = document.getElementById('signaturePreview');
    const assinaturaTipoInput = document.getElementById('assinaturaTipo');
    
    // Definir tipo inicial como canvas
    state.signatureType = 'canvas';
    assinaturaTipoInput.value = 'canvas';
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Atualizar botões
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Atualizar conteúdo
            document.querySelectorAll('.signature-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(`tab-${tabName}`).classList.add('active');
            
            // Atualizar tipo
            state.signatureType = tabName;
            assinaturaTipoInput.value = tabName;
        });
    });
    
    // Preview da assinatura digitada
    signatureTyped.addEventListener('input', function(e) {
        signaturePreview.textContent = e.target.value || '';
    });
}

/**
 * Inicializa formulário
 */
function initializeForm() {
    const form = document.getElementById('fornecedorForm');
    
    // Remover obrigatoriedade do campo de documento (temporariamente oculto)
    const documentoInput = document.getElementById('documento');
    if (documentoInput) {
        documentoInput.removeAttribute('required');
    }
    
    // Inicializar visualizador de PDF
    initializePDFViewer();
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('Formulário submetido');
        
        // Limpar erros anteriores
        clearErrors();
        
        // Validar termos
        const termosAceitos = document.getElementById('termos_aceitos');
        console.log('Termos aceitos:', termosAceitos.checked);
        if (!termosAceitos.checked) {
            showError('termos_aceitos', 'Você deve ler e aceitar o Código de Relacionamento');
            return;
        }
        
        // Validar assinatura
        console.log('Validando assinatura...');
        if (!validateSignature()) {
            showError('assinatura', 'Assinatura é obrigatória');
            return;
        }
        console.log('Assinatura válida');
        
        // Obter token reCAPTCHA
        console.log('Obtendo token reCAPTCHA...');
        try {
            const token = await getRecaptchaToken();
            document.getElementById('recaptchaToken').value = token;
        } catch (error) {
            showMessage('Erro ao validar reCAPTCHA. Tente novamente.', 'error');
            return;
        }
        
        // Enviar formulário
        await submitForm(form);
    });
}

/**
 * Inicializa visualizador de PDF
 */
function initializePDFViewer() {
    const btnConfirmRead = document.getElementById('btnConfirmRead');
    const termosGroup = document.getElementById('termosGroup');
    const pdfConfirmContainer = document.getElementById('pdfConfirmContainer');
    const scrollIndicator = document.getElementById('scrollIndicator');
    
    // Quando o usuário clicar no botão confirmando que leu
    btnConfirmRead.addEventListener('click', function() {
        // Esconder botão de confirmação
        pdfConfirmContainer.style.display = 'none';
        
        // Mostrar checkbox de aceite
        termosGroup.style.display = 'block';
        
        // Atualizar indicador
        scrollIndicator.innerHTML = '<span style="color: #28a745;">✓ Documento visualizado - aceite os termos abaixo</span>';
        
        // Scroll suave até o checkbox
        termosGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

/**
 * Funções de desenho no canvas
 */
function startDrawing(e) {
    state.isDrawing = true;
    const rect = state.canvas.getBoundingClientRect();
    state.ctx.beginPath();
    state.ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
}

function draw(e) {
    if (!state.isDrawing) return;
    const rect = state.canvas.getBoundingClientRect();
    state.ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
    state.ctx.stroke();
}

function stopDrawing() {
    state.isDrawing = false;
}

function handleTouchStart(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousedown', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    state.canvas.dispatchEvent(mouseEvent);
}

function handleTouchMove(e) {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousemove', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    state.canvas.dispatchEvent(mouseEvent);
}

function clearCanvas() {
    state.ctx.clearRect(0, 0, state.canvas.width, state.canvas.height);
}

/**
 * Valida assinatura
 */
function validateSignature() {
    const assinaturaInput = document.getElementById('assinatura');
    
    if (state.signatureType === 'canvas') {
        // Verificar se canvas não está vazio
        const imageData = state.ctx.getImageData(0, 0, state.canvas.width, state.canvas.height);
        const isEmpty = !imageData.data.some(channel => channel !== 0);
        
        if (isEmpty) {
            return false;
        }
        
        // Salvar como base64
        const base64Data = state.canvas.toDataURL('image/png');
        assinaturaInput.value = base64Data;
        console.log('Assinatura canvas salva, tamanho:', base64Data.length);
    } else {
        // Assinatura digitada
        const typedSignature = document.getElementById('signatureTyped').value.trim();
        
        if (!typedSignature) {
            return false;
        }
        
        assinaturaInput.value = typedSignature;
        console.log('Assinatura digitada salva:', typedSignature);
    }
    
    console.log('Valor final do campo assinatura:', assinaturaInput.value.substring(0, 50));
    return true;
}

/**
 * Obtém token reCAPTCHA
 */
function getRecaptchaToken() {
    return new Promise((resolve, reject) => {
        grecaptcha.ready(function() {
            grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'submit' })
                .then(resolve)
                .catch(reject);
        });
    });
}

/**
 * Envia formulário
 */
async function submitForm(form) {
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    
    // Desabilitar botão
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoader.style.display = 'inline-block';
    
    try {
        const formData = new FormData(form);
        
        // Garantir que a assinatura seja enviada
        const assinaturaValue = document.getElementById('assinatura').value;
        const assinaturaTipoValue = document.getElementById('assinaturaTipo').value;
        
        // Remover e adicionar novamente para garantir
        formData.delete('assinatura');
        formData.delete('assinatura_tipo');
        formData.append('assinatura', assinaturaValue);
        formData.append('assinatura_tipo', assinaturaTipoValue);
        
        console.log('=== ENVIANDO ===');
        console.log('Razao Social:', formData.get('razao_social'));
        console.log('CNPJ:', formData.get('cnpj'));
        console.log('Assinatura tipo:', formData.get('assinatura_tipo'));
        console.log('Assinatura tamanho:', formData.get('assinatura')?.length || 0);
        
        const response = await fetch('submit-form.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Erro do servidor:', errorText);
            throw new Error('Erro do servidor: ' + response.status);
        }
        
        const result = await response.json();
        console.log('Resultado:', result);
        
        if (result.success) {
            // Redirecionar para página de sucesso
            if (result.pdf_assinado) {
                window.location.href = 'sucesso.php?pdf=' + encodeURIComponent(result.pdf_assinado);
            } else {
                window.location.href = 'sucesso.php';
            }
        } else {
            if (result.errors) {
                // Mostrar erros de validação
                Object.keys(result.errors).forEach(field => {
                    showError(field, result.errors[field]);
                });
            }
            showMessage(result.message, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showMessage('Erro ao enviar formulário. Tente novamente.', 'error');
    } finally {
        // Reabilitar botão
        submitBtn.disabled = false;
        btnText.style.display = 'inline';
        btnLoader.style.display = 'none';
    }
}

/**
 * Mostra erro em campo específico
 */
function showError(field, message) {
    const errorElement = document.getElementById(`error-${field}`);
    const formGroup = errorElement?.closest('.form-group');
    
    if (errorElement) {
        errorElement.textContent = message;
    }
    
    if (formGroup) {
        formGroup.classList.add('error');
    }
}

/**
 * Limpa todos os erros
 */
function clearErrors() {
    document.querySelectorAll('.error-message').forEach(el => {
        el.textContent = '';
    });
    
    document.querySelectorAll('.form-group').forEach(el => {
        el.classList.remove('error');
    });
}

/**
 * Mostra mensagem de sucesso/erro
 */
function showMessage(message, type) {
    const messageElement = document.getElementById('formMessage');
    messageElement.innerHTML = message; // Usar innerHTML para permitir HTML (link de download)
    messageElement.className = `form-message ${type}`;
    messageElement.style.display = 'block';
    
    // Auto-hide após 30 segundos (mais tempo para baixar o PDF)
    setTimeout(() => {
        messageElement.style.display = 'none';
    }, 30000);
}


/**
 * ===================================
 * PDF Viewer com PDF.js
 * ===================================
 */
let pdfDoc = null;
let pageNum = 1;
let pageRendering = false;
let pageNumPending = null;
let lastPageReached = false;

// Configurar PDF.js
if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    
    // Carregar PDF ao iniciar
    const pdfUrl = 'doc/Código de Relacionamento para Fornecedores de Bens e Serviços_2025.pdf';
    const loadingDiv = document.getElementById('pdfLoading');
    const controlsDiv = document.getElementById('pdfControls');
    const canvasWrapper = document.getElementById('pdfCanvasWrapper');
    const canvas = document.getElementById('pdfCanvas');
    const ctx = canvas.getContext('2d');
    
    // Detectar se é mobile
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const scale = isMobile ? 1.2 : 1.5;
    
    /**
     * Renderizar página do PDF
     */
    function renderPage(num) {
        pageRendering = true;
        
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({scale: scale});
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            
            const renderTask = page.render(renderContext);
            
            renderTask.promise.then(function() {
                pageRendering = false;
                
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
                
                // Verificar se chegou na última página
                if (num === pdfDoc.numPages) {
                    lastPageReached = true;
                    document.getElementById('scrollIndicator').style.display = 'none';
                    document.getElementById('pdfConfirmContainer').style.display = 'block';
                }
            });
        });
        
        document.getElementById('pageNum').textContent = num;
        updatePdfButtons();
    }
    
    /**
     * Enfileirar renderização de página
     */
    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }
    
    /**
     * Atualizar estado dos botões
     */
    function updatePdfButtons() {
        document.getElementById('prevPage').disabled = (pageNum <= 1);
        document.getElementById('nextPage').disabled = (pageNum >= pdfDoc.numPages);
        
        // Mostrar indicador se não chegou na última página
        if (pageNum < pdfDoc.numPages && !lastPageReached) {
            document.getElementById('scrollIndicator').style.display = 'block';
        } else {
            document.getElementById('scrollIndicator').style.display = 'none';
        }
    }
    
    /**
     * Página anterior
     */
    document.getElementById('prevPage').addEventListener('click', function() {
        if (pageNum <= 1) {
            return;
        }
        pageNum--;
        queueRenderPage(pageNum);
    });
    
    /**
     * Próxima página
     */
    document.getElementById('nextPage').addEventListener('click', function() {
        if (pageNum >= pdfDoc.numPages) {
            return;
        }
        pageNum++;
        queueRenderPage(pageNum);
    });
    
    /**
     * Confirmar leitura do documento
     */
    document.getElementById('btnConfirmRead').addEventListener('click', function() {
        if (!lastPageReached) {
            alert('Por favor, navegue até a última página do documento antes de continuar.');
            return;
        }
        
        // Mostrar checkbox de aceite
        document.getElementById('termosGroup').style.display = 'block';
        document.getElementById('pdfConfirmContainer').style.display = 'none';
        
        // Scroll suave até o checkbox
        document.getElementById('termosGroup').scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    
    /**
     * Carregar PDF
     */
    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('pageCount').textContent = pdfDoc.numPages;
        
        // Esconder loading e mostrar PDF
        loadingDiv.style.display = 'none';
        controlsDiv.style.display = 'flex';
        canvasWrapper.style.display = 'flex';
        document.getElementById('scrollIndicator').style.display = 'block';
        
        // Renderizar primeira página
        renderPage(pageNum);
    }).catch(function(error) {
        console.error('Erro ao carregar PDF:', error);
        loadingDiv.innerHTML = 'Erro ao carregar o documento. <a href="' + pdfUrl + '" target="_blank">Clique aqui para abrir em nova aba</a>';
    });
}
