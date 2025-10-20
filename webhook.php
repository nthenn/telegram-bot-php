<?php
$token = getenv("BOT_TOKEN");
$api = "https://api.telegram.org/bot$token/";

$update = json_decode(file_get_contents("php://input"), true);

if (!$update) {
    echo "✅ HiddenKino bot ishlayapti!";
    exit;
}

$chat_id = $update["message"]["chat"]["id"];
$message = $update["message"];

$dbFile = __DIR__ . "/films.json";
if (!file_exists($dbFile)) file_put_contents($dbFile, "{}");
$films = json_decode(file_get_contents($dbFile), true);

if (isset($message["video"])) {
    $caption = trim($message["caption"]);
    if ($caption && preg_match("/#(\d+)/", $caption, $match)) {
        $filmId = $match[1];
        $fileId = $message["video"]["file_id"];
        $films[$filmId] = $fileId;
        file_put_contents($dbFile, json_encode($films, JSON_PRETTY_PRINT));
        file_get_contents($api . "sendMessage?chat_id=$chat_id&text=✅ Film #$filmId saqlandi!");
    } else {
        file_get_contents($api . "sendMessage?chat_id=$chat_id&text=Iltimos, videoga caption sifatida film raqamini yozing. Masalan: #101");
    }
}

if (isset($message["text"])) {
    $text = trim($message["text"]);
    if (preg_match("/#(\d+)/", $text, $match)) {
        $filmId = $match[1];
        if (isset($films[$filmId])) {
            $fileId = $films[$filmId];
            file_get_contents($api . "sendVideo?chat_id=$chat_id&video=$fileId");
        } else {
            file_get_contents($api . "sendMessage?chat_id=$chat_id&text=❌ Film #$filmId topilmadi!");
        }
    } elseif ($text == "/start") {
        file_get_contents($api . "sendMessage?chat_id=$chat_id&text=Salom! 🎥 Menga filmni yuboring va caption sifatida masalan #101 deb yozing. Keyin shunchaki #101 deb yozsangiz filmni qayta olasiz.");
    }
}
?>
