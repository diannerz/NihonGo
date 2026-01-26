<?php
// kana-flashcards.php
require __DIR__ . '/php/check_auth.php';
if (!$user) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kana Flashcard</title>
  <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" rel="stylesheet">

  <style>
    body {
      background-color:#cce7e8;
      font-family:'Kosugi Maru',sans-serif;
      margin:0;
      overflow-x:hidden;
      color:#1e2f30;
    }

    /* === HEADER BAR === */
    .top-bar{
      display:flex;
      justify-content:space-between;
      align-items:center;
      background-color:#6b9aa6;
      padding:8px 20px;
      color:#fff;
      border-bottom:4px solid #4d7d86;
    }
    .top-left{display:flex;align-items:center;gap:15px}
    .header-text{display:flex;flex-direction:column;justify-content:center;line-height:1.3}
    .title{font-size:1.7em;font-weight:bold}
    .subtitle{font-size:0.8em}
    .top-bar img{height:60px;cursor:pointer}

    /* === MAIN FLASHCARD LAYOUT === */
    .flashcard {
      display:flex;
      justify-content:center;
      align-items:center;
      gap:40px;
      margin:50px auto;
      width:85%;
      max-width:1100px;
    }

    /* === LEFT PANEL === */
    .left-panel{
      background-color:#76939b;
      border-radius:30px;
      flex:1;
      text-align:center;
      color:white;
      padding:40px 20px;
      position:relative;
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:20px;
    }

    /* Row with arrows + romaji + sound */
    .nav-row{
      display:flex;
      align-items:center;
      justify-content:center;
      gap:12px;
      margin-bottom:8px;
    }

    .romaji{
      font-size:30px;
      background:white;
      color:#274043;
      border-radius:25px;
      display:inline-block;
      padding:4px 22px;
      font-weight:bold;
    }

    .arrow-btn{
      width:40px;
      height:40px;
      border-radius:50%;
      border:none;
      background:#617e88;
      color:#fff;
      font-size:22px;
      display:flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      transition:background 0.2s, transform 0.2s;
    }
    .arrow-btn:hover{
      background:#7f9aa3;
      transform:translateY(-1px);
    }

    .sound-btn{
      width:60px;
      height:60px;
      cursor:pointer;
      vertical-align:middle;
    }

    .kana-display-main{
      display:flex;
      flex-direction:column;
      align-items:center;
      gap:10px;
    }

    #kanaChar{
      font-size:180px;
      margin:10px 0;
      display:block;
    }

    .vocab-img {
      width:180px;
      margin:10px 0 15px;
    }

    .vocab-text{font-size:22px;margin-top:5px}
    .vocab-romaji{color:#b8e5b8;font-weight:bold}

    /* === RIGHT PANEL === */
    .right-panel {
      background-color:#55767d;
      border-radius:30px;
      flex: 0 0 auto !important;
      padding:20px 25px;
      color:white;

      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:flex-start;
      gap:12px;
      width:340px;
      height:auto !important;
    }

    .stroke-order {
      text-align:center;
      margin:0;
      width:100%;
    }

    .stroke-img {
      border-radius:10px;
      width:100%;
      max-width:260px;
      display:block;
      margin:0 auto;
    }

    .mnemonic-box {
      background-color:#2c4f55;
      padding:12px 16px;
      border-radius:12px;
      font-size:18px;
      line-height:1.5;
      margin:0;
      width:100%;
      max-width:280px;
      text-align:left;
    }

    /* mark-mastered UI */
    .master-row {
      display:flex;
      gap:10px;
      align-items:center;
      justify-content:center;
      margin-top:6px;
      width:100%;
    }
    .master-btn {
      background:#ffd24a;
      color:#274043;
      border:none;
      border-radius:12px;
      padding:8px 12px;
      font-weight:bold;
      cursor:pointer;
      display:flex;
      gap:8px;
      align-items:center;
    }
    .star {
      font-size:22px;
      color:#f2d46b;
      display:inline-block;
      width:28px;
      text-align:center;
      transition:opacity .15s, transform .12s;
    }
    .star.empty { 
      color: rgba(242,212,107,0.35); 
      transform:scale(.98);
    } /* faint when not mastered */

    .progress-track { background:#d8eae9;border-radius:12px;height:18px;overflow:hidden;margin-top:10px; width:100%; }
    .fill { height:100%; background:linear-gradient(90deg,#60a6a9,#2f6f73); width:0%; transition:width .4s; }
    .label{ margin-top:6px; text-align:right; color:#eaf7f6; font-weight:bold; }

    .muted{font-size:.85rem;color:#daf3ef;margin-top:6px}

    /* disabled look for mastered button */
    .master-btn[aria-disabled="true"] {
      opacity: 0.65;
      cursor: default;
    }
  </style>
</head>

<body>
  <!-- HEADER -->
  <div class="top-bar">
    <div class="top-left">
      <a href="dashboard.php">
        <img src="/NihonGo/images/home.png" alt="Home" id="homeBtn">
      </a>
      <div class="header-text">
        <div class="title">Kana Flashcard</div>
        <div class="subtitle">
          Click the sound button above to hear how a kana is pronounced, and the sound button below to hear how to say a vocabulary.
        </div>
      </div>
    </div>

    <div class="top-right">
      <img src="/NihonGo/images/exit.png" alt="Exit" id="exitBtn" style="height:50px;">
      <img src="/NihonGo/images/setting.png" alt="Settings" id="settingsBtn" style="height:50px;">
      <img src="/NihonGo/images/profile.png" alt="Profile" id="profileBtn" style="height:50px;">
    </div>
  </div>

  <!-- FLASHCARD BODY -->
  <div class="flashcard">
    <!-- LEFT PANEL -->
    <div class="left-panel">
      <!-- arrows + romaji + top sound -->
      <div class="nav-row">
        <button id="prevBtn" class="arrow-btn">‹</button>
        <div class="romaji" id="romaji">a</div>
        <img src="/NihonGo/images/sound.png" class="sound-btn" id="kanaSoundTop" alt="Play Kana Sound">
        <button id="nextBtn" class="arrow-btn">›</button>
      </div>

      <!-- main kana + vocab -->
      <div class="kana-display-main">
        <h1 id="kanaChar">あ</h1>

        <img id="vocabImg" class="vocab-img" src="/NihonGo/images/ame.png" alt="Vocab Image">

        <div class="vocab-text">
          <span id="vocabRomaji" class="vocab-romaji">(ame)</span><br>
          <span id="vocabKana">あめ</span> — <span id="vocabEng">candy / rain</span><br>
          <img src="/NihonGo/images/sound.png" class="sound-btn" id="vocabSoundBtn" alt="Play Vocab Sound">
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">
      <div class="stroke-order">
        <img id="strokeImg" class="stroke-img" src="/NihonGo/images/hiraganaa.gif" alt="Stroke Order">
      </div>
      <div class="mnemonic-box" id="mnemonicText">
        When the fish got stabbed by the sword, it went a!
      </div>

      <!-- progress & star -->
      <div style="width:100%;margin-top:8px">
        <div style="font-size:1.1rem;font-weight:bold;text-align:center" id="progressTitle">Script progress</div>
        <div style="font-size:2.0rem;margin-top:6px;text-align:center" id="typeCount">0/46</div>
        <div class="muted" id="typePct">0%</div>

        <div style="margin-top:12px">
          <div class="progress-track"><div id="typeFill" class="fill" style="width:0%"></div></div>
          <div class="label" id="typeLabelText">0/46 — 0%</div>
        </div>
      </div>

      <div class="master-row" role="group" aria-label="master controls">
        <div class="star empty" id="starIcon">☆</div>
        <button id="masterBtn" class="master-btn" aria-pressed="false" aria-disabled="false"><span id="masterBtnText">Mark as Mastered</span></button>
      </div>
      <div class="muted" style="margin-top:8px">Mastery shows your progress for this kana.</div>
    </div>
  </div>

<script>

/* ---------- Image mappings for all kana ---------- */
const hiraImageMap = {
  "あ": { stroke: "hiraganaa.gif", vocabImg: "ame.png" },
  "い": { stroke: "hiraganai.gif", vocabImg: "inu.png" },
  "う": { stroke: "hiraganau.gif", vocabImg: "usagi.png" },
  "え": { stroke: "hiraganae.gif", vocabImg: "enpitsu.png" },
  "お": { stroke: "hiraganao.gif", vocabImg: "onigiri.png" },
  "か": { stroke: "hiraganaka.gif", vocabImg: "kaban.png" },
  "き": { stroke: "hiraganaki.gif", vocabImg: "ki.png" },
  "く": { stroke: "hiraganaku.gif", vocabImg: "kuruma.png" },
  "け": { stroke: "hiraganake.gif", vocabImg: "kemushi.png" },
  "こ": { stroke: "hiraganako.gif", vocabImg: "koma.png" },
  "さ": { stroke: "hiraganasa.gif", vocabImg: "saru.png" },
  "し": { stroke: "hiraganashi.gif", vocabImg: "shinbun.png" },
  "す": { stroke: "hiraganasu.gif", vocabImg: "suika.png" },
  "せ": { stroke: "hiraganase.gif", vocabImg: "senbei.png" },
  "そ": { stroke: "hiraganaso.gif", vocabImg: "sora.png" },
  "た": { stroke: "hiraganata.gif", vocabImg: "tamago.png" },
  "ち": { stroke: "hiraganachi.gif", vocabImg: "chizu.png" },
  "つ": { stroke: "hiraganatsu.gif", vocabImg: "tsuki.png" },
  "て": { stroke: "hiraganate.gif", vocabImg: "tegami.png" },
  "と": { stroke: "hiraganato.gif", vocabImg: "tori.png" },
  "な": { stroke: "hiraganana.gif", vocabImg: "namida.png" },
  "に": { stroke: "hiraganani.gif", vocabImg: "niku.png" },
  "ぬ": { stroke: "hiragananu.gif", vocabImg: "nuno.png" },
  "ね": { stroke: "hiraganane.gif", vocabImg: "neko.png" },
  "の": { stroke: "hiraganano.gif", vocabImg: "nori.png" },
  "は": { stroke: "hiraganaha.gif", vocabImg: "hana.png" },
  "ひ": { stroke: "hiraganahi.gif", vocabImg: "hikari.png" },
  "ふ": { stroke: "hiraganafu.gif", vocabImg: "fune.png" },
  "へ": { stroke: "hiraganahe.gif", vocabImg: "hebi.png" },
  "ほ": { stroke: "hiraganaho.gif", vocabImg: "hoshi.png" },
  "ま": { stroke: "hiraganama.gif", vocabImg: "mado.png" },
  "み": { stroke: "hiraganami.gif", vocabImg: "mizu.png" },
  "む": { stroke: "hiraganamu.gif", vocabImg: "mushi.png" },
  "め": { stroke: "hiraganame.gif", vocabImg: "megane.png" },
  "も": { stroke: "hiraganamo.gif", vocabImg: "mori.png" },
  "や": { stroke: "hiraganaya.gif", vocabImg: "yama.png" },
  "ゆ": { stroke: "hiraganayu.gif", vocabImg: "yuki.png" },
  "よ": { stroke: "hiraganayo.gif", vocabImg: "yoru.png" },
  "ら": { stroke: "hiraganara.gif", vocabImg: "raion.png" },
  "り": { stroke: "hiraganari.gif", vocabImg: "risu.png" },
  "る": { stroke: "hiraganaru.gif", vocabImg: "rusu.png" },
  "れ": { stroke: "hiraganare.gif", vocabImg: "reizoko.png" },
  "ろ": { stroke: "hiraganaro.gif", vocabImg: "rousoku.png" },
  "わ": { stroke: "hiraganawa.gif", vocabImg: "wani.png" },
  "を": { stroke: "hiraganawo.gif", vocabImg: "wo.png" },
  "ん": { stroke: "hiraganan.gif", vocabImg: "hon.png" }
};

const kataImageMap = {
  "ア": { stroke: "katakanaa.gif", vocabImg: "amerika.png" },
  "イ": { stroke: "katakanai.gif", vocabImg: "inku.png" },
  "ウ": { stroke: "katakanau.gif", vocabImg: "uisuki.png" },
  "エ": { stroke: "katakanae.gif", vocabImg: "erebeta.png" },
  "オ": { stroke: "katakanao.gif", vocabImg: "ofisu.png" },
  "カ": { stroke: "katakanaka.gif", vocabImg: "kamera.png" },
  "キ": { stroke: "katakanaki.gif", vocabImg: "kitchin.png" },
  "ク": { stroke: "katakanaku.gif", vocabImg: "kurabu.png" },
  "ケ": { stroke: "katakanake.gif", vocabImg: "keki.png" },
  "コ": { stroke: "katakanako.gif", vocabImg: "kohi.png" },
  "サ": { stroke: "katakanasa.gif", vocabImg: "sando.png" },
  "シ": { stroke: "katakanashi.gif", vocabImg: "shito.png" },
  "ス": { stroke: "katakanasu.gif", vocabImg: "supa.png" },
  "セ": { stroke: "katakanase.gif", vocabImg: "seta.png" },
  "ソ": { stroke: "katakanaso.gif", vocabImg: "soda.png" },
  "タ": { stroke: "katakanata.gif", vocabImg: "takushi.png" },
  "チ": { stroke: "katakanachi.gif", vocabImg: "cheese.png" },
  "ツ": { stroke: "katakanatsu.gif", vocabImg: "tsua.png" },
  "テ": { stroke: "katakanate.gif", vocabImg: "tesuto.png" },
  "ト": { stroke: "katakanato.gif", vocabImg: "tomato.png" },
  "ナ": { stroke: "katakanana.gif", vocabImg: "naifu.png" },
  "ニ": { stroke: "katakanani.gif", vocabImg: "nyusu.png" },
  "ヌ": { stroke: "katakananu.gif", vocabImg: "nudoru.png" },
  "ネ": { stroke: "katakanane.gif", vocabImg: "netto.png" },
  "ノ": { stroke: "katakanano.gif", vocabImg: "noto.png" },
  "ハ": { stroke: "katakanaha.gif", vocabImg: "hanbaga.png" },
  "ヒ": { stroke: "katakanahi.gif", vocabImg: "hiro.png" },
  "フ": { stroke: "katakanafu.gif", vocabImg: "food.png" },
  "ヘ": { stroke: "katakanahe.gif", vocabImg: "helmet.png" },
  "ホ": { stroke: "katakanaho.gif", vocabImg: "hoteru.png" },
  "マ": { stroke: "katakanama.gif", vocabImg: "masuku.png" },
  "ミ": { stroke: "katakanami.gif", vocabImg: "miruku.png" },
  "ム": { stroke: "katakanamu.gif", vocabImg: "mubi.png" },
  "メ": { stroke: "katakaname.gif", vocabImg: "meru.png" },
  "モ": { stroke: "katakanamo.gif", vocabImg: "moderu.png" },
  "ヤ": { stroke: "katakanaya.gif", vocabImg: "yado.png" },
  "ユ": { stroke: "katakanayu.gif", vocabImg: "yunifomu.png" },
  "ヨ": { stroke: "katakanayo.gif", vocabImg: "yoguruto.png" },
  "ラ": { stroke: "katakanara.gif", vocabImg: "rajio.png" },
  "リ": { stroke: "katakanari.gif", vocabImg: "ringu.png" },
  "ル": { stroke: "katakanaru.gif", vocabImg: "ruru.png" },
  "レ": { stroke: "katakanare.gif", vocabImg: "remon.png" },
  "ロ": { stroke: "katakanaro.gif", vocabImg: "robotto.png" },
  "ワ": { stroke: "katakanawa.gif", vocabImg: "wain.png" },
  "ヲ": { stroke: "katakanawo.gif", vocabImg: "wokka.png" },
  "ン": { stroke: "katakanan.gif", vocabImg: "supun.png" }
};

let hiraData = [];
let kataData = [];

/* Load kana data from database and merge with image mappings */
async function loadKanaData() {
  try {
    const hiraResponse = await fetch('/NihonGo/php/get_kana_data.php?type=hiragana');
    const hiraFromDb = await hiraResponse.json();
    hiraData = hiraFromDb.map(k => ({
      ...k,
      stroke: hiraImageMap[k.kana_char]?.stroke || 'placeholder.gif',
      vocabImg: hiraImageMap[k.kana_char]?.vocabImg || 'placeholder.png',
      kana: k.kana_char,
      romaji: k.romaji
    }));

    const kataResponse = await fetch('/NihonGo/php/get_kana_data.php?type=katakana');
    const kataFromDb = await kataResponse.json();
    kataData = kataFromDb.map(k => ({
      ...k,
      stroke: kataImageMap[k.kana_char]?.stroke || 'placeholder.gif',
      vocabImg: kataImageMap[k.kana_char]?.vocabImg || 'placeholder.png',
      kana: k.kana_char,
      romaji: k.romaji
    }));
  } catch (error) {
    console.error('Failed to load kana data:', error);
  }
}

/* Helpers and initialization */
function urlParam(name) {
  const p = new URLSearchParams(window.location.search);
  return p.get(name);
}

const requestedKana = urlParam("kana");
const typeParam = urlParam("type") || "hiragana";

let activeData = [];

// find the requested index or default to 0
let currentIndex = 0;

/* local state for current item and master info */
let currentKanaObj = null;
let currentMastery = 0; // 0 or 2 (mastered)
let lastTypeCount = 0;

/* ================= KANA SOUND ================= */

const kanaAudio = new Audio();
kanaAudio.preload = "auto";

const kanaSoundBtn = document.getElementById("kanaSoundTop");

kanaSoundBtn.addEventListener("click", () => {
  if (!currentKanaObj || !currentKanaObj.romaji) return;

  const romaji = currentKanaObj.romaji.toLowerCase();
  const soundPath = `/NihonGo/sounds/kana/${romaji}.mp3`;

  kanaAudio.src = soundPath;
  kanaAudio.currentTime = 0;

  kanaAudio.play().catch(err => {
    console.warn("Missing kana audio:", soundPath);
  });
});


/* small client-side cache so mastered marks appear instantly while swiping */
const masteredCache = {}; // { "あ|hiragana": true }

/* ---- UI elements ---- */
const kanaCharEl = document.getElementById("kanaChar");
const romajiEl = document.getElementById("romaji");
const mnemonicEl = document.getElementById("mnemonicText");
const vocabKanaEl = document.getElementById("vocabKana");
const vocabRomajiEl = document.getElementById("vocabRomaji");
const vocabEngEl = document.getElementById("vocabEng");
const strokeImgEl = document.getElementById("strokeImg");
const vocabImgEl = document.getElementById("vocabImg");
const typeCountEl = document.getElementById("typeCount");
const typePctEl = document.getElementById("typePct");
const typeFillEl = document.getElementById("typeFill");
const typeLabelTextEl = document.getElementById("typeLabelText");
const starIcon = document.getElementById("starIcon");
const masterBtn = document.getElementById("masterBtn");
const masterBtnText = document.getElementById("masterBtnText");

/* utility to safely clamp numbers */
function safeInt(v, fallback=0) {
  const n = parseInt(v, 10);
  return Number.isFinite(n) ? n : fallback;
}

/* utility to update right panel counts (clamped, robust) */
function updateRightPanel(count) {
  const capped = Math.min(46, Math.max(0, safeInt(count, 0)));
  const pct = Math.round((capped / 46) * 100);
  typeCountEl.textContent = `${capped}/46`;
  typePctEl.textContent = `${pct}%`;
  typeLabelTextEl.textContent = `${capped}/46 — ${pct}%`;
  // ensure width always valid 0..100
  typeFillEl.style.width = Math.max(0, Math.min(100, pct)) + '%';
  lastTypeCount = capped;
}

/* show current star state */
function setStarState(isMastered) {
  if (isMastered) {
    starIcon.classList.remove('empty');
    starIcon.textContent = '★';
    masterBtnText.textContent = 'Mastered';
    masterBtn.setAttribute('aria-pressed','true');
    masterBtn.setAttribute('aria-disabled','true');
  } else {
    starIcon.classList.add('empty');
    starIcon.textContent = '☆';
    masterBtnText.textContent = 'Mark as Mastered';
    masterBtn.setAttribute('aria-pressed','false');
    masterBtn.setAttribute('aria-disabled','false');
  }
}

/* display a kana object given its index */
function displayKana(i) {
  const k = activeData[i];
  currentKanaObj = k;
  kanaCharEl.textContent = k.kana;
  romajiEl.textContent = k.romaji;
  mnemonicEl.textContent = k.mnemonic;
  vocabKanaEl.textContent = k.vocab_jp;
  vocabRomajiEl.textContent = "(" + k.vocab_romaji + ")";
  vocabEngEl.textContent = k.vocab_eng;
  strokeImgEl.src = "/NihonGo/images/" + k.stroke;
  vocabImgEl.src = "/NihonGo/images/" + k.vocabImg;

  // Show cached mastery immediately if available (makes star persistent while swiping)
  const cacheKey = `${k.kana}|${typeParam}`;
  if (masteredCache[cacheKey]) {
    currentMastery = 2;
    setStarState(true);
  } else {
    // default to non-mastered visually until server confirms
    currentMastery = 0;
    setStarState(false);
  }

  // Ask server to register a view and return mastery/type counts
  // (server returns mastery_level and type_count)
  fetch("php/save_progress.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ kana: k.kana, type: typeParam, action: "view" })
  })
  .then(r => r.json())
  .then(data => {
    if (data && data.success) {
      // update mastery from server (this may be same as cached)
      const serverMaster = safeInt(data.mastery_level, 0);
      currentMastery = serverMaster;
      // update client cache so star remains when swiping
      if (serverMaster === 2) masteredCache[cacheKey] = true;
      else delete masteredCache[cacheKey];

      setStarState(currentMastery === 2);

      // update right panel using returned count if present
      if (typeof data.type_count !== 'undefined') {
        updateRightPanel(data.type_count);
      }
    } else {
      // server returned error — leave UI usable, keep cached state
      // nothing to do
    }
  })
  .catch(() => {
    // network error: do not block UI; keep cached star state
    // optionally show a small visual indicator in future
  });
}

/* next / prev handlers */
document.getElementById("nextBtn").addEventListener("click", () => {
  currentIndex = (currentIndex + 1) % activeData.length;
  displayKana(currentIndex);
});

document.getElementById("prevBtn").addEventListener("click", () => {
  currentIndex = (currentIndex - 1 + activeData.length) % activeData.length;
  displayKana(currentIndex);
});

/* master/unmaster toggle - SINGLE HANDLER */
document.getElementById("masterBtn").onclick = () => {
  if (!currentKanaObj) return;

  const kana = currentKanaObj.kana;
  const cacheKey = `${kana}|${typeParam}`;
  const newAction = (currentMastery === 2) ? "unmaster" : "master";

  // Optimistic UI update
  currentMastery = (newAction === "master" ? 2 : 0);

  if (currentMastery === 2) {
    masteredCache[cacheKey] = true;
  } else {
    delete masteredCache[cacheKey];
  }

  setStarState(currentMastery === 2);

  // Send to server
  fetch("php/save_progress.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      action: newAction,
      kana: kana,
      type: typeParam
    })
  })
  .then(r => r.json())
  .then(data => {
    if (data && data.success) {
      currentMastery = data.mastery_level;

      if (currentMastery === 2) masteredCache[cacheKey] = true;
      else delete masteredCache[cacheKey];

      setStarState(currentMastery === 2);

      if (typeof data.type_count !== "undefined") {
        updateRightPanel(data.type_count);
      }
    } else {
      alert("Server error updating mastery.");
    }
  })
  .catch(() => {
    alert("Network error updating mastery.");
  });
};

/* topbar icon wiring */
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

/* initial load - fetch data from database then display */
(async () => {
  await loadKanaData();
  activeData = (typeParam === "katakana") ? kataData : hiraData;
  
  // find the requested index or default to 0
  currentIndex = activeData.findIndex(k => k.kana === requestedKana);
  if (currentIndex === -1) currentIndex = 0;
  
  displayKana(currentIndex);
})();
</script>
</body>
</html>
