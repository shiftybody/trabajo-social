// Agregar al archivo main.js existente

// Sistema de Instant Search
(function() {
  // Datos de búsqueda con permisos
  const searchData = [
    // Sección Principal
    { 
      name: "Inicio", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}home`, 
      permissionKey: "home.view",
      section: "Principal",
      icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>'
    },
    
    // Gestión de Usuarios
    { 
      name: "Lista de Usuarios", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}users`, 
      permissionKey: "users.view",
      section: "Usuarios",
      icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/></svg>'
    },
    { 
      name: "Crear Usuario", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}users/create`, 
      permissionKey: "users.create",
      section: "Usuarios",
      badge: "Nuevo"
    },
    
    // Gestión de Roles
    { 
      name: "Lista de Roles", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}roles`, 
      permissionKey: "roles.view",
      section: "Roles y Permisos",
      icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/></svg>'
    },
    { 
      name: "Gestionar Permisos", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}permissions`, 
      permissionKey: "permissions.view",
      section: "Roles y Permisos"
    },
    
    // Pacientes
    { 
      name: "Lista de Pacientes", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}patients`, 
      permissionKey: "patients.view",
      section: "Pacientes",
      icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3.5 17.5c5.667 4.667 11.333 4.667 17 0"/><path d="M19 18.5l-2 -8.5l1 -2l2 1l1.5 -1.5l-2.5 -4.5c-5.052 .218 -5.99 3.133 -7 6h-6a3 3 0 0 0 -3 3"/><path d="M5 18.5l2 -9.5"/><path d="M8 20l2 -5h4l2 5"/></svg>'
    },
    { 
      name: "Registrar Paciente", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}patients/create`, 
      permissionKey: "patients.create",
      section: "Pacientes",
      badge: "Nuevo"
    },
    
    // Donaciones
    { 
      name: "Gestión de Donaciones", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}donations`, 
      permissionKey: "donations.view",
      section: "Donaciones",
      icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14.017 18l-2.017 2l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 0 1 8.153 5.784"/><path d="M15.99 20l4.197 -4.223a2.81 2.81 0 0 0 0 -3.948a2.747 2.747 0 0 0 -3.91 -.007l-.28 .282l-.279 -.283a2.747 2.747 0 0 0 -3.91 -.007a2.81 2.81 0 0 0 -.007 3.948l4.182 4.238z"/></svg>'
    },
    
    // Reportes y Estadísticas
    { 
      name: "Reportes", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}reports`, 
      permissionKey: "reports.view",
      section: "Análisis",
      icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z"/></svg>'
    },
    { 
      name: "Estadísticas", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}stats`, 
      permissionKey: "stats.view",
      section: "Análisis",
      icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 13a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M15 9a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M9 5a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"/><path d="M4 20h14"/></svg>'
    },
    
    // Configuración
    { 
      name: "Configuración General", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}settings`, 
      permissionKey: "settings.view",
      section: "Configuración",
      icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/></svg>'
    },
    { 
      name: "Mi Perfil", 
      url: `${typeof APP_URL !== 'undefined' ? APP_URL : ''}profile`, 
      permissionKey: "profile.view",
      section: "Configuración",
      icon: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z"/><path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"/><path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05"/></svg>'
    }
  ];

  // Referencias DOM
  const mainSearchInput = document.getElementById('mainSearchInput');
  const searchBackdrop = document.getElementById('searchBackdrop');
  const instantSearchContainer = document.getElementById('instantSearchContainer');
  const modalSearchInput = document.getElementById('modalSearchInput');
  const searchResultsList = document.getElementById('searchResultsList');
  const clearButton = document.getElementById('clearInstantSearch');

  // Verificar que los elementos existan
  if (!mainSearchInput || !searchBackdrop || !instantSearchContainer) {
    return; // No inicializar si faltan elementos
  }

  let isSearchOpen = false;

  // Función para abrir el instant search
  function openInstantSearch() {
    if (isSearchOpen) return;
    
    isSearchOpen = true;
    searchBackdrop.classList.add('active');
    instantSearchContainer.classList.add('active');
    
    // Limpiar y enfocar el input del modal
    modalSearchInput.value = '';
    searchResultsList.innerHTML = '';
    clearButton.style.display = 'none';
    
    setTimeout(() => {
      modalSearchInput.focus();
    }, 100);
    
    // Mostrar todos los resultados inicialmente
    performSearch('');
  }

  // Función para cerrar el instant search
  function closeInstantSearch() {
    if (!isSearchOpen) return;
    
    isSearchOpen = false;
    searchBackdrop.classList.remove('active');
    instantSearchContainer.classList.remove('active');
    
    // Limpiar el input principal
    mainSearchInput.value = '';
  }

  // Función para realizar la búsqueda
  function performSearch(query) {
    const normalizedQuery = query.toLowerCase().trim();
    
    if (!normalizedQuery) {
      // Mostrar todos los resultados agrupados por sección
      displayAllResults();
      return;
    }
    
    // Filtrar resultados
    const filteredResults = searchData.filter(item => 
      item.name.toLowerCase().includes(normalizedQuery)
    );
    
    displaySearchResults(filteredResults, normalizedQuery);
  }

  // Mostrar todos los resultados agrupados
  function displayAllResults() {
    const groupedResults = {};
    
    // Agrupar por sección
    searchData.forEach(item => {
      const section = item.section || 'Otros';
      if (!groupedResults[section]) {
        groupedResults[section] = [];
      }
      groupedResults[section].push(item);
    });
    
    let html = '';
    
    Object.entries(groupedResults).forEach(([section, items]) => {
      html += `<div class="search-result-section">`;
      html += `<div class="search-section-title">${section}</div>`;
      
      items.forEach(item => {
        html += generateResultItemHTML(item);
      });
      
      html += `</div>`;
    });
    
    searchResultsList.innerHTML = html;
  }

  // Mostrar resultados de búsqueda
  function displaySearchResults(results, query) {
    if (results.length === 0) {
      searchResultsList.innerHTML = '<div class="no-results">No se encontraron resultados.</div>';
      return;
    }
    
    let html = '<div class="search-result-section">';
    
    results.forEach(item => {
      html += generateResultItemHTML(item, query);
    });
    
    html += '</div>';
    searchResultsList.innerHTML = html;
  }

  // Generar HTML para un elemento de resultado
  function generateResultItemHTML(item, highlightQuery = '') {
    // Generar el HTML con la sintaxis PHP para verificación de permisos
    let html = `<?php if (App\\Core\\Auth::can('${item.permissionKey}')): ?>`;
    html += `<a href="${item.url}" class="search-result-item">`;
    
    // Agregar icono si existe
    if (item.icon) {
      html += `<span class="search-result-icon">${item.icon}</span>`;
    }
    
    // Nombre del elemento (con resaltado si hay query)
    let displayName = item.name;
    if (highlightQuery) {
      const regex = new RegExp(`(${escapeRegExp(highlightQuery)})`, 'gi');
      displayName = displayName.replace(regex, '<strong>$1</strong>');
    }
    
    html += `<span class="search-result-text">${displayName}</span>`;
    
    // Agregar badge si existe
    if (item.badge) {
      html += `<span class="search-result-badge">${item.badge}</span>`;
    }
    
    html += `</a>`;
    html += `<?php endif; ?>`;
    
    return html;
  }

  // Función auxiliar para escapar caracteres especiales en regex
  function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  }

  // Event Listeners
  
  // Click en el input principal
  mainSearchInput.addEventListener('click', openInstantSearch);
  
  // Detectar el carácter '/'
  mainSearchInput.addEventListener('keydown', (e) => {
    if (e.key === '/') {
      e.preventDefault();
      openInstantSearch();
    }
  });
  
  // También detectar '/' globalmente cuando no esté en un input
  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && 
        !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) &&
        !document.activeElement.isContentEditable) {
      e.preventDefault();
      openInstantSearch();
    }
  });
  
  // Búsqueda en tiempo real
  modalSearchInput.addEventListener('input', (e) => {
    performSearch(e.target.value);
    
    // Mostrar/ocultar botón clear
    if (e.target.value) {
      clearButton.style.display = 'inline';
    } else {
      clearButton.style.display = 'none';
    }
  });
  
  // Funcionalidad del botón clear
  clearButton.addEventListener('click', () => {
    modalSearchInput.value = '';
    modalSearchInput.focus();
    clearButton.style.display = 'none';
    performSearch('');
  });
  
  // Cerrar con Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isSearchOpen) {
      closeInstantSearch();
    }
  });
  
  // Cerrar al hacer click en el backdrop
  searchBackdrop.addEventListener('click', closeInstantSearch);
  
  // Prevenir que los clicks dentro del modal lo cierren
  instantSearchContainer.addEventListener('click', (e) => {
    e.stopPropagation();
  });
  
  // Navegar con teclado
  modalSearchInput.addEventListener('keydown', (e) => {
    const results = searchResultsList.querySelectorAll('.search-result-item');
    const currentFocus = searchResultsList.querySelector('.search-result-item:focus');
    let currentIndex = Array.from(results).indexOf(currentFocus);
    
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (currentIndex < results.length - 1) {
        results[currentIndex + 1].focus();
      } else if (currentIndex === -1 && results.length > 0) {
        results[0].focus();
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (currentIndex > 0) {
        results[currentIndex - 1].focus();
      } else if (currentIndex === 0) {
        modalSearchInput.focus();
      }
    } else if (e.key === 'Enter' && currentIndex !== -1) {
      e.preventDefault();
      results[currentIndex].click();
    }
  });
})();