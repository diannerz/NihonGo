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
/* ---------- full kana lists extracted from your provided arrays ---------- */





const hiraData = [
  { kana: "あ", romaji: "a", mnemonic: "When the fish got stabbed by the sword, it went a!", vocab_jp: "あめ", vocab_romaji: "ame", vocab_eng: "candy / rain", stroke: "hiraganaa.gif", vocabImg: "ame.png" },
  { kana: "い", romaji: "i", mnemonic: "Two eels swimming around each other. Eek!", vocab_jp: "いぬ", vocab_romaji: "inu", vocab_eng: "dog", stroke: "hiraganai.gif", vocabImg: "inu.png" },
  { kana: "う", romaji: "u", mnemonic: "Just Latin U tilting left with a line on top.", vocab_jp: "うさぎ", vocab_romaji: "usagi", vocab_eng: "bunny", stroke: "hiraganau.gif", vocabImg: "usagi.png" },
  { kana: "え", romaji: "e", mnemonic: "Looks like the number '4' rotated.", vocab_jp: "えんぴつ", vocab_romaji: "enpitsu", vocab_eng: "pencil", stroke: "hiraganae.gif", vocabImg: "enpitsu.png" },
  { kana: "お", romaji: "o", mnemonic: "Hand holding a sword writing O.", vocab_jp: "おにぎり", vocab_romaji: "onigiri", vocab_eng: "rice ball", stroke: "hiraganao.gif", vocabImg: "onigiri.png" },

  // KA row
  { kana: "か", romaji: "ka", mnemonic: "Arm of the K is falling down.", vocab_jp: "かばん", vocab_romaji: "kaban", vocab_eng: "bag", stroke: "hiraganaka.gif", vocabImg: "kaban.png" },
  { kana: "き", romaji: "ki", mnemonic: "Looks like a house key.", vocab_jp: "き", vocab_romaji: "ki", vocab_eng: "tree", stroke: "hiraganaki.gif", vocabImg: "ki.png" },
  { kana: "く", romaji: "ku", mnemonic: "Coo-coo bird mouth.", vocab_jp: "くるま", vocab_romaji: "kuruma", vocab_eng: "car", stroke: "hiraganaku.gif", vocabImg: "kuruma.png" },
  { kana: "け", romaji: "ke", mnemonic: "Looks like a KEg.", vocab_jp: "けむし", vocab_romaji: "kemushi", vocab_eng: "caterpillar", stroke: "hiraganake.gif", vocabImg: "kemushi.png" },
  { kana: "こ", romaji: "ko", mnemonic: "Two koi swimming in a pond.", vocab_jp: "こま", vocab_romaji: "koma", vocab_eng: "spinning top", stroke: "hiraganako.gif", vocabImg: "koma.png" },

  // SA row
  { kana: "さ", romaji: "sa", mnemonic: "Looks like a smiling monkey.", vocab_jp: "さる", vocab_romaji: "saru", vocab_eng: "monkey", stroke: "hiraganasa.gif", vocabImg: "saru.png" },
  { kana: "し", romaji: "shi", mnemonic: "Looks like a fishing hook.", vocab_jp: "しんぶん", vocab_romaji: "shinbun", vocab_eng: "newspaper", stroke: "hiraganashi.gif", vocabImg: "shinbun.png" },
  { kana: "す", romaji: "su", mnemonic: "Slurping noodle shape.", vocab_jp: "すいか", vocab_romaji: "suika", vocab_eng: "watermelon", stroke: "hiraganasu.gif", vocabImg: "suika.png" },
  { kana: "せ", romaji: "se", mnemonic: "Mama setting a baby on its lap.", vocab_jp: "せんべい", vocab_romaji: "senbei", vocab_eng: "rice cracker", stroke: "hiraganase.gif", vocabImg: "senbei.png" },
  { kana: "そ", romaji: "so", mnemonic: "SOap — motion you'd wash your belly with in zigzag.", vocab_jp: "そら", vocab_romaji: "sora", vocab_eng: "sky", stroke: "hiraganaso.gif", vocabImg: "sora.png" },

  // TA row
  { kana: "た", romaji: "ta", mnemonic: "Looks like a t with a small o.", vocab_jp: "たまご", vocab_romaji: "tamago", vocab_eng: "egg", stroke: "hiraganata.gif", vocabImg: "tamago.png" },
  { kana: "ち", romaji: "chi", mnemonic: "Looks like the number 5.", vocab_jp: "ちず", vocab_romaji: "chizu", vocab_eng: "map", stroke: "hiraganachi.gif", vocabImg: "chizu.png" },
  { kana: "つ", romaji: "tsu", mnemonic: "Looks like a TSUnami wave.", vocab_jp: "つき", vocab_romaji: "tsuki", vocab_eng: "moon", stroke: "hiraganatsu.gif", vocabImg: "tsuki.png" },
  { kana: "て", romaji: "te", mnemonic: "It looks like a T.", vocab_jp: "てがみ", vocab_romaji: "tegami", vocab_eng: "letter", stroke: "hiraganate.gif", vocabImg: "tegami.png" },
  { kana: "と", romaji: "to", mnemonic: "Your Tooth Touching your TOngue.", vocab_jp: "とり", vocab_romaji: "tori", vocab_eng: "bird", stroke: "hiraganato.gif", vocabImg: "tori.png" },

  // NA row
  { kana: "な", romaji: "na", mnemonic: "Person throwing something saying: NA, I don't need this.", vocab_jp: "なみだ", vocab_romaji: "namida", vocab_eng: "tears", stroke: "hiraganana.gif", vocabImg: "namida.png" },
  { kana: "に", romaji: "ni", mnemonic: "Two little brothers beside older brother.", vocab_jp: "にく", vocab_romaji: "niku", vocab_eng: "meat", stroke: "hiraganani.gif", vocabImg: "niku.png" },
  { kana: "ぬ", romaji: "nu", mnemonic: "Looks like noodles with chopsticks.", vocab_jp: "ぬの", vocab_romaji: "nuno", vocab_eng: "cloth", stroke: "hiragananu.gif", vocabImg: "nuno.png" },
  { kana: "ね", romaji: "ne", mnemonic: "Looks like a cat stretching.", vocab_jp: "ねこ", vocab_romaji: "neko", vocab_eng: "cat", stroke: "hiraganane.gif", vocabImg: "neko.png" },
  { kana: "の", romaji: "no", mnemonic: "Looks like NO with o inside n.", vocab_jp: "のり", vocab_romaji: "nori", vocab_eng: "seaweed", stroke: "hiraganano.gif", vocabImg: "nori.png" },

  // HA row
  { kana: "は", romaji: "ha", mnemonic: "Top looks like H, bottom like small a.", vocab_jp: "はな", vocab_romaji: "hana", vocab_eng: "flower", stroke: "hiraganaha.gif", vocabImg: "hana.png" },
  { kana: "ひ", romaji: "hi", mnemonic: "Looks like a smile when you say hi.", vocab_jp: "ひかり", vocab_romaji: "hikari", vocab_eng: "light", stroke: "hiraganahi.gif", vocabImg: "hikari.png" },
  { kana: "ふ", romaji: "fu", mnemonic: "Looks like someone blowing raspberries.", vocab_jp: "ふね", vocab_romaji: "fune", vocab_eng: "ship", stroke: "hiraganafu.gif", vocabImg: "fune.png" },
  { kana: "へ", romaji: "he", mnemonic: "Looks like a heel.", vocab_jp: "へび", vocab_romaji: "hebi", vocab_eng: "snake", stroke: "hiraganahe.gif", vocabImg: "hebi.png" },
  { kana: "ほ", romaji: "ho", mnemonic: "Flip the strokes sideways → ho.", vocab_jp: "ほし", vocab_romaji: "hoshi", vocab_eng: "star", stroke: "hiraganaho.gif", vocabImg: "hoshi.png" },

  // MA row
  { kana: "ま", romaji: "ma", mnemonic: "Looks like ho without first stroke.", vocab_jp: "まど", vocab_romaji: "mado", vocab_eng: "window", stroke: "hiraganama.gif", vocabImg: "mado.png" },
  { kana: "み", romaji: "mi", mnemonic: "Looks like the number 21.", vocab_jp: "みず", vocab_romaji: "mizu", vocab_eng: "water", stroke: "hiraganami.gif", vocabImg: "mizu.png" },
  { kana: "む", romaji: "mu", mnemonic: "Looks like a cow’s nose (moo).", vocab_jp: "むし", vocab_romaji: "mushi", vocab_eng: "insect", stroke: "hiraganamu.gif", vocabImg: "mushi.png" },
  { kana: "め", romaji: "me", mnemonic: "Looks like an eye (me).", vocab_jp: "めがね", vocab_romaji: "megane", vocab_eng: "glasses", stroke: "hiraganame.gif", vocabImg: "megane.png" },
  { kana: "も", romaji: "mo", mnemonic: "Looks like a MOp sweeping across the floor.", vocab_jp: "もり", vocab_romaji: "mori", vocab_eng: "forest", stroke: "hiraganamo.gif", vocabImg: "mori.png" },

  // YA row
  { kana: "や", romaji: "ya", mnemonic: "Looks like someone punching shouting YA!", vocab_jp: "やま", vocab_romaji: "yama", vocab_eng: "mountain", stroke: "hiraganaya.gif", vocabImg: "yama.png" },
  { kana: "ゆ", romaji: "yu", mnemonic: "Looks like someone hugging → yu!", vocab_jp: "ゆき", vocab_romaji: "yuki", vocab_eng: "snow", stroke: "hiraganayu.gif", vocabImg: "yuki.png" },
  { kana: "よ", romaji: "yo", mnemonic: "Looks like a YO-yo string.", vocab_jp: "よる", vocab_romaji: "yoru", vocab_eng: "night", stroke: "hiraganayo.gif", vocabImg: "yoru.png" },

  // RA row
  { kana: "ら", romaji: "ra", mnemonic: "Bowl of Ramen with spoon.", vocab_jp: "らいおん", vocab_romaji: "raion", vocab_eng: "lion", stroke: "hiraganara.gif", vocabImg: "raion.png" },
  { kana: "り", romaji: "ri", mnemonic: "Looks like a RIVER.", vocab_jp: "りす", vocab_romaji: "risu", vocab_eng: "squirrel", stroke: "hiraganari.gif", vocabImg: "risu.png" },
  { kana: "る", romaji: "ru", mnemonic: "Turn sideways → looks like NO. No RUles.", vocab_jp: "るす", vocab_romaji: "rusu", vocab_eng: "absence", stroke: "hiraganaru.gif", vocabImg: "rusu.png" },
  { kana: "れ", romaji: "re", mnemonic: "A ray of sunshine.", vocab_jp: "れいぞうこ", vocab_romaji: "reizoko", vocab_eng: "fridge", stroke: "hiraganare.gif", vocabImg: "reizoko.png" },
  { kana: "ろ", romaji: "ro", mnemonic: "Looks like RU without circle.", vocab_jp: "ろうそく", vocab_romaji: "rousoku", vocab_eng: "candle", stroke: "hiraganaro.gif", vocabImg: "rousoku.png" },

  // WA row
  { kana: "わ", romaji: "wa", mnemonic: "Looks like Wario's dumpy.", vocab_jp: "わに", vocab_romaji: "wani", vocab_eng: "crocodile", stroke: "hiraganawa.gif", vocabImg: "wani.png" },
  { kana: "を", romaji: "wo", mnemonic: "Stickman sitting on a worm.", vocab_jp: "を", vocab_romaji: "wo", vocab_eng: "is an object particle, japanese words typically don't start with wo.", stroke: "hiraganawo.gif", vocabImg: "wo.png" },
  { kana: "ん", romaji: "n", mnemonic: "Looks like lowercase n.", vocab_jp: "ほん", vocab_romaji: "hon", vocab_eng: "book", stroke: "hiraganan.gif", vocabImg: "hon.png" }
];


/* ------------------------------- helpers ------------------------------- */
function urlParam(name) {
  const p = new URLSearchParams(window.location.search);
  return p.get(name);
}

const requestedKana = urlParam("kana");
const typeParam = urlParam("type") || "hiragana";
const kataData = [
  { kana: "ア", romaji: "a", mnemonic: "Looks like an umbrella (a-mbrella).", vocab_jp: "アメリカ", vocab_romaji: "amerika", vocab_eng: "America", stroke: "katakanaa.gif", vocabImg: "amerika.png" },
  { kana: "イ", romaji: "i", mnemonic: "Internet wires hung on a pole.", vocab_jp: "インク", vocab_romaji: "inku", vocab_eng: "ink", stroke: "katakanai.gif", vocabImg: "inku.png" },
  { kana: "ウ", romaji: "u", mnemonic: "Latin U leaning left.", vocab_jp: "ウイスキー", vocab_romaji: "uisuki", vocab_eng: "whiskey", stroke: "katakanau.gif", vocabImg: "uisuki.png" },
  { kana: "エ", romaji: "e", mnemonic: "Guy starting to propose then saying 'e'.", vocab_jp: "エレベーター", vocab_romaji: "erebeetaa", vocab_eng: "elevator", stroke: "katakanae.gif", vocabImg: "erebeta.png" },
  { kana: "オ", romaji: "o", mnemonic: "Bottom looks like onigiri.", vocab_jp: "オフィス", vocab_romaji: "ofisu", vocab_eng: "office", stroke: "katakanao.gif", vocabImg: "ofisu.png" },

  { kana: "カ", romaji: "ka", mnemonic: "Arm of K falling.", vocab_jp: "カメラ", vocab_romaji: "kamera", vocab_eng: "camera", stroke: "katakanaka.gif", vocabImg: "kamera.png" },
  { kana: "キ", romaji: "ki", mnemonic: "Looks like a scar.", vocab_jp: "キッチン", vocab_romaji: "kitchin", vocab_eng: "kitchen", stroke: "katakanaki.gif", vocabImg: "kitchin.png" },
  { kana: "ク", romaji: "ku", mnemonic: "Looks like a thumbs up.", vocab_jp: "クラブ", vocab_romaji: "kurabu", vocab_eng: "club", stroke: "katakanaku.gif", vocabImg: "kurabu.png" },
  { kana: "ケ", romaji: "ke", mnemonic: "Rotated K.", vocab_jp: "ケーキ", vocab_romaji: "keki", vocab_eng: "cake", stroke: "katakanake.gif", vocabImg: "keki.png" },
  { kana: "コ", romaji: "ko", mnemonic: "Broken cup.", vocab_jp: "コーヒー", vocab_romaji: "koohii", vocab_eng: "coffee", stroke: "katakanako.gif", vocabImg: "kohi.png" },

  { kana: "サ", romaji: "sa", mnemonic: "See-saw.", vocab_jp: "サンド", vocab_romaji: "sando", vocab_eng: "sandwich", stroke: "katakanasa.gif", vocabImg: "sando.png" },
  { kana: "シ", romaji: "shi", mnemonic: "Side-eyes → she → shi.", vocab_jp: "シート", vocab_romaji: "shiito", vocab_eng: "seat", stroke: "katakanashi.gif", vocabImg: "shito.png" },
  { kana: "ス", romaji: "su", mnemonic: "Doing a split (su-plit).", vocab_jp: "スーパー", vocab_romaji: "suupaa", vocab_eng: "supermarket", stroke: "katakanasu.gif", vocabImg: "supa.png" },
  { kana: "セ", romaji: "se", mnemonic: "Mama setting baby.", vocab_jp: "セーター", vocab_romaji: "seetaa", vocab_eng: "sweater", stroke: "katakanase.gif", vocabImg: "seta.png" },
  { kana: "ソ", romaji: "so", mnemonic: "She who lost an eye (so).", vocab_jp: "ソーダ", vocab_romaji: "sooda", vocab_eng: "soda", stroke: "katakanaso.gif", vocabImg: "soda.png" },

  { kana: "タ", romaji: "ta", mnemonic: "Little t + big A = TA.", vocab_jp: "タクシー", vocab_romaji: "takushii", vocab_eng: "taxi", stroke: "katakanata.gif", vocabImg: "takushi.png" },
  { kana: "チ", romaji: "chi", mnemonic: "Cheating on exam → chi.", vocab_jp: "チーズ", vocab_romaji: "chiizu", vocab_eng: "cheese", stroke: "katakanachi.gif", vocabImg: "cheese.png" },
  { kana: "ツ", romaji: "tsu", mnemonic: "Eyes looking at you → tsu!", vocab_jp: "ツアー", vocab_romaji: "tsuaa", vocab_eng: "tour", stroke: "katakanatsu.gif", vocabImg: "tsua.png" },
  { kana: "テ", romaji: "te", mnemonic: "Telephone pole.", vocab_jp: "テスト", vocab_romaji: "tesuto", vocab_eng: "test", stroke: "katakanate.gif", vocabImg: "tesuto.png" },
  { kana: "ト", romaji: "to", mnemonic: "Lowercase t pointing right.", vocab_jp: "トマト", vocab_romaji: "tomato", vocab_eng: "tomato", stroke: "katakanato.gif", vocabImg: "tomato.png" },

  { kana: "ナ", romaji: "na", mnemonic: "Looks like T… NA-h almost.", vocab_jp: "ナイフ", vocab_romaji: "naifu", vocab_eng: "knife", stroke: "katakanana.gif", vocabImg: "naifu.png" },
  { kana: "ニ", romaji: "ni", mnemonic: "Two strokes → ni (two).", vocab_jp: "ニュース", vocab_romaji: "nyuusu", vocab_eng: "news", stroke: "katakanani.gif", vocabImg: "nyusu.png" },
  { kana: "ヌ", romaji: "nu", mnemonic: "New sword with tassel.", vocab_jp: "ヌードル", vocab_romaji: "nuudoru", vocab_eng: "noodles", stroke: "katakananu.gif", vocabImg: "nudoru.png" },
  { kana: "ネ", romaji: "ne", mnemonic: "Looks like necktie.", vocab_jp: "ネット", vocab_romaji: "netto", vocab_eng: "internet", stroke: "katakanane.gif", vocabImg: "netto.png" },
  { kana: "ノ", romaji: "no", mnemonic: "Person refusing… NO.", vocab_jp: "ノート", vocab_romaji: "nooto", vocab_eng: "notebook", stroke: "katakanano.gif", vocabImg: "noto.png" },

  { kana: "ハ", romaji: "ha", mnemonic: "Manga 'ha ha ha!' lines.", vocab_jp: "ハンバーガー", vocab_romaji: "hanbaagaa", vocab_eng: "hamburger", stroke: "katakanaha.gif", vocabImg: "hanbaga.png" },
  { kana: "ヒ", romaji: "hi", mnemonic: "Person waving Hi!", vocab_jp: "ヒーロー", vocab_romaji: "hiiro", vocab_eng: "hero", stroke: "katakanahi.gif", vocabImg: "hiro.png" },
  { kana: "フ", romaji: "fu", mnemonic: "Half smiling 'fufufu'", vocab_jp: "フード", vocab_romaji: "fuudo", vocab_eng: "food", stroke: "katakanafu.gif", vocabImg: "food.png" },
  { kana: "ヘ", romaji: "he", mnemonic: "Hanging off cliff yelling HELP!", vocab_jp: "ヘルメット", vocab_romaji: "herumetto", vocab_eng: "helmet", stroke: "katakanahe.gif", vocabImg: "helmet.png" },
  { kana: "ホ", romaji: "ho", mnemonic: "Holy cross.", vocab_jp: "ホテル", vocab_romaji: "hoteru", vocab_eng: "hotel", stroke: "katakanaho.gif", vocabImg: "hoteru.png" },

  { kana: "マ", romaji: "ma", mnemonic: "Side of a breast → mama.", vocab_jp: "マスク", vocab_romaji: "masuku", vocab_eng: "mask", stroke: "katakanama.gif", vocabImg: "masuku.png" },
  { kana: "ミ", romaji: "mi", mnemonic: "Do re mi → 3 strokes.", vocab_jp: "ミルク", vocab_romaji: "miruku", vocab_eng: "milk", stroke: "katakanami.gif", vocabImg: "miruku.png" },
  { kana: "ム", romaji: "mu", mnemonic: "Flexing muscles.", vocab_jp: "ムービー", vocab_romaji: "muubii", vocab_eng: "movie", stroke: "katakanamu.gif", vocabImg: "mubi.png" },
  { kana: "メ", romaji: "me", mnemonic: "Metal sword.", vocab_jp: "メール", vocab_romaji: "meeru", vocab_eng: "mail", stroke: "katakaname.gif", vocabImg: "meru.png" },
  { kana: "モ", romaji: "mo", mnemonic: "Ni + mo = finding ni mo.", vocab_jp: "モデル", vocab_romaji: "moderu", vocab_eng: "model", stroke: "katakanamo.gif", vocabImg: "moderu.png" },

  { kana: "ヤ", romaji: "ya", mnemonic: "Looks like や.", vocab_jp: "ヤード", vocab_romaji: "yaado", vocab_eng: "yard", stroke: "katakanaya.gif", vocabImg: "yado.png" },
  { kana: "ユ", romaji: "yu", mnemonic: "Number one → yu are number one.", vocab_jp: "ユニフォーム", vocab_romaji: "yunifo-mu", vocab_eng: "uniform", stroke: "katakanayu.gif", vocabImg: "yunifomu.png" },
  { kana: "ヨ", romaji: "yo", mnemonic: "'Yo, this is backwards E!'", vocab_jp: "ヨーグルト", vocab_romaji: "yooguruto", vocab_eng: "yogurt", stroke: "katakanayo.gif", vocabImg: "yoguruto.png" },

  { kana: "ラ", romaji: "ra", mnemonic: "Ramen bowl.", vocab_jp: "ラジオ", vocab_romaji: "rajio", vocab_eng: "radio", stroke: "katakanara.gif", vocabImg: "rajio.png" },
  { kana: "リ", romaji: "ri", mnemonic: "Richard’s right ear.", vocab_jp: "リング", vocab_romaji: "ringu", vocab_eng: "ring", stroke: "katakanari.gif", vocabImg: "ringu.png" },
  { kana: "ル", romaji: "ru", mnemonic: "Road → ru.", vocab_jp: "ルール", vocab_romaji: "ruuru", vocab_eng: "rule", stroke: "katakanaru.gif", vocabImg: "ruru.png" },
  { kana: "レ", romaji: "re", mnemonic: "L of lemon.", vocab_jp: "レモン", vocab_romaji: "remon", vocab_eng: "lemon", stroke: "katakanare.gif", vocabImg: "remon.png" },
  { kana: "ロ", romaji: "ro", mnemonic: "Robot head.", vocab_jp: "ロボット", vocab_romaji: "robotto", vocab_eng: "robot", stroke: "katakanaro.gif", vocabImg: "robotto.png" },

  { kana: "ワ", romaji: "wa", mnemonic: "Water faucet → wa.", vocab_jp: "ワイン", vocab_romaji: "wain", vocab_eng: "wine", stroke: "katakanawa.gif", vocabImg: "wain.png" },
  { kana: "ヲ", romaji: "wo", mnemonic: "Rotate → w → whoa!", vocab_jp: "ウォッカ", vocab_romaji: "wokka", vocab_eng: "vodka", stroke: "katakanawo.gif", vocabImg: "wokka.png" },

  { kana: "ン", romaji: "n", mnemonic: "Yawning person lying down.", vocab_jp: "スプーン", vocab_romaji: "supuun", vocab_eng: "spoon", stroke: "katakanan.gif", vocabImg: "supun.png" }
];

const activeData = (typeParam === "katakana") ? kataData : hiraData;

// find the requested index or default to 0
let currentIndex = activeData.findIndex(k => k.kana === requestedKana);
if (currentIndex === -1) currentIndex = 0;

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

/* master/unmaster toggle */
document.getElementById("masterBtn").onclick = () => {
  if (!currentKanaObj) return;

  const kana = currentKanaObj.kana;
  const cacheKey = `${kana}|${typeParam}`;
  const newAction = (currentMastery === 2) ? "unmaster" : "master";

  // 🔵 Optimistically update UI instantly
  currentMastery = (newAction === "master" ? 2 : 0);

  if (currentMastery === 2) {
    masteredCache[cacheKey] = true;
  } else {
    delete masteredCache[cacheKey];
  }

  setStarState(currentMastery === 2);

  // 🔵 Send request to server
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

      if (currentMastery === 2) {
        masteredCache[cacheKey] = true;
      } else {
        delete masteredCache[cacheKey];
      }

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


/* master/unmaster toggle */
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

/* initial load */
displayKana(currentIndex);
</script>
</body>
</html>