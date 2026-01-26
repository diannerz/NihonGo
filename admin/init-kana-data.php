<?php
require __DIR__ . '/admin-functions.php';
require_admin();

$hiragana_data = [
  ["あ", "a", "When the fish got stabbed by the sword, it went a!", "あめ", "ame", "candy / rain"],
  ["い", "i", "Two eels swimming around each other. Eek!", "いぬ", "inu", "dog"],
  ["う", "u", "Just Latin U tilting left with a line on top.", "うさぎ", "usagi", "bunny"],
  ["え", "e", "Looks like the number '4' rotated.", "えんぴつ", "enpitsu", "pencil"],
  ["お", "o", "Hand holding a sword writing O.", "おにぎり", "onigiri", "rice ball"],
  ["か", "ka", "Arm of the K is falling down.", "かばん", "kaban", "bag"],
  ["き", "ki", "Looks like a house key.", "き", "ki", "tree"],
  ["く", "ku", "Coo-coo bird mouth.", "くるま", "kuruma", "car"],
  ["け", "ke", "Looks like a KEg.", "けむし", "kemushi", "caterpillar"],
  ["こ", "ko", "Two koi swimming in a pond.", "こま", "koma", "spinning top"],
  ["さ", "sa", "Looks like a smiling monkey.", "さる", "saru", "monkey"],
  ["し", "shi", "Looks like a fishing hook.", "しんぶん", "shinbun", "newspaper"],
  ["す", "su", "Slurping noodle shape.", "すいか", "suika", "watermelon"],
  ["せ", "se", "Mama setting a baby on its lap.", "せんべい", "senbei", "rice cracker"],
  ["そ", "so", "SOap — motion you'd wash your belly with in zigzag.", "そら", "sora", "sky"],
  ["た", "ta", "Looks like a t with a small o.", "たまご", "tamago", "egg"],
  ["ち", "chi", "Looks like the number 5.", "ちず", "chizu", "map"],
  ["つ", "tsu", "Looks like a TSUnami wave.", "つき", "tsuki", "moon"],
  ["て", "te", "It looks like a T.", "てがみ", "tegami", "letter"],
  ["と", "to", "Your Tooth Touching your TOngue.", "とり", "tori", "bird"],
  ["な", "na", "Person throwing something saying: NA, I don't need this.", "なみだ", "namida", "tears"],
  ["に", "ni", "Two little brothers beside older brother.", "にく", "niku", "meat"],
  ["ぬ", "nu", "Looks like noodles with chopsticks.", "ぬの", "nuno", "cloth"],
  ["ね", "ne", "Looks like a cat stretching.", "ねこ", "neko", "cat"],
  ["の", "no", "Looks like NO with o inside n.", "のり", "nori", "seaweed"],
  ["は", "ha", "Top looks like H, bottom like small a.", "はな", "hana", "flower"],
  ["ひ", "hi", "Looks like a smile when you say hi.", "ひかり", "hikari", "light"],
  ["ふ", "fu", "Looks like someone blowing raspberries.", "ふね", "fune", "ship"],
  ["へ", "he", "Looks like a heel.", "へび", "hebi", "snake"],
  ["ほ", "ho", "Flip the strokes sideways → ho.", "ほし", "hoshi", "star"],
  ["ま", "ma", "Looks like ho without first stroke.", "まど", "mado", "window"],
  ["み", "mi", "Looks like the number 21.", "みず", "mizu", "water"],
  ["む", "mu", "Looks like a cow's nose (moo).", "むし", "mushi", "insect"],
  ["め", "me", "Looks like an eye (me).", "めがね", "megane", "glasses"],
  ["も", "mo", "Looks like a MOp sweeping across the floor.", "もり", "mori", "forest"],
  ["や", "ya", "Looks like someone punching shouting YA!", "やま", "yama", "mountain"],
  ["ゆ", "yu", "Looks like someone hugging → yu!", "ゆき", "yuki", "snow"],
  ["よ", "yo", "Looks like a YO-yo string.", "よる", "yoru", "night"],
  ["ら", "ra", "Bowl of Ramen with spoon.", "らいおん", "raion", "lion"],
  ["り", "ri", "Looks like a RIVER.", "りす", "risu", "squirrel"],
  ["る", "ru", "Turn sideways → looks like NO. No RUles.", "るす", "rusu", "absence"],
  ["れ", "re", "A ray of sunshine.", "れいぞうこ", "reizoko", "fridge"],
  ["ろ", "ro", "Looks like RU without circle.", "ろうそく", "rousoku", "candle"],
  ["わ", "wa", "Looks like Wario's dumpy.", "わに", "wani", "crocodile"],
  ["を", "wo", "Stickman sitting on a worm.", "を", "wo", "object particle"],
  ["ん", "n", "Looks like lowercase n.", "ほん", "hon", "book"]
];

// Katakana mapping
$katakana_map = [
  "あ" => "ア", "い" => "イ", "う" => "ウ", "え" => "エ", "お" => "オ",
  "か" => "カ", "き" => "キ", "く" => "ク", "け" => "ケ", "こ" => "コ",
  "さ" => "サ", "し" => "シ", "す" => "ス", "せ" => "セ", "そ" => "ソ",
  "た" => "タ", "ち" => "チ", "つ" => "ツ", "て" => "テ", "と" => "ト",
  "な" => "ナ", "に" => "ニ", "ぬ" => "ヌ", "ね" => "ネ", "の" => "ノ",
  "は" => "ハ", "ひ" => "ヒ", "ふ" => "フ", "へ" => "ヘ", "ほ" => "ホ",
  "ま" => "マ", "み" => "ミ", "む" => "ム", "め" => "メ", "も" => "モ",
  "や" => "ヤ", "ゆ" => "ユ", "よ" => "ヨ",
  "ら" => "ラ", "り" => "リ", "る" => "ル", "れ" => "レ", "ろ" => "ロ",
  "わ" => "ワ", "を" => "ヲ", "ん" => "ン"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    $inserted = 0;
    $skipped = 0;

    // Insert Hiragana
    foreach ($hiragana_data as $data) {
        $stmt = $pdo->prepare('
            INSERT INTO kana_flashcards 
            (kana_char, romaji, kana_type, mnemonic, vocab_jp, vocab_romaji, vocab_eng)
            VALUES (:kana, :romaji, :type, :mnemonic, :vocab_jp, :vocab_romaji, :vocab_eng)
            ON DUPLICATE KEY UPDATE mnemonic = VALUES(mnemonic)
        ');
        $result = $stmt->execute([
            ':kana' => $data[0],
            ':romaji' => $data[1],
            ':type' => 'hiragana',
            ':mnemonic' => $data[2],
            ':vocab_jp' => $data[3],
            ':vocab_romaji' => $data[4],
            ':vocab_eng' => $data[5]
        ]);
        $inserted += $stmt->rowCount();
    }

    // Insert Katakana
    foreach ($hiragana_data as $data) {
        $katakana_char = $katakana_map[$data[0]];
        $stmt = $pdo->prepare('
            INSERT INTO kana_flashcards 
            (kana_char, romaji, kana_type, mnemonic, vocab_jp, vocab_romaji, vocab_eng)
            VALUES (:kana, :romaji, :type, :mnemonic, :vocab_jp, :vocab_romaji, :vocab_eng)
            ON DUPLICATE KEY UPDATE mnemonic = VALUES(mnemonic)
        ');
        $result = $stmt->execute([
            ':kana' => $katakana_char,
            ':romaji' => $data[1],
            ':type' => 'katakana',
            ':mnemonic' => $data[2],
            ':vocab_jp' => $data[3],
            ':vocab_romaji' => $data[4],
            ':vocab_eng' => $data[5]
        ]);
        $inserted += $stmt->rowCount();
    }

    echo "<div style='background: #2ecc71; color: white; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h2>✓ Success!</h2>";
    echo "<p>Inserted/Updated $inserted kana records</p>";
    echo "<p><a href='kana-charts.php' style='color: white; text-decoration: none; font-weight: bold;'>Go to Kana Charts →</a></p>";
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Initialize Kana Data</title>
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin-style.css">
  <style>
    .init-container {
      max-width: 600px;
      margin: 100px auto;
      background: var(--panel-bg);
      border-radius: 48px;
      padding: 36px 42px;
      text-align: center;
    }
    .init-container h2 {
      color: white;
      font-size: 2rem;
      margin-bottom: 16px;
    }
    .init-container p {
      color: #e7fbf7;
      font-size: 1.1rem;
      margin-bottom: 24px;
      line-height: 1.6;
    }
    .init-btn {
      background: var(--accent-mint);
      color: #244850;
      border: none;
      padding: 14px 28px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .init-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(175, 230, 210, 0.3);
    }
  </style>
</head>
<body>
  <div class="init-container">
    <h2>Initialize Kana Flashcard Data</h2>
    <p>This will populate your kana_flashcards table with all 92 kana (46 hiragana + 46 katakana) with mnemonics and vocabulary examples.</p>
    <p style="color: #ffcc00; font-weight: bold;">⚠️ This action will create or update all kana records.</p>
    
    <form method="POST">
      <input type="hidden" name="confirm" value="yes">
      <button type="submit" class="init-btn">Populate Kana Data</button>
    </form>
  </div>
</body>
</html>
