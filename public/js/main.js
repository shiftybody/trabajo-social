function goTo(url) {
  window.location.href = `../${url}`;
}

document.addEventListener('keydown', (e) => {
  if (e.key === '/') {
    document.querySelector('.search').focus();
  }
});