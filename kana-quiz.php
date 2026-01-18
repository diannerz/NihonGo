<?php
// /NihonGo/kana-quiz.php
require __DIR__ . '/php/check_auth.php';
if (!$user) {
    header('Location: login.html');
    exit;
}
require __DIR__ . '/php/db.php';

$uid = (int) $_SESSION['user_id'];

// fetch both type-wide distinct mastered counts to show progress (we use both because quiz is mixed)
$hiraStmt = $pdo->prepare("SELECT COUNT(DISTINCT kana_char) as cnt FROM kana_progress WHERE user_id = :uid AND kana_type = 'hiragana' AND mastery_level = 2");
$hiraStmt->execute([':uid'=>$uid]);
$hiraCount = (int)$hiraStmt->fetchColumn();
$hiraCount = min($hiraCount, 46);
$hiraPct = (int) round(($hiraCount / 46) * 100);

$kataStmt = $pdo->prepare("SELECT COUNT(DISTINCT kana_char) as cnt FROM kana_progress WHERE user_id = :uid AND kana_type = 'katakana' AND mastery_level = 2");
$kataStmt->execute([':uid'=>$uid]);
$kataCount = (int)$kataStmt->fetchColumn();
$kataCount = min($kataCount, 46);
$kataPct = (int) round(($kataCount / 46) * 100);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Kana Quiz — NihonGo</title>
  <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" rel="stylesheet">
  <style>
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 18px;
      background-color: #6b9aa6;
    }
    .topbar-left { display:flex; align-items:center; gap:12px; }
    .topbar-right { display:flex; align-items:center; gap:14px; }
    .topbar img { height:40px; cursor:pointer; }
    body { font-family: 'Kosugi Maru', sans-serif; background:#cce7e8; color:#1e2f30; margin:0; }
    .container{max-width:980px;margin:40px auto;padding:20px}
    .card{background:#76939b;border-radius:16px;padding:20px;color:#eef7f6}
    .heading{font-size:1.6rem;font-weight:bold;margin-bottom:12px}
    .small{font-size:.9rem;color:#eaf7f6}
    .quiz-area{display:flex;gap:20px;align-items:flex-start;margin-top:18px}
    .left{flex:1}
    .right{width:320px;flex:0 0 auto}
    .kana-char{font-size:140px;text-align:center;margin:18px 0}
    .question-box{background:#2f6f73;padding:14px;border-radius:12px}
    .choices{display:flex;flex-direction:column;gap:10px;margin-top:12px}
    .choice{background:#eaf7f6;color:#274043;padding:10px 12px;border-radius:10px;border:none;cursor:pointer;text-align:left}
    .choice.correct{outline:3px solid #6ee06e}
    .choice.wrong{opacity:.5}
    .btn{background:#ffd24a;border:none;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:bold}
    .progress-track{background:#d8eae9;border-radius:12px;height:18px;overflow:hidden;margin-top:10px}
    .fill{height:100%;background:linear-gradient(90deg,#60a6a9,#2f6f73);width:0%;transition:width .4s}
    .label{margin-top:6px;text-align:right;color:#eaf7f6;font-weight:bold}
    .stat{background:#55767d;padding:12px;border-radius:10px;text-align:center}
    .muted{font-size:.85rem;color:#daf3ef;margin-top:6px}
  </style>
</head>

<body>

<!-- TOP BAR: home on left, icons on right -->
<div class="topbar" role="navigation" aria-label="topbar">
  <div class="topbar-left">
    <a href="dashboard.php" title="Back to dashboard">
      <img src="images/home.png" alt="Home">
    </a>
  </div>

  <div class="topbar-right" aria-hidden="false">
    <img src="images/exit.png" alt="exit" id="exitBtn" title="Log out">
    <img src="images/setting.png" alt="gear" id="settingsBtn" title="Settings">
    <img src="images/profile.png" alt="profile" id="profileBtn" title="Profile">
  </div>
</div>

<div class="container">
  <div class="card">
    <div class="heading">Quick quiz — verify kana recognition</div>
    <div class="small">Correct answers mark that kana as <strong>Mastered</strong> and will count toward your "<em>My progress</em>" total. When you finish the quiz we also increment today's daily quiz counter.</div>

    <div class="quiz-area">
      <div class="left">
        <div class="question-box">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div>Question <span id="qIndex">1</span>/<span id="qTotal">10</span></div>
            <div class="small" id="scoreText">Score: 0</div>
          </div>

          <div id="kanaChar" class="kana-char" aria-live="polite">あ</div>

          <div class="choices" id="choices" role="list"></div>

          <div style="display:flex;justify-content:space-between;margin-top:14px">
            <button id="skipBtn" class="btn" style="background:#2f6f73;color:#eaf7f6">Skip</button>
            <button id="nextBtn" class="btn">Next</button>
          </div>
        </div>
      </div>

      <div class="right">
        <div class="stat">
          <div style="font-size:1.1rem;font-weight:bold" id="progressTitle">Script progress</div>
          <div style="font-size:2.4rem;margin-top:6px" id="typeCount"><?= $hiraCount ?>/46</div>
          <div class="muted" id="typePct"><?= $hiraPct ?>%</div>

          <div style="margin-top:12px">
            <div class="progress-track"><div id="typeFill" class="fill" style="width:<?= $hiraPct ?>%"></div></div>
            <div class="label" id="typeLabelText"><?= $hiraCount ?>/46 — <?= $hiraPct ?>%</div>
          </div>

          <div class="muted" style="margin-top:8px">Correct answers will set mastery for the kana.</div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
/* ---------- full kana lists extracted from your provided pages ---------- */

/* We'll build flat arrays from your page-style data (keeps layout identical to your uploading style) */
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

/* ---------- seeded rng (so daily selection is deterministic) ----------
   We'll seed from today's date (YYYY-MM-DD) so the same quiz set is used for everyone each day.
*/
function xfnv1a(str) { // hash -> 32-bit
  let h = 2166136261 >>> 0;
  for (let i = 0; i < str.length; i++) {
    h ^= str.charCodeAt(i);
    h = Math.imul(h, 16777619);
  }
  return h >>> 0;
}
function mulberry32(a) {
  return function() {
    a |= 0; a = a + 0x6D2B79F5 | 0;
    let t = Math.imul(a ^ a >>> 15, 1 | a);
    t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
    return ((t ^ t >>> 14) >>> 0) / 4294967296;
  }
}
function seededShuffle(arr, seedStr) {
  const seed = xfnv1a(seedStr);
  const rand = mulberry32(seed);
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(rand() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
  return arr;
}

/* -------------- configuration -------------- */
// mixed pool
const mixedPool = [...hiraData, ...kataData];

// daily seed: date only
const today = new Date().toISOString().slice(0,10); // YYYY-MM-DD
const seededPool = seededShuffle(mixedPool.slice(), today); // deterministic per day

const totalQuestions = 10; // number of questions per quiz
let quizList = []; // final quiz items
let current = 0;
let score = 0;
let answeredThis = false;

// inject initial counts from PHP (use let so we can update them later)
let initialHiraCount = <?= json_encode($hiraCount) ?>;
let initialKataCount = <?= json_encode($kataCount) ?>;

/* ------------------ helpers ------------------ */
function shuffleWithSeedless(arr) { // small local shuffle for choices
  for (let i = arr.length-1; i>0; i--) {
    const j = Math.floor(Math.random()*(i+1));
    [arr[i],arr[j]] = [arr[j],arr[i]];
  }
  return arr;
}
function isKatakana(ch) {
  if (!ch) return false;
  const code = ch.charCodeAt(0);
  return (code >= 0x30A0 && code <= 0x30FF); // Katakana block
}
function isHiragana(ch) {
  if (!ch) return false;
  const code = ch.charCodeAt(0);
  return (code >= 0x3040 && code <= 0x309F); // Hiragana block
}

/* Build quizList: deterministic daily selection (seededPool) */
function buildQuiz() {
  const take = Math.min(totalQuestions, seededPool.length);
  quizList = seededPool.slice(0, take).map(item => {
    // build 3 distractors from same script (prefer same script for plausible choices)
    const sameScriptPool = (item.script === 'hiragana') ? hiraData : kataData;
    const otherPool = sameScriptPool.filter(p => p.kana !== item.kana);
    shuffleWithSeedless(otherPool);
    const distractors = [];
    for (let i = 0; i < 3 && i < otherPool.length; i++) distractors.push(otherPool[i].romaji);
    const choices = shuffleWithSeedless([item.romaji, ...distractors]);
    return { kana: item.kana, romaji: item.romaji, script: item.script, choices };
  });
  document.getElementById('qTotal').textContent = quizList.length;
}

/* Render question & update right-side progress for this script */
function renderQuestion() {
  const q = quizList[current];
  document.getElementById('qIndex').textContent = current + 1;
  document.getElementById('kanaChar').textContent = q.kana;
  document.getElementById('scoreText').textContent = `Score: ${score}`;
  const choicesEl = document.getElementById('choices');
  choicesEl.innerHTML = '';

  q.choices.forEach(choice => {
    const btn = document.createElement('button');
    btn.className = 'choice';
    btn.type = 'button';
    btn.textContent = choice;
    btn.dataset.val = choice;
    btn.addEventListener('click', () => handleChoice(btn, q));
    choicesEl.appendChild(btn);
  });

  // update right panel to show script-specific progress
  if (q.script === 'katakana') {
    document.getElementById('progressTitle').textContent = 'Katakana progress';
    updateRightPanel(initialKataCount);
  } else {
    document.getElementById('progressTitle').textContent = 'Hiragana progress';
    updateRightPanel(initialHiraCount);
  }
}

/* Update right panel UI given a numeric count */
function updateRightPanel(count) {
  const capped = Math.min(46, parseInt(count) || 0);
  const pct = Math.round((capped/46)*100);
  document.getElementById('typeCount').textContent = `${capped}/46`;
  document.getElementById('typePct').textContent = `${pct}%`;
  document.getElementById('typeLabelText').textContent = `${capped}/46 — ${pct}%`;
  document.getElementById('typeFill').style.width = pct + '%';
}

/* handle choice click */
function handleChoice(btn, q) {
  if (answeredThis) return; // prevent double click
  answeredThis = true;
  const val = btn.dataset.val;
  const correct = val === q.romaji;
  if (correct) {
    btn.classList.add('correct');
    score++;
    // determine kana type
    const kanaType = q.script === 'katakana' ? 'katakana' : 'hiragana';
    // call server to mark this kana as mastered
    fetch('php/save_progress.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ kana: q.kana, type: kanaType, action: 'master' })
    })
    .then(r => r.json())
    .then(data => {
      // update local counters if server returned type_count for that type
      if (data && data.type_count !== undefined) {
        if (kanaType === 'hiragana') {
          initialHiraCount = Math.min(46, parseInt(data.type_count) || 0);
          if (q.script === 'hiragana') updateRightPanel(initialHiraCount);
        } else {
          initialKataCount = Math.min(46, parseInt(data.type_count) || 0);
          if (q.script === 'katakana') updateRightPanel(initialKataCount);
        }
      }
    }).catch(()=>{ /* ignore errors silently for now */ });
  } else {
    btn.classList.add('wrong');
    // highlight correct choice
    const all = document.querySelectorAll('.choice');
    all.forEach(b => {
      if (b.dataset.val === q.romaji) b.classList.add('correct');
    });
  }
}

/* finish quiz: notify server quiz_complete and show results */
function finishQuiz() {
  fetch('php/save_progress.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ action: 'quiz_complete' })
  })
  .then(r => r.json())
  .then(data => {
    alert(`Quiz finished. You scored ${score}/${quizList.length}.`);
    // redirect to dashboard so daily counters refresh
    window.location.href = 'dashboard.php';
  })
  .catch(()=> {
    alert(`Quiz finished. You scored ${score}/${quizList.length}. (network error)`);
    window.location.href = 'dashboard.php';
  });
}

/* next/skip handling and initialisation */
document.addEventListener('DOMContentLoaded', () => {
  // topbar icon listeners
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

  buildQuiz();
  renderQuestion();

  document.getElementById('nextBtn').addEventListener('click', () => {
    // REQUIRE an answer before moving on. Skip is the only way to advance without answering.
    if (!answeredThis) {
      alert('PLEASE CHOOSE AN ANSWER (or Skip if you dont know).');
      return;
    }
    // reset answered flag and advance
    answeredThis = false;
    current++;
    if (current >= quizList.length) { finishQuiz(); return; }
    renderQuestion();
  });

  document.getElementById('skipBtn').addEventListener('click', () => {
    // skip explicitly allowed (no answer required)
    answeredThis = false;
    current++;
    if (current >= quizList.length) { finishQuiz(); return; }
    renderQuestion();
  });
});
</script>

</body>
</html>
