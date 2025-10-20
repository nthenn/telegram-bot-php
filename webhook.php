<?php
// ✅ Hiddenkino_bot - 2025-yil to‘liq ishlaydigan versiya

$botToken = "8324379957:AAG3NTnOJFzvO7IesQ9jMXgP7uXBUFTXAEk";
$apiURL = "https://api.telegram.org/bot$botToken/";

// Telegram yuborgan JSON ma'lumotni o‘qiymiz
$update = json_decode(file_get_contents("php://input"), true);

if (isset($update["message"])) {
    $chatId = $update["message"]["chat"]["id"];
    $text = trim($update["message"]["text"]);

    if ($text == "/start") {
        $reply = "👋 Salom! Men @Hiddenkino_bot.\n🎬 Menga kino nomini yozing, men sizga topib beraman!";
    } else {
        $reply = "🔎 Siz yozdingiz: $text\n(Topish funksiyasi tez orada yoqiladi 🎥)";
    }

    file_get_contents($apiURL . "sendMessage?chat_id=$chatId&text=" . urlencode($reply));
}

// Faqat test uchun (brauzer orqali kirilganda)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "✅ Hiddenkino bot server ishlayapti (Render 2025)";
}
?>
