// script.js
document.addEventListener('DOMContentLoaded', () => {
  const tracks = document.querySelectorAll('.progress-track');

  tracks.forEach(track => {
    const target = parseFloat(track.getAttribute('data-target') || '0'); // percent target
    const fill = track.querySelector('.progress-inner-fill');
    const percentEl = track.parentElement.querySelector('.progress-percent');

    // small fade-in delay based on index for staggered animation
    const delay = Array.from(tracks).indexOf(track) * 150;

    // animate width using requestAnimationFrame for smoothness:
    setTimeout(() => {
      // update CSS width (transition in CSS handles animation)
      fill.style.width = target + '%';

      // animate numeric counter (0 -> target)
      let current = 0;
      const duration = 900; // ms
      const start = performance.now();
      const step = (now) => {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const eased = progress; // linear - could add easing
        const value = Math.round(eased * target);
        percentEl.textContent = value + '%';
        if (progress < 1) requestAnimationFrame(step);
        else percentEl.textContent = target + '%';
      };
      requestAnimationFrame(step);
    }, delay + 120);
  });
});
