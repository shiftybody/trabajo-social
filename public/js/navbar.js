// Elementos del sidebar izquierdo
const menuButton = document.getElementById("left-menu");
const closeButton = document.getElementById("left-closeButton");
const sidebar = document.getElementById("left-sidebar");

// Elementos del sidebar derecho
const menuAvatar = document.getElementById("avatar");
const closeAvatar = document.getElementById("right-closeButton");
const sidebarAvatar = document.getElementById("right-sidebar");

// Elemento de blur para el contenido
const contentBlur = document.querySelector(".contentblur");

// Función para verificar si algún sidebar está abierto
function isSidebarOpen() {
  return sidebar.style.width === "20rem" || sidebarAvatar.style.width === "20rem";
}

// Evento para abrir el sidebar izquierdo
menuButton.addEventListener("click", function(event) {
  event.stopPropagation(); // Evita que el click se propague al documento
  sidebar.style.width = "20rem"; // Despliega el menú lateral izquierdo
  sidebarAvatar.style.width = "0"; // Cierra el menú lateral derecho si estaba abierto
  contentBlur.style.display = "block";
});

// Evento para cerrar el sidebar izquierdo con su botón de cierre
closeButton.addEventListener("click", function(event) {
  event.stopPropagation(); // Evitar propagación
  sidebar.style.width = "0"; // Cierra el menú
  // Solo quita el blur si no hay otro sidebar abierto
  if (!isSidebarOpen()) {
    contentBlur.style.display = "none";
  }
});

// Evento para abrir el sidebar derecho
menuAvatar.addEventListener("click", function(event) {
  event.stopPropagation(); // Evita que el click se propague al documento
  sidebarAvatar.style.width = "20rem"; // Despliega el menú lateral derecho
  sidebar.style.width = "0"; // Cierra el menú lateral izquierdo si estaba abierto
  contentBlur.style.display = "block";
});

// Evento para cerrar el sidebar derecho con su botón de cierre
closeAvatar.addEventListener("click", function(event) {
  event.stopPropagation(); // Evitar propagación
  sidebarAvatar.style.width = "0"; // Cierra el menú
  // Solo quita el blur si no hay otro sidebar abierto
  if (!isSidebarOpen()) {
    contentBlur.style.display = "none";
  }
});

// Detectar clicks fuera de los sidebars
document.addEventListener("click", function(event) {
  // Verificar si el click NO fue dentro de ninguno de los sidebars o sus botones
  const clickedInsideSidebar = 
    sidebar.contains(event.target) || 
    sidebarAvatar.contains(event.target) || 
    menuButton.contains(event.target) || 
    menuAvatar.contains(event.target);
  
  // Si el click fue fuera, cerrar ambos sidebars
  if (!clickedInsideSidebar) {
    sidebar.style.width = "0";
    sidebarAvatar.style.width = "0";
    contentBlur.style.display = "none";
  }
});