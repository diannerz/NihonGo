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
/* (Use your existing styles) */
:root{--teal:#244e53;--mint:#dffbf7;--panel:#8ea6ab;--panel-inner:#91a3ad;--text:#244850;--white:#fff;--cell-h:120px}
*{box-sizing:border-box}html,body{margin:0;padding:0;background:var(--mint);font-family:"Noto Sans JP","Yu Gothic","Segoe UI",Roboto,Arial,sans-serif;color:var(--text)}
.topbar{height:72px;background:#7aa5a8;display:flex;align-items:center;justify-content:space-between;padding:8px 36px}
.topbar-left{display:flex;align-items:center;gap:12px}
.topbar-left a{display:flex;align-items:center;gap:10px;color:var(--white);font-weight:900;font-size:22px}
.topbar-left img{width:64px;height:auto}
.topbar-right img{width:64px;margin-left:12px}
.page{padding:28px 48px;display:flex;flex-direction:column;gap:26px}
.chart-panel{background:var(--panel);border-radius:48px;padding:36px 42px;box-shadow:0 12px 30px rgba(0,0,0,.06)}
.chart-inner{background:var(--panel-inner);border-radius:36px;padding:24px;display:flex;flex-direction:column;gap:16px}
.chart-header{display:flex;justify-content:center;align-items:center;gap:24px}
.tabs{display:flex;gap:18px;align-items:center}
.tab{font-weight:900;font-size:32px;color:rgba(255,255,255,.6);cursor:pointer;padding-bottom:6px}
.tab.active{color:var(--white);border-bottom:4px solid rgba(255,255,255,.15)}
.nav-arrows{display:flex;justify-content:center;align-items:center;gap:32px}
.nav-arrows button{width:48px;height:48px;border-radius:50%;border:none;font-size:26px;cursor:pointer;background:rgba(255,255,255,.3);color:#0b2426}
.chart-grid{display:grid;grid-template-columns:80px repeat(5,1fr);gap:12px;background:rgba(255,255,255,.05);padding:20px;border-radius:28px}
.chart-grid .header{font-weight:800;text-align:center;color:rgba(255,255,255,.9);font-size:20px;text-decoration:underline;text-underline-offset:8px}
.consonant{display:flex;align-items:center;justify-content:center;font-weight:900;color:rgba(255,255,255,.95);font-size:20px}
.cell{min-height:var(--cell-h);display:flex;flex-direction:column;align-items:center;justify-content:flex-start;gap:8px}
.big{font-size:36px;font-weight:900;color:#0b2426}
.small{font-size:14px;color:rgba(0,0,0,.6)}
.chart-grid> *{align-self:start}
.chart-grid .consonant{align-self:start;transform:translateY(-10px)!important;margin:0;padding-top:2px}
.chart-grid .header{padding-top:6px}
.kana-page .chart-grid .consonant{transform:translateY(-10px)!important}
</style>
</head>
<body>

<div class="topbar">
  <div class="topbar-left">
    <a href="dashboard.php">
      <img src="/NihonGo/images/home.png" alt="home">
      <span>Back to Dashboard</span>
    </a>
  </div>
  <div class="topbar-right">
    <img src="/NihonGo/images/exit.png" alt="logout">
    <img src="/NihonGo/images/setting.png" alt="settings">
    <img src="/NihonGo/images/profile.png" alt="profile">
  </div>
</div>

<div class="page">
  <div class="chart-panel">
    <div class="chart-inner">
      <div class="chart-header">
        <div class="tabs">
          <div id="tab-hira" class="tab active" data-mode="hiragana">Hiragana</div>
          <div id="tab-kata" class="tab" data-mode="katakana">Katakana</div>
        </div>
      </div>

      <div class="nav-arrows">
        <button id="prevBtn">◀</button>
        <button id="nextBtn">▶</button>
      </div>

      <div class="chart-grid" id="chartGrid"></div>
    </div>
  </div>
</div>

<script>
// pages data (kept small here; use full dataset if you want)
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

          // Open flashcard with kana + type param
          big.style.cursor = 'pointer';
          big.addEventListener('click', () => {
            const url = 'kana-flashcards.php?kana=' + encodeURIComponent(kana) + '&type=' + encodeURIComponent(mode);
            window.location.href = url;
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

  prevBtn.disabled = pageIndex === 0;
  nextBtn.disabled = pageIndex === (mode === 'hiragana' ? hiraPages.length-1 : kataPages.length-1);
  prevBtn.style.opacity = prevBtn.disabled ? 0.35 : 1;
  nextBtn.style.opacity = nextBtn.disabled ? 0.35 : 1;
}

tabHira.onclick = () => { mode = 'hiragana'; pageIndex = 0; render(); };
tabKata.onclick = () => { mode = 'katakana'; pageIndex = 0; render(); };
prevBtn.onclick = () => { if (pageIndex > 0) pageIndex--; render(); };
nextBtn.onclick = () => { const pages = mode === 'hiragana' ? hiraPages : kataPages; if (pageIndex < pages.length - 1) pageIndex++; render(); };

document.addEventListener('keydown', e => {
  if (e.key === 'ArrowLeft') prevBtn.click();
  if (e.key === 'ArrowRight') nextBtn.click();
});

render();
</script>
</body>
</html>
