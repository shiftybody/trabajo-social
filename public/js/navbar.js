// cuando se presione un tag img con clase logo
document.querySelectorAll("img.logo").forEach((logo) => {
  logo.addEventListener("click", function () {
    // redirigir a la página principal
    window.location.href = APP_URL + "home";
  });
});

async function logout() {
  sidebarAvatar.classList.remove("open");
  contentBlur.classList.remove("active");

  const confirmacion = await CustomDialog.confirm(
    "Cerrar Sesión",
    `¿Está seguro de que deseas cerrar sesión?`,
    "Cerrar Sesión",
    "Cancelar"
  );

  if (confirmacion) {
    try {
      fetch(APP_URL + "api/logout", {
        method: "POST",
        headers: {
          Accept: "application/json",
        },
        credentials: "same-origin",
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Error en la respuesta del servidor");
          }
          return response.json();
        })
        .then((data) => {
          console.log(data);
          if (data.status === "success") {
            window.location.href = data.redirect;
          } else {
            CustomDialog.error("Error", data.message);
          }
        });
    } catch (error) {
      console.error("Error en la petición fetch:", error);
      CustomDialog.error(
        "Error de Red",
        "Ocurrió un problema al intentar conectar con el servidor."
      );
    }
  }
}

const menuButton = document.getElementById("leftMenu");
const closeButton = document.getElementById("leftCloseButton");
const sidebar = document.getElementById("leftSidebar");

const menuAvatar = document.getElementById("avatar");
const closeAvatar = document.getElementById("rightCloseButton");
const sidebarAvatar = document.getElementById("rightSidebar");

const contentBlur = document.querySelector(".contentblur");

function isSidebarOpen() {
  return (
    sidebar.classList.contains("open") ||
    sidebarAvatar.classList.contains("open")
  );
}

function closeAllSidebars() {
  sidebar.classList.remove("open");
  sidebarAvatar.classList.remove("open");
  contentBlur.classList.remove("active");
}

menuButton.addEventListener("click", function (event) {
  event.stopPropagation();
  sidebar.classList.add("open");
  sidebarAvatar.classList.remove("open");
  contentBlur.classList.add("active");
});

closeButton.addEventListener("click", function (event) {
  event.stopPropagation();
  sidebar.classList.remove("open");
  if (!isSidebarOpen()) {
    contentBlur.classList.remove("active");
  }
});

menuAvatar.addEventListener("click", function (event) {
  event.stopPropagation();
  sidebarAvatar.classList.add("open");
  sidebar.classList.remove("open");
  contentBlur.classList.add("active");
});

closeAvatar.addEventListener("click", function (event) {
  event.stopPropagation();
  sidebarAvatar.classList.remove("open");

  if (!isSidebarOpen()) {
    contentBlur.classList.remove("active");
  }
});

document.addEventListener("click", function (event) {
  const clickedInsideLeftSidebarOrButton =
    sidebar.contains(event.target) || menuButton.contains(event.target);
  const clickedInsideRightSidebarOrButton =
    sidebarAvatar.contains(event.target) || menuAvatar.contains(event.target);

  if (!clickedInsideLeftSidebarOrButton && !clickedInsideRightSidebarOrButton) {
    closeAllSidebars();
  }
});

document.addEventListener("keydown", function (event) {
  if (event.key === "Escape" && isSidebarOpen()) {
    closeAllSidebars();
  }
});

contentBlur.addEventListener("click", function () {
  closeAllSidebars();
});

// Variable para almacenar los datos de búsqueda
let searchData = [];

// Función para cargar la configuración de rutas
async function loadRoutesConfig() {
  try {
    const response = await fetch(
      APP_URL + "public/js/data/navigation-routes.json"
    );
    const config = await response.json();

    // Procesar las rutas para el formato que espera tu código
    searchData = config.routes.map((route) => ({
      name: route.name,
      url: APP_URL + route.url,
      permissionKey: route.permissionKey,
      section: route.section,
      icon: route.icon || null,
      badge: route.badge || null,
      id: route.id,
    }));
  } catch (error) {
    console.error("Error loading routes config:", error);
    searchData = [];
  }
}

// Referencias DOM
const mainSearchInput = document.getElementById("mainSearchInput");
const searchBackdrop = document.getElementById("searchBackdrop");
const instantSearchContainer = document.getElementById(
  "instantSearchContainer"
);
const modalSearchInput = document.getElementById("modalSearchInput");
const searchResultsList = document.getElementById("searchResultsList");
const clearSearchButton = document.getElementById("clearInstantSearch");

let isSearchOpen = false;

// Función para abrir el instant search
function openInstantSearch() {
  if (isSearchOpen) return;

  isSearchOpen = true;
  searchBackdrop.classList.add("active");
  instantSearchContainer.classList.add("active");

  // Limpiar y enfocar el input del modal
  modalSearchInput.value = "";
  searchResultsList.innerHTML = "";
  clearSearchButton.style.display = "none";

  setTimeout(() => {
    modalSearchInput.focus();
  }, 100);

  // Mostrar todos los resultados inicialmente
  performSearch("");
}

// Función para cerrar el instant search
function closeInstantSearch() {
  if (!isSearchOpen) return;

  isSearchOpen = false;
  searchBackdrop.classList.remove("active");
  instantSearchContainer.classList.remove("active");

  // Limpiar el input principal
  mainSearchInput.value = "";
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
  const filteredResults = searchData.filter((item) =>
    item.name.toLowerCase().includes(normalizedQuery)
  );

  displaySearchResults(filteredResults, normalizedQuery);
}

// Mostrar todos los resultados agrupados
function displayAllResults() {
  const groupedResults = {};

  // Agrupar por sección
  searchData.forEach((item) => {
    const section = item.section || "Otros";
    if (!groupedResults[section]) {
      groupedResults[section] = [];
    }
    groupedResults[section].push(item);
  });

  let html = "";

  Object.entries(groupedResults).forEach(([section, items]) => {
    html += `<div class="search-result-section">`;
    html += `<div class="search-section-title">${section}</div>`;

    items.forEach((item) => {
      html += generateResultItemHTML(item);
    });

    html += `</div>`;
  });

  searchResultsList.innerHTML = html;
}

// Mostrar resultados de búsqueda
function displaySearchResults(results, query) {
  if (results.length === 0) {
    searchResultsList.innerHTML =
      '<div class="no-results">No se encontraron resultados.</div>';
    return;
  }

  let html = '<div class="search-result-section">';

  results.forEach((item) => {
    html += generateResultItemHTML(item, query);
  });

  html += "</div>";
  searchResultsList.innerHTML = html;
}

// Generar HTML para un elemento de resultado
function generateResultItemHTML(item, highlightQuery = "") {
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
    const regex = new RegExp(`(${escapeRegExp(highlightQuery)})`, "gi");
    displayName = displayName.replace(regex, "<strong>$1</strong>");
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
  return string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

// Event Listeners

// Click en el input principal
mainSearchInput.addEventListener("click", openInstantSearch);

// Detectar el carácter '/'
mainSearchInput.addEventListener("keydown", (e) => {
  if (e.key === "/") {
    e.preventDefault();
    openInstantSearch();
  }
});

// También detectar '/' globalmente cuando no esté en un input
document.addEventListener("keydown", (e) => {
  if (
    e.key === "/" &&
    !["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement.tagName) &&
    !document.activeElement.isContentEditable
  ) {
    e.preventDefault();
    openInstantSearch();
  }
});

// Búsqueda en tiempo real
modalSearchInput.addEventListener("input", (e) => {
  performSearch(e.target.value);

  // Mostrar/ocultar botón clear
  if (e.target.value) {
    clearSearchButton.style.display = "inline";
  } else {
    clearSearchButton.style.display = "none";
  }
});

// Funcionalidad del botón clear
clearSearchButton.addEventListener("click", () => {
  modalSearchInput.value = "";
  modalSearchInput.focus();
  clearSearchButton.style.display = "none";
  performSearch("");
});

// Cerrar con Escape
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape" && isSearchOpen) {
    closeInstantSearch();
  }
});

// Cerrar al hacer click en el backdrop
searchBackdrop.addEventListener("click", closeInstantSearch);

// Prevenir que los clicks dentro del modal lo cierren
instantSearchContainer.addEventListener("click", (e) => {
  e.stopPropagation();
});

// Navegar con teclado
modalSearchInput.addEventListener("keydown", (e) => {
  const results = searchResultsList.querySelectorAll(".search-result-item");
  const currentFocus = searchResultsList.querySelector(
    ".search-result-item:focus"
  );
  let currentIndex = Array.from(results).indexOf(currentFocus);

  if (e.key === "ArrowDown") {
    e.preventDefault();
    if (currentIndex < results.length - 1) {
      results[currentIndex + 1].focus();
    } else if (currentIndex === -1 && results.length > 0) {
      results[0].focus();
    }
  } else if (e.key === "ArrowUp") {
    e.preventDefault();
    if (currentIndex > 0) {
      results[currentIndex - 1].focus();
    } else if (currentIndex === 0) {
      modalSearchInput.focus();
    }
  } else if (e.key === "Enter" && currentIndex !== -1) {
    e.preventDefault();
    results[currentIndex].click();
  }
});

// Cargar configuración al cargar la página
document.addEventListener("DOMContentLoaded", loadRoutesConfig);

window.openInputSearch = openInstantSearch;
