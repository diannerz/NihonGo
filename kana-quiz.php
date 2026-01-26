<?php
require __DIR__ . '/php/check_auth.php';
if (!$user) {
    header('Location: login.html');
    exit;
}
require __DIR__ . '/php/db.php';

$uid = (int) $_SESSION['user_id'];

/* Initial progress counts */
$hiraStmt = $pdo->prepare("
  SELECT COUNT(DISTINCT kana_char)
  FROM kana_progress
  WHERE user_id=:uid AND kana_type='hiragana' AND mastery_level=2
");
$hiraStmt->execute([':uid'=>$uid]);
$hiraCount = min((int)$hiraStmt->fetchColumn(), 46);

$kataStmt = $pdo->prepare("
  SELECT COUNT(DISTINCT kana_char)
  FROM kana_progress
  WHERE user_id=:uid AND kana_type='katakana' AND mastery_level=2
");
$kataStmt->execute([':uid'=>$uid]);
$kataCount = min((int)$kataStmt->fetchColumn(), 46);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kana Quiz — NihonGo</title>

<link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" rel="stylesheet">

<style>
body{margin:0;font-family:'Kosugi Maru',sans-serif;background:#cce7e8;color:#1e2f30}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:10px 18px;background:#7aa5a8;border-bottom:4px solid #4d7d86}
.topbar-left{display:flex;align-items:center;gap:14px}
.topbar-left img{height:52px}
.header-text{display:flex;flex-direction:column;line-height:1.1}
.header-text .title{font-size:1.6rem;font-weight:800;color:#fff}
.header-text .subtitle{font-size:.85rem;color:#eaf5f6}
.topbar-right{display:flex;gap:14px}
.topbar-right img{height:58px;cursor:pointer}

.container{max-width:1280px;margin:40px auto;padding:20px}
.card{background:#76939b;border-radius:16px;padding:32px;color:#eef7f6}
.quiz-area{display:flex;gap:30px}
.left{flex:1}
.right{width:380px}
.question-box{background:#2f6f73;padding:28px;border-radius:14px}
.kana-char{font-size:140px;text-align:center;margin:18px 0}
.choices{display:flex;flex-direction:column;gap:10px}
.choice{background:#eaf7f6;color:#274043;padding:10px;border-radius:10px;border:none;cursor:pointer}
.choice.correct{outline:3px solid #6ee06e}
.choice.wrong{opacity:.5}
.btn{background:#ffd24a;border:none;border-radius:10px;padding:10px 14px;font-weight:bold}
.stat{background:#55767d;padding:12px;border-radius:12px;text-align:center}
.progress-track{background:#d8eae9;border-radius:12px;height:18px;overflow:hidden;margin-top:10px}
.fill{height:100%;background:linear-gradient(90deg,#60a6a9,#2f6f73);width:0%}
.label{margin-top:6px;font-weight:bold;text-align:right}
</style>
</head>

<body>

<div class="topbar">
  <div class="topbar-left">
    <a href="dashboard.php"><img src="images/home.png"></a>
    <div class="header-text">
      <div class="title">Quick quiz</div>
      <div class="subtitle">Verify kana recognition</div>
    </div>
  </div>
  <div class="topbar-right">
    <img src="images/exit.png" id="exitBtn">
    <img src="images/profile.png" id="settingsBtn">
    <img src="images/donations.png" id="profileBtn">
  </div>
</div>

<div class="container">
<div class="card">
<div class="quiz-area">

<div class="left">
<div class="question-box">
  <div style="display:flex;justify-content:space-between">
    <div>Question <span id="qIndex">1</span>/<span id="qTotal">10</span></div>
    <div id="scoreText">Score: 0</div>
  </div>

  <div id="kanaChar" class="kana-char"></div>
  <div id="choices" class="choices"></div>

  <div style="display:flex;justify-content:space-between;margin-top:14px">
    <button id="skipBtn" class="btn" style="background:#2f6f73;color:#fff">Skip</button>
    <button id="nextBtn" class="btn">Next</button>
  </div>
</div>
</div>

<div class="right">
<div class="stat">
  <div id="progressTitle"></div>
  <div id="typeCount"></div>
  <div id="typePct"></div>
  <div class="progress-track"><div id="typeFill" class="fill"></div></div>
  <div class="label" id="typeLabelText"></div>
</div>
</div>

</div>
</div>
</div>

<script>
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

/* helper to flatten pages into [{kana,romaji,script}] */
function flattenPages(pages, script) {
  const out = [];
  for (const page of pages) {
    for (let r = 1; r < page.length; r++) { // skip header row (index 0)
      const row = page[r];
      for (let c = 1; c < row.length; c++) { // skip row header
        const cell = row[c];
        if (Array.isArray(cell) && cell[0]) {
          out.push({ kana: cell[0], romaji: cell[1], script });
        }
      }
    }
  }
  return out;
}

const hiraData = flattenPages(hiraPages, 'hiragana');
const kataData = flattenPages(kataPages, 'katakana');

const pool = [...hiraData, ...kataData];

let quiz = [];
let current = 0;
let score = 0;
let answered = false;

let hiraCount = <?= $hiraCount ?>;
let kataCount = <?= $kataCount ?>;

function updatePanel(script) {
  const count = script === 'katakana' ? kataCount : hiraCount;
  const pct = Math.round((count / 46) * 100);

  progressTitle.textContent =
    script === 'katakana' ? 'Katakana progress' : 'Hiragana progress';

  typeCount.textContent = `${count}/46`;
  typePct.textContent = `${pct}%`;
  typeLabelText.textContent = `${count}/46 — ${pct}%`;
  typeFill.style.width = pct + '%';
}

function buildQuiz() {
  quiz = pool.sort(() => 0.5 - Math.random()).slice(0, 10).map(q => {
    const src = q.script === 'hiragana' ? hiraData : kataData;
    const choices = [q.romaji,
      ...src.filter(x => x.kana !== q.kana).slice(0, 3).map(x => x.romaji)
    ].sort(() => 0.5 - Math.random());

    return { ...q, choices };
  });
  qTotal.textContent = quiz.length;
}

function render() {
  const q = quiz[current];
  qIndex.textContent = current + 1;
  kanaChar.textContent = q.kana;
  scoreText.textContent = `Score: ${score}`;
  updatePanel(q.script);

  choices.innerHTML = '';
  q.choices.forEach(choice => {
    const btn = document.createElement('button');
    btn.className = 'choice';
    btn.textContent = choice;
    btn.onclick = () => answer(btn, q);
    choices.appendChild(btn);
  });
}

function answer(btn, q) {
  if (answered) return;
  answered = true;

  if (btn.textContent === q.romaji) {
    btn.classList.add('correct');
    score++;

    fetch('php/save_progress.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ kana: q.kana, type: q.script, action: 'master' })
    })
    .then(r => r.json())
    .then(d => {
      if (q.script === 'hiragana') hiraCount = d.type_count;
      else kataCount = d.type_count;
      updatePanel(q.script);
    });

  } else {
    btn.classList.add('wrong');
    [...choices.children].forEach(b => {
      if (b.textContent === q.romaji) b.classList.add('correct');
    });
  }
}

nextBtn.onclick = () => {
  if (!answered) return alert('Choose an answer or Skip');
  answered = false;
  current++;
  if (current >= quiz.length) {
    fetch('php/save_progress.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'quiz_complete' })
    }).then(() => location.href='dashboard.php');
    return;
  }
  render();
};

skipBtn.onclick = () => {
  answered = false;
  current++;
  current < quiz.length ? render() : location.href='dashboard.php';
};

exitBtn.onclick = () => confirm('Log out?') && (location.href='php/logout.php');
settingsBtn.onclick = () => location.href='settings.php';
profileBtn.onclick = () => location.href = 'donation.php';

buildQuiz();
render();
</script>

</body>
</html>
