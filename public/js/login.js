document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad mostrar/ocultar contraseña
    const passwordToggle = document.getElementById('password-toggle');
    const passwordInput = document.getElementById('password');
    
    if (passwordToggle && passwordInput) {
        passwordToggle.style.display = 'flex'; // Mostrar el botón
        
        passwordToggle.addEventListener('click', function() {
            const eyeIcon = this.querySelector('.eye-icon');
            const eyeOffIcon = this.querySelector('.eye-off-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        });
    }

    // Auto-focus en el campo de usuario cuando carga la página
    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.focus();
    }

    // Enter en username pasa a password
    if (usernameInput && passwordInput) {
        usernameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                passwordInput.focus();
            }
        });
    }

    // Limpiar errores cuando el usuario empieza a escribir
    const inputs = ['username', 'password'];
    inputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function() {
                // Limpiar errores visuales
                this.classList.remove('error-input');
                const errorMsg = this.parentElement.querySelector('.error-message');
                if (errorMsg) errorMsg.remove();

                // Limpiar mensaje general de error
                const messageContainer = document.getElementById('message-container');
                if (messageContainer && messageContainer.classList.contains('visible')) {
                    messageContainer.classList.remove('visible');
                }
            });
        }
    });
});