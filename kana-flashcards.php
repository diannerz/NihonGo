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
  align-items:center;   /* ← THIS LINE centers right panel */
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
  width:180px;        /* bigger vocabulary images */
  margin:10px 0 15px; /* optional, gives some breathing room */
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
      width:340px;     /* fixed, compact width */
      height:auto !important;     /* shrinks to content */
    }

    .stroke-order {
      text-align:center;
      margin:0;
      width:100%;
    }

    .stroke-img {
      border-radius:10px;
      width:100%;
      max-width:260px; /* a bit bigger but still compact */
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

    /* bottom nav-buttons no longer used, but keep just in case */
    .nav-buttons{text-align:center;margin-top:25px;display:none;}
    .nav-buttons button{background-color:#5a8f9a;border:none;color:white;font-size:18px;border-radius:20px;padding:10px 25px;margin:0 15px;cursor:pointer}
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
      <img src="/NihonGo/images/setting.png" alt="Settings" style="height:50px;">
      <img src="/NihonGo/images/profile.png" alt="Profile" style="height:50px;">
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
    </div>
  </div>

  <!-- (old bottom nav removed visually) -->
  <div class="nav-buttons">
    <button id="prevBtn-old">← Previous</button>
    <button id="nextBtn-old">Next →</button>
  </div>

<script>
/*
  Kana dataset. Each image must exist in /NihonGo/images/
*/
const kanaData = [
  { kana: "あ", romaji: "a", mnemonic: "When the fish got stabbed by the sword, it went a!", vocab_jp: "あめ", vocab_romaji: "ame", vocab_eng: "candy / rain", stroke: "hiraganaa.gif", vocabImg: "ame.png", type: "hiragana" },
  { kana: "い", romaji: "i", mnemonic: "Two eels swimming around each other. Eek!", vocab_jp: "いぬ", vocab_romaji: "inu", vocab_eng: "dog", stroke: "hiraganai.gif", vocabImg: "inu.png", type: "hiragana" },
  { kana: "う", romaji: "u", mnemonic: "Just Latin U tilting to the left with a line on top.", vocab_jp: "うさぎ", vocab_romaji: "usagi", vocab_eng: "bunny", stroke: "hiraganau.gif", vocabImg: "usagi.png", type: "hiragana" },
  { kana: "え", romaji: "e", mnemonic: "When we pronounce 'z' we say 'zee' and e looks like z.", vocab_jp: "えんぴつ", vocab_romaji: "enpitsu", vocab_eng: "pencil", stroke: "hiraganae.gif", vocabImg: "enpitsu.png", type: "hiragana" },
  { kana: "お", romaji: "o", mnemonic: "The upper right is the hand holding a sword and writing an unfinished 'O' at the sand.", vocab_jp: "おにぎり", vocab_romaji: "onigiri", vocab_eng: "rice ball", stroke: "hiraganao.gif", vocabImg: "onigiri.png", type: "hiragana" },
  { kana: "か", romaji: "ka", mnemonic: "The arm of the K is falling down.", vocab_jp: "かばん", vocab_romaji: "kaban", vocab_eng: "bag", stroke: "hiraganaka.gif", vocabImg: "kaban.png", type: "hiragana" },
  { kana: "き", romaji: "ki", mnemonic: "Looks like a house key.", vocab_jp: "き", vocab_romaji: "ki", vocab_eng: "tree", stroke: "hiraganaki.gif", vocabImg: "ki.png", type: "hiragana" },
  { kana: "く", romaji: "ku", mnemonic: "Coo coo bird mouth making a ku sound.", vocab_jp: "くるま", vocab_romaji: "kuruma", vocab_eng: "car", stroke: "hiraganaku.gif", vocabImg: "kuruma.png", type: "hiragana" },
  { kana: "け", romaji: "ke", mnemonic: "Similar to how a KEg looks.", vocab_jp: "けむし", vocab_romaji: "kemushi", vocab_eng: "caterpillar", stroke: "hiraganake.gif", vocabImg: "kemushi.png", type: "hiragana" },
  { kana: "こ", romaji: "ko", mnemonic: "Two koi fish swimming in a pond when viewed from above.", vocab_jp: "こま", vocab_romaji: "koma", vocab_eng: "spinning top", stroke: "hiraganako.gif", vocabImg: "koma.png", type: "hiragana" },
  { kana: "さ", romaji: "sa", mnemonic: "Looks like a smiling monkey.", vocab_jp: "さる", vocab_romaji: "saru", vocab_eng: "monkey", stroke: "hiraganasa.gif", vocabImg: "saru.png", type: "hiragana" },
  { kana: "し", romaji: "shi", mnemonic: "Looks like a fishing hook.", vocab_jp: "しんぶん", vocab_romaji: "shinbun", vocab_eng: "newspaper", stroke: "hiraganashi.gif", vocabImg: "shinbun.png", type: "hiragana" },
  { kana: "す", romaji: "su", mnemonic: "Suuuuu is the sound of slurping noodles.", vocab_jp: "すいか", vocab_romaji: "suika", vocab_eng: "watermelon", stroke: "hiraganasu.gif", vocabImg: "suika.png", type: "hiragana" },
  { kana: "せ", romaji: "se", mnemonic: "Looks like a mama setting a baby on its lap.", vocab_jp: "せんべい", vocab_romaji: "senbei", vocab_eng: "rice cracker", stroke: "hiraganase.gif", vocabImg: "senbei.png", type: "hiragana" }
];

// helpers
function urlParam(name) {
  const p = new URLSearchParams(window.location.search);
  return p.get(name);
}
function isKatakana(ch) {
  if (!ch) return false;
  const code = ch.charCodeAt(0);
  return (code >= 0x30A0 && code <= 0x30FF);
}

const requestedKana = urlParam('kana') || kanaData[0].kana;
let forcedType = urlParam('type') || null;

let currentIndex = kanaData.findIndex(k => k.kana === requestedKana);
if (currentIndex === -1) currentIndex = 0;
if (!forcedType) {
  forcedType = isKatakana(requestedKana) ? 'katakana' : 'hiragana';
}

function displayKana(i) {
  const k = kanaData[i];
  if (!k) return;

  document.getElementById("kanaChar").textContent = k.kana;
  document.getElementById("romaji").textContent = k.romaji;
  document.getElementById("mnemonicText").textContent = k.mnemonic;
  document.getElementById("vocabKana").textContent = k.vocab_jp;
  document.getElementById("vocabRomaji").textContent = `(${k.vocab_romaji})`;
  document.getElementById("vocabEng").textContent = k.vocab_eng;
  document.getElementById("strokeImg").src = "/NihonGo/images/" + k.stroke;
  document.getElementById("vocabImg").src = "/NihonGo/images/" + k.vocabImg;

  const typeToSave = forcedType || k.type || (isKatakana(k.kana) ? 'katakana' : 'hiragana');
  saveProgress(k.kana, typeToSave);
}

// arrow buttons (top row)
document.getElementById("nextBtn").addEventListener("click", () => {
  currentIndex = (currentIndex + 1) % kanaData.length;
  displayKana(currentIndex);
});

document.getElementById("prevBtn").addEventListener("click", () => {
  currentIndex = (currentIndex - 1 + kanaData.length) % kanaData.length;
  displayKana(currentIndex);
});

// exit = back to dashboard
document.getElementById("exitBtn").addEventListener("click", () => {
  window.location.href = 'dashboard.php';
});

displayKana(currentIndex);

function saveProgress(kanaChar, kanaType) {
  fetch("php/save_progress.php", {
    method: "POST",
    headers: {"Content-Type":"application/json"},
    body: JSON.stringify({ kana: kanaChar, type: kanaType })
  }).catch(err => {
    console.warn('save_progress failed', err);
  });
}
</script>
</body>
</html>
