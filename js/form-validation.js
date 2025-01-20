function maskTelefone(input) {
    // Remove tudo que não for número
    let value = input.value.replace(/\D/g, '');
    
    // Formata o número conforme a quantidade de dígitos
    if (value.length <= 11) {
        if (value.length > 2) {
            value = '(' + value.substring(0,2) + ')' + value.substring(2);
        }
        if (value.length > 7) {
            if (value.length <= 10) {
                value = value.substring(0,7) + '-' + value.substring(7);
            } else {
                value = value.substring(0,8) + '-' + value.substring(8);
            }
        }
    }
    
    input.value = value;
}

function validateForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('#submitBtn');
    submitBtn.disabled = true;

    // Validações
    const nome = form.querySelector('[name="nome"]');
    if (nome.value.length < 3) {
        showMessage('error', 'Nome deve ter pelo menos 3 caracteres');
        submitBtn.disabled = false;
        return false;
    }

    const email = form.querySelector('[name="email"]');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
        showMessage('error', 'Email inválido');
        submitBtn.disabled = false;
        return false;
    }

    const telefone = form.querySelector('[name="telefone"]');
    const telefoneNumeros = telefone.value.replace(/\D/g, '');
    if (telefoneNumeros.length < 10 || telefoneNumeros.length > 11) {
        showMessage('error', 'Digite um telefone válido no formato (99)99999-9999');
        submitBtn.disabled = false;
        return false;
    }

    const mensagem = form.querySelector('[name="mensagem"]');
    if (mensagem.value.length < 10) {
        showMessage('error', 'Mensagem muito curta');
        submitBtn.disabled = false;
        return false;
    }

    // Envio AJAX
    const formData = new FormData(form);
    
    fetch(formAjax.ajaxurl, {  // Usando a URL do WordPress
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(response.statusText);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            showMessage('success', data.message);
            form.reset();
        } else {
            showMessage('error', data.message || 'Erro ao enviar mensagem');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showMessage('error', 'Erro ao enviar mensagem. Tente novamente.');
    })
    .finally(() => {
        submitBtn.disabled = false;
    });

    return false;
}

function showMessage(status, text) {
    const oldMessage = document.querySelector('.form-message');
    if (oldMessage) {
        oldMessage.remove();
    }

    const message = document.createElement('div');
    message.className = `form-message ${status}`;
    message.innerHTML = `<p>${text}</p>`;

    const form = document.querySelector('.form-contato');
    const submitBtn = form.querySelector('#submitBtn');
    submitBtn.parentNode.insertBefore(message, submitBtn.nextSibling);

    setTimeout(() => {
        message.style.opacity = '0';
        setTimeout(() => message.remove(), 300);
    }, 5000);
}

// Adiciona listener para mensagens de erro/sucesso
document.addEventListener('DOMContentLoaded', function() {
    const message = document.querySelector('.form-message');
    if (message) {
        // Remove parâmetros da URL
        window.history.replaceState({}, document.title, window.location.pathname);
        
        // Remove a mensagem após 5 segundos
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 300);
        }, 5000);
    }
}); 