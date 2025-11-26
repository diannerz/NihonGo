<?php
// kana-charts.php — protected page
require __DIR__ . '/php/check_auth.php';
if (!$user) {
    header('Location: login.html');
    exit;
}
?>
<!doctype html>
<html lang="en" class="kana-page">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>NihonGo — Kana Charts</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700;900&display=swap" rel="stylesheet">
<style>
/* ---------------- variables ---------------- */
:root{
  --teal:#244e53;
  --mint:#dffbf7;
  --panel:#8ea6ab;
  --panel-inner:#91a3ad;
  --text:#244850;
  --white:#fff;
  --cell-h:120px;
  --accent: #7aa5a8;
  --hover-glow: rgba(255,255,255,0.15);
  --kana-hover-bg: rgba(255,255,255,0.92);
}

/* ---------------- reset & base ---------------- */
*{box-sizing:border-box}
html,body{margin:0;padding:0;background:var(--mint);font-family:"Noto Sans JP","Yu Gothic","Segoe UI",Roboto,Arial,sans-serif;color:var(--text);-webkit-font-smoothing:antialiased}
a{color:inherit;text-decoration:none}

/* ---------------- topbar ---------------- */
.topbar{
  height:72px;
  background:var(--accent);
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:8px 36px;
  box-shadow: 0 4px 18px rgba(0,0,0,0.06);
}

.topbar-left {
  display:flex;
  align-items:center;
  gap:14px;
}

.topbar-left a {
  display:flex;
  align-items:center;
  gap:12px;
  color:var(--white);
  line-height:1;
  padding:6px 10px;
  border-radius:10px;
  transition: background 0.18s ease, transform 0.18s ease;
}
.topbar-left a:hover {
  background: rgba(255,255,255,0.04);
  transform: translateY(-2px);
}

.topbar-left img{
  width:86px;
  height:auto;
  display:block;
  filter: drop-shadow(0 6px 8px rgba(0,0,0,0.12));
  border-radius:6px;
}

.header-text-inline {
  display:flex;
  flex-direction:column;
  justify-content:center;
  gap:2px;
}

.header-text-inline .title {
  font-size:28px;
  font-weight:900;
  color: #244e53;
  letter-spacing:1px;
  margin:0;
}

.header-text-inline .subtitle {
  font-size:12.5px;
  color: rgba(0,0,0,0.45);
  margin-top:0;
}

.topbar-right img{
  width:64px;
  margin-left:12px;
  height:auto;
  cursor:pointer;
  filter: drop-shadow(0 4px 6px rgba(0,0,0,0.08));
  transition: transform .14s ease;
}
.topbar-right img:hover{ transform: translateY(-2px) scale(1.03); }

/* ---------------- page layout ---------------- */
.page{padding:28px 48px;display:flex;flex-direction:column;gap:26px}
.chart-panel{background:var(--panel);border-radius:48px;padding:36px 42px;box-shadow:0 12px 30px rgba(0,0,0,.06)}
.chart-inner{background:var(--panel-inner);border-radius:36px;padding:24px;display:flex;flex-direction:column;gap:16px}
.chart-header{display:flex;justify-content:center;align-items:center;gap:24px}
.tabs{display:flex;gap:18px;align-items:center}
.tab{font-weight:900;font-size:32px;color:rgba(255,255,255,.6);cursor:pointer;padding-bottom:6px;transition:color .16s ease}
.tab.active{color:var(--white);border-bottom:4px solid rgba(255,255,255,.15)}
.nav-arrows{display:flex;justify-content:center;align-items:center;gap:32px}
.nav-arrows button{width:48px;height:48px;border-radius:50%;border:none;font-size:26px;cursor:pointer;background:rgba(255,255,255,.3);color:#0b2426;transition:transform .12s ease}
.nav-arrows button:hover{transform:translateY(-4px)}
.nav-arrows button:disabled{opacity:.35; cursor:default; transform:none}

/* ---------------- FIXED ROW ALIGNMENT GRID ---------------- */
.chart-grid{
  display:grid;
  grid-template-columns:100px repeat(5,1fr);
  gap:12px;
  background:rgba(255,255,255,.05);
  padding:20px;
  border-radius:28px;

  /* Ensures every row INCLUDING consonant row is identical height */
  grid-auto-rows: var(--cell-h);
}

/* top row headers */
.chart-grid .header{
  font-weight:800;
  text-align:center;
  color:rgba(255,255,255,.9);
  font-size:20px;
  text-decoration:underline;
  text-underline-offset:8px;

  display:flex;
  justify-content:center;
  align-items:center;
  height:var(--cell-h);
}

/* left consonant column FIXED */
.consonant{
  display:flex;
  align-items:center;
  justify-content:center;
  font-weight:900;
  color:rgba(255,255,255,.95);
  font-size:20px;
  margin:0;
  padding:0;
  transform:none !important;
}

/* kana cells */
.cell{
  height:var(--cell-h);
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:8px;
  border-radius:14px;
  transition:transform .18s cubic-bezier(.2,.9,.2,1),
             box-shadow .18s,
             background .18s;
}

.cell .big{font-size:42px;font-weight:900;color:#0b2426}
.cell .small{font-size:14px;color:rgba(0,0,0,.6)}

/* ---------------- hover / focus animations ---------------- */
.cell:hover{
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 18px 36px rgba(0,0,0,0.12);
  background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(250,250,250,0.9));
}
.cell:hover .big{
  transform: scale(1.18) rotate(-1deg);
  text-shadow: 0 10px 22px rgba(122,165,168,0.22), 0 2px 6px rgba(0,0,0,0.12);
  color: #184443;
}

.cell:focus-within, .cell:focus {
  outline: none;
  box-shadow: 0 18px 36px rgba(0,0,0,0.14), 0 0 0 6px rgba(122,165,168,0.06);
  transform: translateY(-8px) scale(1.02);
}

.big[tabindex] { outline: none; }

/* ---------------- responsive ---------------- */
@media (max-width:1100px){
  .page{padding:18px}
  .chart-grid{grid-template-columns:80px repeat(5,1fr)}
  .cell .big{font-size:32px}
  .topbar-left img{width:70px}
  .header-text-inline .title{font-size:20px}
  .header-text-inline .subtitle{font-size:11px}
}

@media (max-width:720px){
  .chart-grid{grid-template-columns:60px repeat(5,1fr); gap:8px; padding:12px}
  .cell .big{font-size:26px}
  .topbar{padding:8px 14px}
  .topbar-left img{width:56px}
  .header-text-inline .title{font-size:18px}
  .header-text-inline .subtitle{display:none}
}

</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-left">
    <!-- anchor still links to dashboard, but now has the "reference" style -->
    <a href="dashboard.php" aria-label="Back to Dashboard">
      <img src="/NihonGo/images/home.png" alt="home">
      <div class="header-text-inline" aria-hidden="false">
        <div class="title">Kana Charts</div>
        <div class="subtitle">Click on a black character to view its flashcard</div>
      </div>
    </a>
  </div>

  <div class="topbar-right" role="toolbar" aria-label="Topbar actions">
    <img src="/NihonGo/images/exit.png" alt="logout" title="Log out" id="exitBtn">
    <img src="/NihonGo/images/setting.png" alt="settings" title="Settings" id="settingsBtn">
    <img src="/NihonGo/images/profile.png" alt="profile" title="Profile" id="profileBtn">
  </div>
</div>

<div class="page">
  <div class="chart-panel">
    <div class="chart-inner">
      <div class="chart-header">
        <div class="tabs" role="tablist" aria-label="Kana types">
          <div id="tab-hira" class="tab active" data-mode="hiragana" role="tab" aria-selected="true">Hiragana</div>
          <div id="tab-kata" class="tab" data-mode="katakana" role="tab" aria-selected="false">Katakana</div>
        </div>
      </div>

      <div class="nav-arrows" aria-hidden="false">
        <button id="prevBtn" aria-label="Previous page">◀</button>
        <button id="nextBtn" aria-label="Next page">▶</button>
      </div>

      <div class="chart-grid" id="chartGrid" role="grid" aria-readonly="true"></div>
    </div>
  </div>
</div>

<script>
/* ---------- data ---------- */
const hiraPages = [
  [
    ["", "~a","~i","~u","~e","~o"],
    ["", ["あ","a"],["い","i"],["う","u"],["え","e"],["お","o"]],
    ["k~", ["か","ka"],["き","ki"],["く","ku"],["け","ke"],["こ","ko"]],
    ["s~", ["さ","sa"],["し","shi"],["す","su"],["せ","se"],["そ","so"]],
    ["t~", ["た","ta"],["ち","chi"],["つ","tsu"],["て","te"],["と","to"]],
    ["n~", ["な","na"],["に","ni"],["ぬ","nu"],["ね","ne"],["の","no"]],
    ["h~", ["は","ha"],["ひ","hi"],["ふ","fu"],["へ","he"],["ほ","ho"]],
  ],
  [
    ["", "~a","~i","~u","~e","~o"],
    ["m~", ["ま","ma"],["み","mi"],["む","mu"],["め","me"],["も","mo"]],
    ["y~", ["や","ya"],["ゆ","yu"],["よ","yo"],["",""],["",""]],
    ["r~", ["ら","ra"],["り","ri"],["る","ru"],["れ","re"],["ろ","ro"]],
    ["w~", ["わ","wa"],["",""],["",""],["",""],["を","wo"]],
    ["", ["ん","n"],["",""],["",""],["",""],["",""]],
  ]
];

const kataPages = [
  [
    ["", "~a","~i","~u","~e","~o"],
    ["", ["ア","a"],["イ","i"],["ウ","u"],["エ","e"],["オ","o"]],
    ["k~", ["カ","ka"],["キ","ki"],["ク","ku"],["ケ","ke"],["コ","ko"]],
    ["s~", ["サ","sa"],["シ","shi"],["ス","su"],["セ","se"],["ソ","so"]],
    ["t~", ["タ","ta"],["チ","chi"],["ツ","tsu"],["テ","te"],["ト","to"]],
    ["n~", ["ナ","na"],["ニ","ni"],["ヌ","nu"],["ネ","ne"],["ノ","no"]],
    ["h~", ["ハ","ha"],["ヒ","hi"],["フ","fu"],["ヘ","he"],["ホ","ho"]],
  ],
  [
    ["", "~a","~i","~u","~e","~o"],
    ["m~", ["マ","ma"],["ミ","mi"],["ム","mu"],["メ","me"],["モ","mo"]],
    ["y~", ["ヤ","ya"],["ユ","yu"],["ヨ","yo"],["",""],["",""]],
    ["r~", ["ラ","ra"],["リ","ri"],["ル","ru"],["レ","re"],["ロ","ro"]],
    ["w~", ["ワ","wa"],["",""],["",""],["",""],["ヲ","wo"]],
    ["", ["ン","n"],["",""],["",""],["",""],["",""]],
  ]
];

let mode = "hiragana";
let pageIndex = 0;

const chartGrid = document.getElementById('chartGrid');
const tabHira = document.getElementById('tab-hira');
const tabKata = document.getElementById('tab-kata');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

/* render grid rows/cols from page data */
function render() {
  const pages = mode === 'hiragana' ? hiraPages : kataPages;
  const data = pages[pageIndex] || [];
  chartGrid.innerHTML = '';

  data.forEach((row, r) => {
    row.forEach((col, c) => {
      const div = document.createElement('div');

      if (r === 0) {
        div.className = 'header';
        div.textContent = col;
      } else if (c === 0) {
        div.className = 'consonant';
        div.textContent = col;
      } else {
        div.className = 'cell';
        const [kana, romaji] = col;
        if (kana) {
          const big = document.createElement('div');
          big.className = 'big';
          big.textContent = kana;
          // make keyboard-focusable & screen-reader friendly
          big.setAttribute('role','button');
          big.setAttribute('tabindex','0');
          big.setAttribute('aria-label', `${kana} — ${romaji}`);

          // click => open flashcard with kana + type param
          const openFlashcard = () => {
            const url = 'kana-flashcards.php?kana=' + encodeURIComponent(kana) + '&type=' + encodeURIComponent(mode);
            window.location.href = url;
          };
          big.addEventListener('click', openFlashcard);
          big.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openFlashcard(); }
          });

          const small = document.createElement('div');
          small.className = 'small';
          small.textContent = romaji;
          div.append(big, small);
        }
      }
      chartGrid.appendChild(div);
    });
  });

  tabHira.classList.toggle('active', mode === 'hiragana');
  tabKata.classList.toggle('active', mode === 'katakana');

  // update nav buttons
  prevBtn.disabled = pageIndex === 0;
  const maxIndex = (mode === 'hiragana' ? hiraPages.length-1 : kataPages.length-1);
  nextBtn.disabled = pageIndex === maxIndex;
  prevBtn.style.opacity = prevBtn.disabled ? 0.35 : 1;
  nextBtn.style.opacity = nextBtn.disabled ? 0.35 : 1;

  // update aria-selected for tabs
  tabHira.setAttribute('aria-selected', mode === 'hiragana' ? 'true' : 'false');
  tabKata.setAttribute('aria-selected', mode === 'katakana' ? 'true' : 'false');
}

tabHira.onclick = () => { mode = 'hiragana'; pageIndex = 0; render(); };
tabKata.onclick = () => { mode = 'katakana'; pageIndex = 0; render(); };
prevBtn.onclick = () => { if (pageIndex > 0) pageIndex--; render(); };
nextBtn.onclick = () => { const pages = mode === 'hiragana' ? hiraPages : kataPages; if (pageIndex < pages.length - 1) pageIndex++; render(); };

document.addEventListener('keydown', e => {
  if (e.key === 'ArrowLeft') prevBtn.click();
  if (e.key === 'ArrowRight') nextBtn.click();
});

/* topbar icons behaviour */
document.getElementById('exitBtn').addEventListener('click', () => {
  if (!confirm('Log out?')) return;
  window.location.href = 'php/logout.php';
});
document.getElementById('settingsBtn').addEventListener('click', () => {
  window.location.href = 'settings.html';
});
document.getElementById('profileBtn').addEventListener('click', () => {
  window.location.href = 'dashboard.php';
});

render();
</script>
</body>
</html>
