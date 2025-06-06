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
