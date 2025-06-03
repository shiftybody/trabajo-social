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
  return (
    sidebar.classList.contains("open") ||
    sidebarAvatar.classList.contains("open")
  );
}

// Función para cerrar todos los sidebars
function closeAllSidebars() {
  sidebar.classList.remove("open");
  sidebarAvatar.classList.remove("open");
  contentBlur.classList.remove("active");
}

// Evento para abrir el sidebar izquierdo
menuButton.addEventListener("click", function (event) {
  event.stopPropagation(); // Evita que el click se propague al documento
  sidebar.classList.add("open"); // Muestra el sidebar izquierdo
  sidebarAvatar.classList.remove("open"); // Cierra el sidebar derecho si estaba abierto
  contentBlur.classList.add("active"); // Muestra el efecto de blur
});

// Evento para cerrar el sidebar izquierdo con su botón de cierre
closeButton.addEventListener("click", function (event) {
  event.stopPropagation(); // Evitar propagación
  sidebar.classList.remove("open");
  if (!isSidebarOpen()) {
    contentBlur.classList.remove("active");
  }
});

// Evento para abrir el sidebar derecho
menuAvatar.addEventListener("click", function (event) {
  event.stopPropagation(); // Evita que el click se propague al documento
  sidebarAvatar.classList.add("open"); // Muestra el sidebar derecho
  sidebar.classList.remove("open"); // Cierra el sidebar izquierdo si estaba abierto
  contentBlur.classList.add("active"); // Muestra el efecto de blur
});

// Evento para cerrar el sidebar derecho con su botón de cierre
closeAvatar.addEventListener("click", function (event) {
  event.stopPropagation(); // Evitar propagación
  sidebarAvatar.classList.remove("open"); // Cierra el menú
  // Solo quita el blur si no hay otro sidebar abierto
  if (!isSidebarOpen()) {
    contentBlur.classList.remove("active");
  }
});

// Detectar clicks fuera de los sidebars
document.addEventListener("click", function (event) {
  // Verificar si el click NO fue dentro de ninguno de los sidebars o sus botones de apertura
  const clickedInsideLeftSidebarOrButton =
    sidebar.contains(event.target) || menuButton.contains(event.target);
  const clickedInsideRightSidebarOrButton =
    sidebarAvatar.contains(event.target) || menuAvatar.contains(event.target);

  // Si el click fue fuera de ambos sidebars y sus respectivos botones de apertura
  if (!clickedInsideLeftSidebarOrButton && !clickedInsideRightSidebarOrButton) {
    closeAllSidebars();
  }
});

// NUEVO: Detectar tecla ESC para cerrar sidebars
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape" && isSidebarOpen()) {
    closeAllSidebars();
  }
});

// NUEVO: Cerrar sidebars al hacer click en el contentBlur
contentBlur.addEventListener("click", function () {
  closeAllSidebars();
});
