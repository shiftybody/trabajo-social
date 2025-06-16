class FormValidator {
    constructor() {
        this.rules = {
            required: (value, rule) => {
                const isEmpty = !value || (typeof value === 'string' && value.trim() === '');
                return isEmpty ? (rule.message || 'Este campo es obligatorio') : null;
            },
            
            minLength: (value, rule) => {
                if (!value) return null;
                return value.length < rule.value ? 
                    (rule.message || `Mínimo ${rule.value} caracteres`) : null;
            },
            
            maxLength: (value, rule) => {
                if (!value) return null;
                return value.length > rule.value ? 
                    (rule.message || `Máximo ${rule.value} caracteres`) : null;
            },
            
            pattern: (value, rule) => {
                if (!value) return null;
                const regex = new RegExp(rule.value);
                return !regex.test(value) ? 
                    (rule.message || 'Formato no válido') : null;
            },
            
            email: (value, rule) => {
                if (!value) return null;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return !emailRegex.test(value) ? 
                    (rule.message || 'Correo electrónico no válido') : null;
            },
            
            matches: (value, rule, formData) => {
                if (!value) return null;
                const otherValue = formData.get(rule.field);
                return value !== otherValue ? 
                    (rule.message || 'Los campos no coinciden') : null;
            },
            
            fileSize: (file, rule) => {
                if (!file || file.size === 0) return null;
                return file.size > rule.value ? 
                    (rule.message || `Archivo muy grande. Máximo ${rule.value / 1024 / 1024}MB`) : null;
            },
            
            fileType: (file, rule) => {
                if (!file || file.size === 0) return null;
                return !rule.value.includes(file.type) ? 
                    (rule.message || 'Tipo de archivo no válido') : null;
            }
        };
    }

    // Agregar regla personalizada
    addRule(name, validator) {
        this.rules[name] = validator;
    }

    // Validar usando un esquema
    async validate(formData, schema) {
        const errors = {};
        let isValid = true;

        for (const [fieldName, fieldRules] of Object.entries(schema)) {
            const fieldValue = formData.get(fieldName);
            
            // Si es un archivo
            const fileInput = document.querySelector(`[name="${fieldName}"][type="file"]`);
            const actualValue = fileInput ? fileInput.files[0] : fieldValue;

            for (const [ruleName, ruleConfig] of Object.entries(fieldRules)) {
                if (ruleName === 'custom') {
                    // Regla personalizada
                    const customError = await ruleConfig(actualValue, formData);
                    if (customError) {
                        errors[fieldName] = customError;
                        isValid = false;
                        break;
                    }
                } else if (this.rules[ruleName]) {
                    const error = this.rules[ruleName](actualValue, ruleConfig, formData);
                    if (error) {
                        errors[fieldName] = error;
                        isValid = false;
                        break;
                    }
                }
            }
        }

        return { isValid, errors };
    }
}

// Crear instancia global
window.FormValidator = new FormValidator();