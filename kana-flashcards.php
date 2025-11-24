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
/*
  TWO SEPARATE DATASETS
  One for HIRAGANA
  One for KATAKANA
*/

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
 { kana: "そ", romaji: "so", mnemonic: "SOap, just the motion you'd wash your belly with in a zigzag motion.", vocab_jp: "そら", vocab_romaji: "sora", vocab_eng: "sky", stroke: "hiraganaso.gif", vocabImg: "sora.png" },


  // TA row
  { kana: "た", romaji: "ta", mnemonic: "Looks like a t with a small o and it’s alphabetically the first T character", vocab_jp: "たまご", vocab_romaji: "tamago", vocab_eng: "egg", stroke: "hiraganata.gif", vocabImg: "tamago.png" },
  { kana: "ち", romaji: "chi", mnemonic: "Looks like the number 5", vocab_jp: "ちず", vocab_romaji: "chizu", vocab_eng: "map", stroke: "hiraganachi.gif", vocabImg: "chizu.png" },
  { kana: "つ", romaji: "tsu", mnemonic: "Looks like TSUnami wave", vocab_jp: "つき", vocab_romaji: "tsuki", vocab_eng: "moon", stroke: "hiraganatsu.gif", vocabImg: "tsuki.png" },
  { kana: "て", romaji: "te", mnemonic: "It looks like a T", vocab_jp: "てがみ", vocab_romaji: "tegami", vocab_eng: "letter", stroke: "hiraganate.gif", vocabImg: "tegami.png" },
  { kana: "と", romaji: "to", mnemonic: "Your Tooth Touching your TOngue.", vocab_jp: "とり", vocab_romaji: "tori", vocab_eng: "bird", stroke: "hiraganato.gif", vocabImg: "tori.png" },


  // NA row
  { kana: "な", romaji: "na", mnemonic: "Left person throwing something away saying: NA, I don't need this anymore", vocab_jp: "なつ", vocab_romaji: "natsu", vocab_eng: "summer", stroke: "hiraganana.gif", vocabImg: "natsu.png" },
  { kana: "に", romaji: "ni", mnemonic: "Looks like there's two little brothers next to their older brother", vocab_jp: "にく", vocab_romaji: "niku", vocab_eng: "meat", stroke: "hiraganani.gif", vocabImg: "niku.png" },
  { kana: "ぬ", romaji: "nu", mnemonic: "Looks like noodles with chopsticks", vocab_jp: "ぬの", vocab_romaji: "nuno", vocab_eng: "cloth", stroke: "hiragananu.gif", vocabImg: "nuno.png" },
  { kana: "ね", romaji: "ne", mnemonic: "Looks like a cat stretching", vocab_jp: "ねこ", vocab_romaji: "neko", vocab_eng: "cat", stroke: "hiraganane.gif", vocabImg: "neko.png" },
  { kana: "の", romaji: "no", mnemonic: " Looks like no but witht the o inside the n", vocab_jp: "のり", vocab_romaji: "nori", vocab_eng: "seaweed", stroke: "hiraganano.gif", vocabImg: "nori.png" },


  // HA row
  { kana: "は", romaji: "ha", mnemonic: "The top part looks like an H, the bottom right part looks like a squished a", vocab_jp: "はな", vocab_romaji: "hana", vocab_eng: "flower", stroke: "hiraganaha.gif", vocabImg: "hana.png" },
  { kana: "ひ", romaji: "hi", mnemonic: "Looks like a smile and you smile when you say “hi” to someone", vocab_jp: "ひかり", vocab_romaji: "hikari", vocab_eng: "light", stroke: "hiraganahi.gif", vocabImg: "hikari.png" },
  { kana: "ふ", romaji: "fu", mnemonic: "Looks like someone blowing raspberries.", vocab_jp: "ふね", vocab_romaji: "fune", vocab_eng: "ship", stroke: "hiraganaf u.gif", vocabImg: "fune.png" },
  { kana: "へ", romaji: "he", mnemonic: "Looks like a heel", vocab_jp: "へび", vocab_romaji: "hebi", vocab_eng: "snake", stroke: "hiraganahe.gif", vocabImg: "hebi.png" },
  { kana: "ほ", romaji: "ho", mnemonic: "Pretend the first stroke isn't there and flip the rest to the side and it says ho", vocab_jp: "ほし", vocab_romaji: "hoshi", vocab_eng: "star", stroke: "hiraganaho.gif", vocabImg: "hoshi.png" },


  // MA row
  { kana: "ま", romaji: "ma", mnemonic: "Looks like ho without the first stroke", vocab_jp: "まど", vocab_romaji: "mado", vocab_eng: "window", stroke: "hiraganama.gif", vocabImg: "mado.png" },
  { kana: "み", romaji: "mi", mnemonic: "Looks like the number 21.", vocab_jp: "みず", vocab_romaji: "mizu", vocab_eng: "water", stroke: "hiraganami.gif", vocabImg: "mizu.png" },

  { kana: "む", romaji: "mu", mnemonic: "Looks like a cow’s nose (moo)", vocab_jp: "むし", vocab_romaji: "mushi", vocab_eng: "insect", stroke: "hiraganamu.gif", vocabImg: "mushi.png" },
  { kana: "め", romaji: "me", mnemonic: "Looks like an eye (me)", vocab_jp: "めがね", vocab_romaji: "megane", vocab_eng: "glasses", stroke: "hiraganame.gif", vocabImg: "megane.png" },
  { kana: "も", romaji: "mo", mnemonic: "Looks like a MOp sweeping across the floor", vocab_jp: "もり", vocab_romaji: "mori", vocab_eng: "forest", stroke: "hiraganamo.gif", vocabImg: "mori.png" },


  // YA row
  { kana: "や", romaji: "ya", mnemonic: "It looks like a person punching and shouting: ya", vocab_jp: "やま", vocab_romaji: "yama", vocab_eng: "mountain", stroke: "hiraganaya.gif", vocabImg: "yama.png" },
  { kana: "ゆ", romaji: "yu", mnemonic: "One person is wrapping their arm around another saying: I want to hug yu!", vocab_jp: "ゆき", vocab_romaji: "yuki", vocab_eng: "snow", stroke: "hiraganayu.gif", vocabImg: "yuki.png" },
  { kana: "よ", romaji: "yo", mnemonic: "Looks like a YO-yo string.", vocab_jp: "よる", vocab_romaji: "yoru", vocab_eng: "night", stroke: "hiraganayo.gif", vocabImg: "yoru.png" },


  // RA row
  { kana: "ら", romaji: "ra", mnemonic: "A bowl of RAmen with a spoon sticking out from it.", vocab_jp: "らいおん", vocab_romaji: "raion", vocab_eng: "lion", stroke: "hiraganara.gif", vocabImg: "raion.png" },
  { kana: "り", romaji: "ri", mnemonic: "It looks like a RI ver!", vocab_jp: "りす", vocab_romaji: "risu", vocab_eng: "squirrel", stroke: "hiraganari.gif", vocabImg: "risu.png" },
  { kana: "る", romaji: "ru", mnemonic: "If you turnbit sideways it says no. So no RUles!", vocab_jp: "るす", vocab_romaji: "rusu", vocab_eng: "absence", stroke: "hiraganaru.gif", vocabImg: "rusu.png" },
  { kana: "れ", romaji: "re", mnemonic: "A RAY of sunshine across some mountains", vocab_jp: "れいぞうこ", vocab_romaji: "reizoko", vocab_eng: "fridge", stroke: "hiraganare.gif", vocabImg: "reizoko.png" },
  { kana: "ろ", romaji: "ro", mnemonic: "Looks like RU without the circle", vocab_jp: "ろうそく", vocab_romaji: "rousoku", vocab_eng: "candle", stroke: "hiraganaro.gif", vocabImg: "rousoku.png" },


  { kana: "わ", romaji: "wa", mnemonic: "Looks like Wario's big fat dumpy.", vocab_jp: "わに", vocab_romaji: "wani", vocab_eng: "crocodile", stroke: "hiraganawa.gif", vocabImg: "wani.png" },
  { kana: "を", romaji: "wo", mnemonic: "Stickman sitting on a WOrm", vocab_jp: "を", vocab_romaji: "wo", vocab_eng: "object particle", stroke: "hiraganawo.gif", vocabImg: "wo.png" },
  { kana: "ん", romaji: "n", mnemonic: "Looks a lot like a lowercase n.", vocab_jp: "ほん", vocab_romaji: "hon", vocab_eng: "book", stroke: "hiraganan.gif", vocabImg: "hon.png" }
];



const kataData = [
  // --- A row ---
  { kana: "ア", romaji: "a", mnemonic: "It kind of looks like an a-mbrella (umbrella).", vocab_jp: "アメリカ", vocab_romaji: "amerika", vocab_eng: "America", stroke: "katakanaa.gif", vocabImg: "amerika.png" },
  { kana: "イ", romaji: "i", mnemonic: "Internet wires hung on a pole.", vocab_jp: "インク", vocab_romaji: "inku", vocab_eng: "ink", stroke: "katakanai.gif", vocabImg: "inku.png" },
  { kana: "ウ", romaji: "u", mnemonic: "Just Latin U tilting to the left with a line on top.", vocab_jp: "ウイスキー", vocab_romaji: "uisuki", vocab_eng: "whiskey", stroke: "katakanau.gif", vocabImg: "uisuki.png" },
  { kana: "エ", romaji: "e", mnemonic: "Looks like a guy proposing, but he got interrupted half way and only said 'e'.", vocab_jp: "エレベーター", vocab_romaji: "erebeetaa", vocab_eng: "elevator", stroke: "katakanae.gif", vocabImg: "erebeta.png" },
  { kana: "オ", romaji: "o", mnemonic: "Bottom part is shaped like an onigiri without the nori.", vocab_jp: "オフィス", vocab_romaji: "ofisu", vocab_eng: "office", stroke: "katakanao.gif", vocabImg: "ofisu.png" },

  // --- KA row ---
  { kana: "カ", romaji: "ka", mnemonic: "Looks like the arm of the K is falling down", vocab_jp: "カメラ", vocab_romaji: "kamera", vocab_eng: "camera", stroke: "katakanaka.gif", vocabImg: "kamera.png" },
  { kana: "キ", romaji: "ki", mnemonic: "Looks like a scar", vocab_jp: "キッチン", vocab_romaji: "kitchin", vocab_eng: "kitchen", stroke: "katakanaki.gif", vocabImg: "kitchin.png" },
  { kana: "ク", romaji: "ku", mnemonic: "Looks like a thumbs up", vocab_jp: "クラブ", vocab_romaji: "kurabu", vocab_eng: "club", stroke: "katakanaku.gif", vocabImg: "kurabu.png" },
  { kana: "ケ", romaji: "ke", mnemonic: "Looks like a rotated K", vocab_jp: "ケーキ", vocab_romaji: "keki", vocab_eng: "cake", stroke: "katakanake.gif", vocabImg: "keki.png" },
  { kana: "コ", romaji: "ko", mnemonic: "Looks like broken cup", vocab_jp: "コーヒー", vocab_romaji: "kohi", vocab_eng: "coffee", stroke: "katakanako.gif", vocabImg: "kohi.png" },

  // --- SA row ---
  { kana: "サ", romaji: "sa", mnemonic: "Looks like two people on a see-saw.", vocab_jp: "サンド", vocab_romaji: "sando", vocab_eng: "sandwich", stroke: "katakanasa.gif", vocabImg: "sando.png" },
  { kana: "シ", romaji: "shi", mnemonic: "The short strokes are looking at someone else. (She → shi)", vocab_jp: "シート", vocab_romaji: "shiito", vocab_eng: "seat", stroke: "katakanashi.gif", vocabImg: "shito.png" },
  { kana: "ス", romaji: "su", mnemonic: "Looks like someone about to do a split. (SU-plit)", vocab_jp: "スーパー", vocab_romaji: "suupaa", vocab_eng: "supermarket", stroke: "katakanasu.gif", vocabImg: "supa.png" },
  { kana: "セ", romaji: "se", mnemonic: "Looks like a mama setting a baby on its lap.", vocab_jp: "セーター", vocab_romaji: "seetaa", vocab_eng: "sweater", stroke: "katakanase.gif", vocabImg: "seta.png" },
  { kana: "ソ", romaji: "so", mnemonic: "Looks like she (shi) who lost an eye.", vocab_jp: "ソーダ", vocab_romaji: "sooda", vocab_eng: "soda", stroke: "katakanaso.gif", vocabImg: "soda.png" },

  // --- TA row ---
  { kana: "タ", romaji: "ta", mnemonic: "Little T on the left + big A → TA.", vocab_jp: "タクシー", vocab_romaji: "takushii", vocab_eng: "taxi", stroke: "katakanata.gif", vocabImg: "takushi.png" },
  { kana: "チ", romaji: "chi", mnemonic: "Looks like someone cheating on an exam. (CHI-t)", vocab_jp: "チーズ", vocab_romaji: "chiizu", vocab_eng: "cheese", stroke: "katakanachi.gif", vocabImg: "cheese.png" },
  { kana: "ツ", romaji: "tsu", mnemonic: "The two short strokes look like eyes looking at you. TSU!", vocab_jp: "ツアー", vocab_romaji: "tsuaa", vocab_eng: "tour", stroke: "katakanatsu.gif", vocabImg: "tsua.png" },
  { kana: "テ", romaji: "te", mnemonic: "Looks like a telephone pole.", vocab_jp: "テスト", vocab_romaji: "tesuto", vocab_eng: "test", stroke: "katakanate.gif", vocabImg: "tesuto.png" },
  { kana: "ト", romaji: "to", mnemonic: "Looks like a lowercase t pointing TO the right.", vocab_jp: "トマト", vocab_romaji: "tomato", vocab_eng: "tomato", stroke: "katakanato.gif", vocabImg: "tomato.png" },

  // --- NA row ---
  { kana: "ナ", romaji: "na", mnemonic: "Looks like a T… NA-h, almost.", vocab_jp: "ナイフ", vocab_romaji: "naifu", vocab_eng: "knife", stroke: "katakanana.gif", vocabImg: "naifu.png" },
  { kana: "ニ", romaji: "ni", mnemonic: "Ni means two — these are just two lines.", vocab_jp: "ニュース", vocab_romaji: "nyuusu", vocab_eng: "news", stroke: "katakanani.gif", vocabImg: "nyusu.png" },
  { kana: "ヌ", romaji: "nu", mnemonic: "Looks like a new ('nu') sword with a tassel.", vocab_jp: "ヌードル", vocab_romaji: "nuudoru", vocab_eng: "noodles", stroke: "katakananu.gif", vocabImg: "nudor u.png" },
  { kana: "ネ", romaji: "ne", mnemonic: "Looks like a necktie.", vocab_jp: "ネット", vocab_romaji: "netto", vocab_eng: "internet/net", stroke: "katakanane.gif", vocabImg: "netto.png" },
  { kana: "ノ", romaji: "no", mnemonic: "Looks like the person refused to finish it: NO.", vocab_jp: "ノート", vocab_romaji: "nooto", vocab_eng: "notebook", stroke: "katakanano.gif", vocabImg: "noto.png" },

  // --- HA row ---
  { kana: "ハ", romaji: "ha", mnemonic: "Looks like manga “ha ha ha!” laugh lines.", vocab_jp: "ハンバーガー", vocab_romaji: "hanbaagaa", vocab_eng: "hamburger", stroke: "katakanaha.gif", vocabImg: "hanbaga.png" },
  { kana: "ヒ", romaji: "hi", mnemonic: "Looks like a sitting person waving 'Hi!'", vocab_jp: "ヒンロ", vocab_romaji: "hiiro", vocab_eng: "hint", stroke: "katakanahi.gif", vocabImg: "hiro.png" },
  { kana: "フ", romaji: "fu", mnemonic: "Looks like half of a laughing smile 'fufufu'", vocab_jp: "フード", vocab_romaji: "fudo", vocab_eng: "food", stroke: "katakanafu.gif", vocabImg: "food.png" },
  { kana: "ヘ", romaji: "he", mnemonic: "Looks like someone hanging off a cliff yelling 'HElp!'", vocab_jp: "ヘルメット", vocab_romaji: "herumetto", vocab_eng: "helmet", stroke: "katakanahe.gif", vocabImg: "helmet.png" },
  { kana: "ホ", romaji: "ho", mnemonic: "Looks like a holy cross in a church aisle.", vocab_jp: "ホテル", vocab_romaji: "hoteru", vocab_eng: "hotel", stroke: "katakanaho.gif", vocabImg: "hoteru.png" },

  // --- MA row ---
  { kana: "マ", romaji: "ma", mnemonic: "Looks like the side of a breast — mama.", vocab_jp: "マスク", vocab_romaji: "masuku", vocab_eng: "mask", stroke: "katakanama.gif", vocabImg: "masuku.png" },
  { kana: "ミ", romaji: "mi", mnemonic: "Do re mi — 3 notes, 3 lines.", vocab_jp: "ミルク", vocab_romaji: "miruku", vocab_eng: "milk", stroke: "katakanami.gif", vocabImg: "miruku.png" },
  { kana: "ム", romaji: "mu", mnemonic: "Looks like flexing MUscles.", vocab_jp: "ムービー", vocab_romaji: "muubii", vocab_eng: "movie", stroke: "katakanamu.gif", vocabImg: "mubi.png" },
  { kana: "メ", romaji: "me", mnemonic: "Looks like a metal sword — me(tal).", vocab_jp: "メール", vocab_romaji: "meeru", vocab_eng: "mail", stroke: "katakaname.gif", vocabImg: "meru.png" },
  { kana: "モ", romaji: "mo", mnemonic: "First strokes look like ‘ni’. Finding ‘ni’ mo.", vocab_jp: "モデル", vocab_romaji: "moderu", vocab_eng: "model", stroke: "katakanamo.gif", vocabImg: "moderu.png" },

  // --- YA row ---
  { kana: "ヤ", romaji: "ya", mnemonic: "Looks very similar to や.", vocab_jp: "ヤード", vocab_romaji: "yaado", vocab_eng: "yard", stroke: "katakanaya.gif", vocabImg: "yado.png" },
  { kana: "ユ", romaji: "yu", mnemonic: "Looks like a small number one. Yu are number one.", vocab_jp: "ユニフォーム", vocab_romaji: "yunifoome", vocab_eng: "uniform", stroke: "katakanayu.gif", vocabImg: "yunifomu.png" },
  { kana: "ヨ", romaji: "yo", mnemonic: "'Yo, why is this E backwards?'", vocab_jp: "ヨーグルト", vocab_romaji: "yooguruto", vocab_eng: "yogurt", stroke: "katakanayo.gif", vocabImg: "yoguruto.png" },

  // --- RA row ---
  { kana: "ラ", romaji: "ra", mnemonic: "A bowl of RAmen with pork.", vocab_jp: "ラジオ", vocab_romaji: "rajio", vocab_eng: "radio", stroke: "katakanara.gif", vocabImg: "rajio.png" },
  { kana: "リ", romaji: "ri", mnemonic: "Looks like Richard’s right ear.", vocab_jp: "リング", vocab_romaji: "ringu", vocab_eng: "ring", stroke: "katakanari.gif", vocabImg: "ringu.png" },
  { kana: "ル", romaji: "ru", mnemonic: "Looks like a road — and road in French sounds like 'ru'.", vocab_jp: "ルール", vocab_romaji: "ruuru", vocab_eng: "rule", stroke: "katakanaru.gif", vocabImg: "ruru.png" },
  { kana: "レ", romaji: "re", mnemonic: "It looks like the L of lemon.", vocab_jp: "レモン", vocab_romaji: "remon", vocab_eng: "lemon", stroke: "katakanare.gif", vocabImg: "remon.png" },
  { kana: "ロ", romaji: "ro", mnemonic: "Looks like a robot’s head.", vocab_jp: "ロボット", vocab_romaji: "robotto", vocab_eng: "robot", stroke: "katakanaro.gif", vocabImg: "robotto.png" },

  // --- WA row ---
  { kana: "ワ", romaji: "wa", mnemonic: "Looks like a faucet for (wa)ter.", vocab_jp: "ワイン", vocab_romaji: "wain", vocab_eng: "wine", stroke: "katakanawa.gif", vocabImg: "wain.png" },
  { kana: "ヲ", romaji: "wo", mnemonic: "Rotate it right — looks like a 'w'. Your reaction is 'whoa!'", vocab_jp: "ウォッカ", vocab_romaji: "wokka", vocab_eng: "vodka", stroke: "katakanawo.gif", vocabImg: "wokka.png" },

  // --- N ---
  { kana: "ン", romaji: "n", mnemonic: "Looks like a tired person may yawN and lie on their side.", vocab_jp: "スプーン", vocab_romaji: "supuun", vocab_eng: "spoon", stroke: "katakanan.gif", vocabImg: "supun.png" }
];


// -------------------------------
// Helper functions
// -------------------------------
function urlParam(name) {
  const p = new URLSearchParams(window.location.search);
  return p.get(name);
}

const requestedKana = urlParam("kana");
const typeParam = urlParam("type") || "hiragana";

// choose dataset
const activeData = typeParam === "katakana" ? kataData : hiraData;

// find the correct index
let currentIndex = activeData.findIndex(k => k.kana === requestedKana);
if (currentIndex === -1) currentIndex = 0;

// -------------------------------
// Display function
// -------------------------------
function displayKana(i) {
  const k = activeData[i];
  document.getElementById("kanaChar").textContent = k.kana;
  document.getElementById("romaji").textContent = k.romaji;
  document.getElementById("mnemonicText").textContent = k.mnemonic;
  document.getElementById("vocabKana").textContent = k.vocab_jp;
  document.getElementById("vocabRomaji").textContent = "(" + k.vocab_romaji + ")";
  document.getElementById("vocabEng").textContent = k.vocab_eng;
  document.getElementById("strokeImg").src = "/NihonGo/images/" + k.stroke;
  document.getElementById("vocabImg").src = "/NihonGo/images/" + k.vocabImg;

  saveProgress(k.kana, typeParam);
}

// -------------------------------
// Next / Previous
// -------------------------------
document.getElementById("nextBtn").onclick = () => {
  currentIndex = (currentIndex + 1) % activeData.length;
  displayKana(currentIndex);
};
document.getElementById("prevBtn").onclick = () => {
  currentIndex = (currentIndex - 1 + activeData.length) % activeData.length;
  displayKana(currentIndex);
};

// INITIAL DISPLAY
displayKana(currentIndex);

// -------------------------------
// Save Progress
// -------------------------------
function saveProgress(kanaChar, type) {
  fetch("php/save_progress.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ kana: kanaChar, type: type })
  });
}

</script>
</body>
</html>
