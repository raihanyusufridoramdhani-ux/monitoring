document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const text = btn.dataset.copy || '';
      try {
        await navigator.clipboard.writeText(text);
        const old = btn.textContent; btn.textContent = 'Tersalin';
        setTimeout(() => btn.textContent = old, 1400);
      } catch { alert('Clipboard tidak tersedia. Salin teks secara manual.'); }
    });
  });
  document.querySelectorAll('[data-template]').forEach(sel => {
    sel.addEventListener('change', () => {
      const target = document.querySelector(sel.dataset.target);
      if (target && sel.value) target.value = sel.options[sel.selectedIndex].dataset.description || '';
    });
  });
});
