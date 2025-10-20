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

// 🧑‍💻 Faqat sizning ID’ingiz (siz /start bosganingizda ID chiqadi, hozircha vaqtincha 8324379957 qo‘yamiz)
$admin_id = 8324379957;

$dbFile = DIR . "/films.json";
if (!file_exists($dbFile)) file_put_contents($dbFile, "{}");
$films = json_decode(file_get_contents($dbFile), true);

// 🎥 Agar admin video yuborsa — saqlanadi
if (isset($message["video"])) {
    if ($chat_id == $admin_id) {
        $caption = trim($message["caption"]);
        if ($caption && preg_match("/#(\d+)/", $caption, $match)) {
            $filmId = $match[1];
            $fileId = $message["video"]["file_id"];
            $films[$filmId] = $fileId;
            file_put_contents($dbFile, json_encode($films, JSON_PRETTY_PRINT));
            file_get_contents($api . "sendMessage?chat_id=$chat_id&text=✅ Film #$filmId saqlandi!");
        } else {
            file_get_contents($api . "sendMessage?chat_id=$chat_id&text=ℹ️ Iltimos, videoga caption sifatida masalan #101 deb yozing.");
        }
    } else {
        file_get_contents($api . "sendMessage?chat_id=$chat_id&text=🚫 Siz film yuklay olmaysiz. Faqat admin yubora oladi.");
    }
}

// 🔍 Agar foydalanuvchi matn yuborsa
if (isset($message["text"])) {
    $text = trim($message["text"]);

    // 🎥 Raqam orqali film qidirish
    if (preg_match("/#(\d+)/", $text, $match)) {
        $filmId = $match[1];
        if (isset($films[$filmId])) {
            $fileId = $films[$filmId];
            file_get_contents($api . "sendVideo?chat_id=$chat_id&video=$fileId");
        } else {
            file_get_contents($api . "sendMessage?chat_id=$chat_id&text=❌ Film #$filmId topilmadi!");
        }
    }
    // 👋 Start buyrug‘i
    elseif ($text == "/start") {
        if ($chat_id == $admin_id) {
            file_get_contents($api . "sendMessage?chat_id=$chat_id&text=Salom, admin! 🎬 Film yuboring va caption sifatida #raqam yozing. Masalan: #101");
        } else {
            file_get_contents($api . "sendMessage?chat_id=$chat_id&text=Salom! 👋 Menga film raqamini yozing, masalan #101 — bot sizga filmni yuboradi 🎥");
        }
    }
}
?>
