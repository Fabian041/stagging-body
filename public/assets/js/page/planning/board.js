(() => {
  const html = document.documentElement;
  const btn  = document.getElementById('themeToggle');

  function apply(theme) {
    html.setAttribute('data-theme', theme);     // untuk CSS custom kita
    html.setAttribute('data-bs-theme', theme);  // agar Bootstrap ikut ganti
    localStorage.setItem('board-theme', theme);
    if (btn) btn.querySelector('span').textContent = theme === 'dark' ? 'Dark' : 'Light';
  }

  const saved = localStorage.getItem('board-theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  apply(saved || (prefersDark ? 'dark' : 'light'));

  btn?.addEventListener('click', () => {
    const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    apply(next);
  });
})();

(function backnoAliasBoard(){
  const LS_KEY = 'backnoRenameMap';
  let map = {};
  try { map = JSON.parse(localStorage.getItem(LS_KEY) || '{}'); } catch {}
  const fallback = { 'D403':'CI18', 'D111':'CI12', 'D500':'CI19' };
  const aliasMap = Object.assign({}, fallback, map);

  document.querySelectorAll('.js-backno').forEach(el=>{
    const raw = (el.textContent || '').trim().toUpperCase();
    const alias = aliasMap[raw];
    if (alias) el.textContent = alias;
  });
})();