// script.js (overwrite)
document.addEventListener('DOMContentLoaded', () => {

  /* -------------------------
     Progress tracker (unchanged)
     ------------------------- */
  const tracks = document.querySelectorAll('.progress-track');
  tracks.forEach((track, i) => {
    const target = parseFloat(track.getAttribute('data-target') || '0');
    const fill = track.querySelector('.progress-inner-fill');
    const percentEl = track.parentElement.querySelector('.progress-percent');
    const delay = i * 150;
    if (!fill || !percentEl) return;
    setTimeout(() => {
      fill.style.width = target + '%';
      const duration = 900;
      const start = performance.now();
      const step = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        percentEl.textContent = Math.round(progress * target) + '%';
        if (progress < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    }, delay + 120);
  });


  /* -------------------------
     Manga slider + story text
     ------------------------- */
  // limit scope to manga page
  const root = document.querySelector('.manga-page');
  if (!root) return;

  const slides = Array.from(root.querySelectorAll('.slides img'));
  const prevBtn = root.querySelector('.slide-btn.prev');
  const nextBtn = root.querySelector('.slide-btn.next');
  const enText = root.querySelector('.story-text .en');
  const jpText = root.querySelector('.story-text .jp');
  const romajiText = root.querySelector('.story-text .romaji');
  const dotsContainer = root.querySelector('.progress-dots');

  if (!slides.length || !prevBtn || !nextBtn || !enText || !jpText || !romajiText) {
    // nothing to do on other pages
    return;
  }

  const story = [
    { en: "One day, an egg had fallen.", jp: "あるひ、たまごがおちていました。", romaji: "Aru hi, tamago ga ochite imashita" },
    { en: "What kind of egg is it?", jp: "なんのたまごだろう？", romaji: "Nan no tamago darou?" },
    { en: "Ah! It looks like something is hatching!", jp: "なにかがうまれそうだ！", romaji: "Nani ka ga umare-sou da!" },
    { en: "Ahh! Something came out!", jp: "うわ！でてきた！", romaji: "Uwa! dete kita!" },
    { en: "What kind of living creature is this?", jp: "これはなんのいきものだろう？", romaji: "Kore wa nan no ikimono darou?" },
    { en: "The little creature looked around curiously.", jp: "ちいさないきものはきょろきょろとまわりをみました。", romaji: "Chiisana ikimono wa kyoro kyoro to mawari o mimashita." }
  ];

  // make dots UI (● ○ ○)
  function renderDots(activeIndex) {
    if (!dotsContainer) return;
    dotsContainer.innerHTML = '';
    for (let i = 0; i < slides.length; i++) {
      const dot = document.createElement('span');
      dot.textContent = i === activeIndex ? '●' : '○';
      dot.style.margin = '0 6px';
      dot.style.color = 'rgba(255,255,255,0.9)';
      dotsContainer.appendChild(dot);
    }
  }

  let current = 0;

  function updateSlide(i) {
    i = Math.max(0, Math.min(i, slides.length - 1));
    current = i;
    slides.forEach((s, idx) => s.classList.toggle('active', idx === i));
    sendMangaProgress();
    // story text: guard against story length mismatch
    slides.forEach((s, idx) => s.classList.toggle('active', idx === i));
    const sdata = story[i] || story[story.length - 1];
    enText.textContent = `“${sdata.en}”`;
    jpText.textContent = sdata.jp;
    romajiText.textContent = sdata.romaji;
    prevBtn.disabled = i === 0;
    nextBtn.disabled = i === slides.length - 1;
    renderDots(i);

        // --- Notify server once per page load that manga is being viewed ---
    if (!window.__mangaViewSent) {
        fetch('php/save_progress.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'manga_view' })
        }).catch(() => {});
        window.__mangaViewSent = true;
    }

}


  prevBtn.addEventListener('click', () => updateSlide(current - 1));
  nextBtn.addEventListener('click', () => updateSlide(current + 1));
  document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') prevBtn.click();
    if (e.key === 'ArrowRight') nextBtn.click();
  });

  updateSlide(0);
});
