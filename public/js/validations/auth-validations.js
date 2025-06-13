// Esquemas de validación para autenticación
const AUTH_VALIDATION_SCHEMAS = {
    login: {
        username: {
            required: { message: 'Ingrese su nombre de usuario o correo' },
            minLength: { value: 3, message: 'Mínimo 3 caracteres' }
        },
        password: {
            required: { message: 'Ingrese su contraseña' },
            minLength: { value: 1, message: 'La contraseña es obligatoria' }
        }
    }
};

// Funciones de validación
const AuthValidations = {
    validateLogin: async (form) => {
        const formData = new FormData(form);
        return await FormValidator.validate(formData, AUTH_VALIDATION_SCHEMAS.login);
    }
};

// Manejadores de respuesta
const AuthHandlers = {
    onLoginSuccess: async (data, form) => {
        // Mostrar mensaje de éxito breve
        if (window.CustomDialog) {
            CustomDialog.toast('Iniciando sesión...', 'success', 1500);
        }
        
        // Redirigir después de un breve delay
        setTimeout(() => {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.href = `${APP_URL}home`;
            }
        }, 1000);
    },

    onLoginError: async (data, form) => {
        // Mostrar mensaje de error en el contenedor específico del login
        const messageContainer = document.getElementById('message-container');
        
        if (messageContainer) {
            messageContainer.textContent = data.message || 'Credenciales incorrectas';
            messageContainer.classList.add('visible');
            
            // Ocultar el mensaje después de 5 segundos
            setTimeout(() => {
                messageContainer.classList.remove('visible');
            }, 5000);
        }
        
        // También mostrar con CustomDialog si está disponible
        if (window.CustomDialog) {
            await CustomDialog.error('Error de Autenticación', data.message || 'Usuario o contraseña incorrectos');
        }
    }
};

// Registrar validaciones cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Registrar formulario de login
    if (document.getElementById('login-form')) {
        FormManager.register('login-form', {
            validate: AuthValidations.validateLogin,
            onSuccess: AuthHandlers.onLoginSuccess,
            onError: AuthHandlers.onLoginError,
            beforeSubmit: (form) => {
                // Limpiar mensajes de error previos
                const messageContainer = document.getElementById('message-container');
                if (messageContainer) {
                    messageContainer.classList.remove('visible');
                }
            }
        });
    }
});