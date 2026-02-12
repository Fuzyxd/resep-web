// Main site JS
document.addEventListener('DOMContentLoaded', () => {
  const navbarSearch = document.getElementById('navbarSearch');
  const navbarSearchToggle = document.getElementById('navbarSearchToggle');
  const searchInput = document.getElementById('searchInput');

  if (navbarSearch && navbarSearchToggle) {
    navbarSearchToggle.addEventListener('click', () => {
      const isOpen = navbarSearch.classList.toggle('open');
      if (isOpen && searchInput) {
        searchInput.focus();
      }
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 768) {
        navbarSearch.classList.remove('open');
      }
    });
  }
});
