/**
 * ============================================================================
 * VALIDACIONES PARA ROLES
 * ============================================================================
 * Archivo: public/js/validations/role-validations.js
 * 
 * Maneja validaciones para:
 * - Crear rol (index.php)
 * - Editar rol (index.php) 
 * - Asignar permisos (permissions.php)
 * 
 * Integra con el sistema de BaseModal y FormManager del proyecto
 * ============================================================================
 */

// ==================== ESQUEMAS DE VALIDACIÓN ====================

const ROLE_VALIDATION_SCHEMAS = {
  // Validación para crear rol
  create: {
    nombre: {
      required: {
        message: "El nombre del rol no puede estar vacío"
      },
      minLength: {
        value: 3,
        message: "El nombre del rol debe tener al menos 3 caracteres"
      },
      maxLength: {
        value: 50,
        message: "El nombre del rol no puede exceder 50 caracteres"
      },
      pattern: {
        value: "^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$",
        message: "El nombre del rol solo puede contener letras y espacios"
      },
    },
  },

  // Validación para editar rol
  edit: {
    nombre: {
      required: {
        message: "El nombre del rol no puede estar vacío"
      },
      minLength: {
        value: 3,
        message: "El nombre del rol debe tener al menos 3 caracteres"
      },
      maxLength: {
        value: 50,
        message: "El nombre del rol no puede exceder 50 caracteres"
      },
      pattern: {
        value: "^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$",
        message: "El nombre del rol solo puede contener letras y espacios"
      },
      custom: async (value, formData) => {
        // Validar que el nombre no exista (excluyendo el rol actual)
        const rolId = formData.get('rol_id') || window.currentRoleId;
        if (!rolId) return null;
        
        try {
          const response = await fetch(`${APP_URL}api/roles/check-name`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({ 
              nombre: value,
              exclude_id: rolId 
            })
          });
          
          const data = await response.json();
          if (data.exists) {
            return "Ya existe un rol con este nombre";
          }
        } catch (error) {
          console.warn("No se pudo validar duplicidad de nombre:", error);
        }
        return null;
      }
    }
  },

  // Validación para asignación de permisos
  permissions: {
    permisos: {
      custom: async (value, formData) => {
        const selectedPermissions = formData.getAll('permisos[]');
        
        if (selectedPermissions.length === 0) {
          return "Debe seleccionar al menos un permiso";
        }

        // Validar dependencias de permisos
        try {
          const response = await fetch(`${APP_URL}public/js/data/permissions-dependencies.json`);
          const dependencies = await response.json();
          
          const violations = [];
          
          dependencies.dependencies.forEach(dep => {
            const hasPermission = selectedPermissions.includes(dep.permissionSlug);
            const hasRequiredPermissions = dep.requires.every(req => 
              selectedPermissions.includes(req)
            );
            
            if (hasPermission && !hasRequiredPermissions) {
              const missing = dep.requires.filter(req => 
                !selectedPermissions.includes(req)
              );
              violations.push(`${dep.permissionSlug} requiere: ${missing.join(', ')}`);
            }
          });
          
          if (violations.length > 0) {
            return `Dependencias faltantes:\n${violations.join('\n')}`;
          }
          
        } catch (error) {
          console.warn("No se pudieron validar dependencias de permisos:", error);
        }
        
        return null;
      }
    }
  }
};

// ==================== FUNCIONES DE VALIDACIÓN ====================

const RoleValidations = {
  validateCreate: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(formData, ROLE_VALIDATION_SCHEMAS.create);
  },

  validateEdit: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(formData, ROLE_VALIDATION_SCHEMAS.edit);
  },

  validatePermissions: async (form) => {
    const formData = new FormData(form);
    return await FormValidator.validate(formData, ROLE_VALIDATION_SCHEMAS.permissions);
  }
};

// ==================== MANEJADORES DE RESPUESTA ====================

const RoleHandlers = {
  onCreateSuccess: async (data, form) => {
    // Cerrar modal si existe
    if (typeof BaseModal !== 'undefined') {
      BaseModal.closeAll();
    }
    
    await CustomDialog.success(
      "Rol Creado",
      data.message || "El rol se creó correctamente"
    );
    
    // Recargar tabla de roles
    if (typeof loadData === 'function') {
      await loadData();
    } else {
      window.location.reload();
    }
  },

  onEditSuccess: async (data, form) => {
    // Cerrar modal si existe
    if (typeof BaseModal !== 'undefined') {
      BaseModal.closeAll();
    }
    
    await CustomDialog.success(
      "Rol Actualizado", 
      data.message || "El rol se actualizó correctamente"
    );
    
    // Recargar tabla de roles
    if (typeof loadData === 'function') {
      await loadData();
    } else {
      window.location.reload();
    }
  },

  onPermissionsSuccess: async (data, form) => {
    await CustomDialog.success(
      "Permisos Actualizados",
      data.message || "Los permisos se actualizaron correctamente"
    );
    
    // Actualizar contador de permisos asignados
    RoleUtils.updatePermissionsCount();
    
    // Ocultar indicador de cambios
    const changesIndicator = document.getElementById('changes-indicator');
    if (changesIndicator) {
      changesIndicator.style.display = 'none';
    }
    
    // Deshabilitar botón guardar
    const saveButton = document.getElementById('save-permissions');
    if (saveButton) {
      saveButton.disabled = true;
    }
  },

  onError: async (data, form) => {
    if (data.errors) {
      // Mostrar errors específicos de campos
      for (const [field, message] of Object.entries(data.errors)) {
        const input = form.querySelector(`[name="${field}"], [name="${field}[]"]`);
        if (input) {
          input.classList.add("error-input");
          
          // Crear mensaje de error
          const errorMessage = document.createElement("div");
          errorMessage.className = "error-message";
          errorMessage.textContent = message;
          
          // Insertar después del input o su contenedor
          const container = input.closest('.input-field') || input.closest('.form-group') || input.parentElement;
          container.appendChild(errorMessage);
        }
      }
    } else {
      // Error general
      await CustomDialog.error(
        "Error",
        data.message || "Ocurrió un error al procesar la solicitud"
      );
    }
  }
};

// ==================== UTILIDADES ESPECÍFICAS PARA ROLES ====================

const RoleUtils = {
  // Actualizar contador de permisos en permissions.php
  updatePermissionsCount: () => {
    const checkboxes = document.querySelectorAll('input[name="permisos[]"]');
    const checkedBoxes = document.querySelectorAll('input[name="permisos[]"]:checked');
    const totalCount = checkboxes.length;
    const selectedCount = checkedBoxes.length;
    
    // Actualizar contadores en la UI
    const totalElement = document.getElementById('total-permissions');
    const assignedElement = document.getElementById('assigned-permissions');
    const selectedCountElement = document.getElementById('selected-count');
    
    if (totalElement) totalElement.textContent = totalCount;
    if (assignedElement) assignedElement.textContent = selectedCount;
    if (selectedCountElement) selectedCountElement.textContent = selectedCount;
  },

  // Configurar eventos para la página de permisos
  setupPermissionsEvents: () => {
    // Escuchar cambios en checkboxes de permisos
    document.addEventListener('change', (e) => {
      if (e.target.matches('input[name="permisos[]"]')) {
        RoleUtils.updatePermissionsCount();
        RoleUtils.showChangesIndicator();
      }
    });

    // Configurar botón "Seleccionar Todo"
    const selectAllBtn = document.getElementById('select-all-permissions');
    if (selectAllBtn) {
      selectAllBtn.addEventListener('click', () => {
        const checkboxes = document.querySelectorAll('input[name="permisos[]"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
          cb.checked = !allChecked;
        });
        
        RoleUtils.updatePermissionsCount();
        RoleUtils.showChangesIndicator();
      });
    }

    // Configurar botón guardar permisos
    const saveBtn = document.getElementById('save-permissions');
    if (saveBtn) {
      saveBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        
        // Crear form data con permisos seleccionados
        const formData = new FormData();
        const checkedBoxes = document.querySelectorAll('input[name="permisos[]"]:checked');
        
        checkedBoxes.forEach(checkbox => {
          formData.append('permisos[]', checkbox.value);
        });
        
        // Validar antes de enviar
        const validation = await RoleValidations.validatePermissions({ 
          querySelector: () => null,
          querySelectorAll: () => checkedBoxes
        });
        
        if (!validation.isValid) {
          await CustomDialog.error("Error de Validación", Object.values(validation.errors).join('\n'));
          return;
        }
        
        // Enviar permisos
        try {
          const roleId = window.currentRoleId || document.querySelector('[data-role-id]')?.dataset.roleId;
          const response = await fetch(`${APP_URL}api/roles/${roleId}/permissions`, {
            method: 'POST',
            body: formData,
            headers: {
              'Accept': 'application/json'
            }
          });
          
          const data = await response.json();
          
          if (response.ok && data.status === 'success') {
            await RoleHandlers.onPermissionsSuccess(data, { querySelector: () => null });
          } else {
            await RoleHandlers.onError(data, { querySelector: () => null });
          }
          
        } catch (error) {
          console.error('Error al guardar permisos:', error);
          await CustomDialog.error("Error", "No se pudieron guardar los permisos");
        }
      });
    }
  },

  // Mostrar indicador de cambios sin guardar
  showChangesIndicator: () => {
    const indicator = document.getElementById('changes-indicator');
    const saveButton = document.getElementById('save-permissions');
    
    if (indicator) {
      indicator.style.display = 'flex';
    }
    
    if (saveButton) {
      saveButton.disabled = false;
    }
  },

  // Limpiar errors de formularios
  clearFormErrors: (form) => {
    form.querySelectorAll('.error-message').forEach(el => el.remove());
    form.querySelectorAll('.error-input').forEach(el => el.classList.remove('error-input'));
  },

  // Configurar limpieza automática de errors al escribir
  setupErrorClearingOnInput: () => {
    document.addEventListener('input', (e) => {
      if (e.target.matches('input, select, textarea')) {
        if (e.target.classList.contains('error-input')) {
          e.target.classList.remove('error-input');
          
          const errorMessage = e.target.closest('.input-field, .form-group')?.querySelector('.error-message');
          if (errorMessage) {
            errorMessage.remove();
          }
        }
      }
    });
  }
};

// ==================== HELPERS PARA TEMPLATES ====================

// Extender TEMPLATE_HELPERS si no existe el método
if (typeof TEMPLATE_HELPERS !== 'undefined') {
  // Procesar datos para editar rol simplificado (solo nombre)
  TEMPLATE_HELPERS.processEditRoleSimplifiedData = (role) => {
    return {
      roleName: role.rol_nombre || '',
      submitText: 'Actualizar Rol'
    };
  };
}

// ==================== FUNCIONES GLOBALES PARA HTML ====================

// Función para gestionar permisos (usada en index.php)
function gestionarPermisos(rolId) {
  window.location.href = `${APP_URL}roles/${rolId}/permissions`;
}

// Función para eliminar rol (usada en index.php)
function eliminarRol(rolId, nombreRol, usuariosCount) {
  if (typeof window.mostrarModalEliminarRol === 'function') {
    window.mostrarModalEliminarRol(rolId, nombreRol, usuariosCount);
  }
}

// Función para crear rol (usada en index.php)
function crearRol() {
  if (typeof window.mostrarModalCrearRol === 'function') {
    window.mostrarModalCrearRol();
  }
}

// Función para editar rol (usada en index.php)
function editarRol(rolId) {
  if (typeof window.editarRol === 'function') {
    window.editarRol(rolId);
  }
}

// ==================== REGISTRO DE FORMULARIOS ====================

// 1. Función reutilizable para registrar formularios disponibles
function registerAvailableForms(container) {
  // Asegurarse de que el contenedor sea un elemento válido antes de usar querySelector
  if (!container || typeof container.querySelector !== 'function') {
    return;
  }

  // Verificar que FormManager esté disponible
  if (typeof FormManager === "undefined" || !FormManager) {
    console.error("FormManager no está disponible");
    return;
  }

  // --- Registrar formulario de creación de rol ---
  if (container.querySelector("#createRoleForm")) {
    console.log("Registrando createRoleForm...");
    FormManager.register("createRoleForm", {
      validate: RoleValidations.validateCreate,
      onSuccess: RoleHandlers.onCreateSuccess,
      onError: RoleHandlers.onError,
      beforeSubmit: (form) => {
        RoleUtils.clearFormErrors(form);
      }
    });
  }

  // --- Registrar formulario de edición de rol ---
  if (container.querySelector("#editRoleForm")) {
    console.log("Registrando editRoleForm...");
    FormManager.register("editRoleForm", {
      validate: RoleValidations.validateEdit,
      onSuccess: RoleHandlers.onEditSuccess,
      onError: RoleHandlers.onError,
      beforeSubmit: (form) => {
        RoleUtils.clearFormErrors(form);
      }
    });
  }

  // --- Registrar formulario de edición simplificada ---
  if (container.querySelector("#editRoleSimplifiedForm")) {
    console.log("Registrando editRoleSimplifiedForm...");
    FormManager.register("editRoleSimplifiedForm", {
      validate: RoleValidations.validateEdit,
      onSuccess: RoleHandlers.onEditSuccess,
      onError: RoleHandlers.onError,
      beforeSubmit: (form) => {
        RoleUtils.clearFormErrors(form);
      }
    });
  }
}

// ==================== INICIALIZACIÓN ====================

// 2. Ejecutar al cargar la página inicial
document.addEventListener("DOMContentLoaded", function () {
  // Configurar limpieza automática de errors
  RoleUtils.setupErrorClearingOnInput();

  // Para la página de permisos
  const currentPath = window.location.pathname;
  if (currentPath.includes('/permissions') || document.getElementById('save-permissions')) {
    RoleUtils.setupPermissionsEvents();
    RoleUtils.updatePermissionsCount();
    
    // Obtener role ID de la URL o del DOM
    const pathParts = currentPath.split('/');
    const roleIdIndex = pathParts.indexOf('roles') + 1;
    if (roleIdIndex > 0 && pathParts[roleIdIndex]) {
      window.currentRoleId = pathParts[roleIdIndex];
    }
  }

  // Registra los formularios que ya existen al cargar la página
  registerAvailableForms(document.body);

  // 3. Configurar el observador para futuros cambios en el DOM
  const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
      // Si se han añadido nodos (como un modal)
      if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
        mutation.addedNodes.forEach(node => {
          // Nos aseguramos de que el nodo sea un elemento HTML (nodeType 1)
          if (node.nodeType === 1) {
            registerAvailableForms(node);
          }
        });
      }
    }
  });

  observer.observe(document.body, { childList: true, subtree: true });
});