// JavaScript para el funcionamiento del chatbot
document.addEventListener('DOMContentLoaded', function() {
    const chatBubble = document.getElementById('chat-bubble');
    const chatContainer = document.getElementById('chat-container');
    const closeBtn = document.getElementById('close-btn');

    // Mostrar u ocultar el chat al hacer click en el botón
    if (chatBubble) {
        chatBubble.addEventListener('click', () => {
            const isVisible = chatContainer.style.display === 'block';
            chatContainer.style.display = isVisible ? 'none' : 'block';
            
            // Guardar estado en localStorage (solo si está disponible)
            try {
                localStorage.setItem('chatVisible', !isVisible);
            } catch (e) {
                // localStorage no disponible, continuar sin guardar estado
            }
        });
    }

    // Botón de cerrar dentro del chat
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            chatContainer.style.display = 'none';
            
            // Guardar estado en localStorage (solo si está disponible)
            try {
                localStorage.setItem('chatVisible', 'false');
            } catch (e) {
                // localStorage no disponible, continuar sin guardar estado
            }
        });
    }

    // Mantener el estado al recargar (solo si localStorage está disponible)
    try {
        if (localStorage.getItem('chatVisible') === 'true') {
            chatContainer.style.display = 'block';
        }
    } catch (e) {
        // localStorage no disponible, continuar sin restaurar estado
    }

    // Auto-scroll del chat
    const chatBox = document.getElementById('chat');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Enfocar el input de mensaje cuando se abre el chat
    const messageInput = chatContainer.querySelector('input[name="mensaje"]');
    if (messageInput && chatContainer.style.display === 'block') {
        messageInput.focus();
    }
});

// Función para enviar mensajes desde botones
function enviarMensaje(mensaje) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'php/chatbot_processor.php';
    form.style.display = 'none';
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'mensaje';
    input.value = mensaje;
    
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// Manejar envío de formularios del chat
document.addEventListener('submit', function(e) {
    if (e.target.closest('.chat-container')) {
        const submitBtn = e.target.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';
        }
    }
});

// Atajos de teclado para el chat
document.addEventListener('keydown', function(e) {
    // Ctrl + Alt + C para abrir/cerrar chat
    if (e.ctrlKey && e.altKey && e.key === 'c') {
        e.preventDefault();
        const chatBubble = document.getElementById('chat-bubble');
        if (chatBubble) {
            chatBubble.click();
        }
    }
    
    // Escape para cerrar chat
    if (e.key === 'Escape') {
        const chatContainer = document.getElementById('chat-container');
        if (chatContainer && chatContainer.style.display === 'block') {
            chatContainer.style.display = 'none';
            try {
                localStorage.setItem('chatVisible', 'false');
            } catch (e) {
                // localStorage no disponible
            }
        }
    }
});