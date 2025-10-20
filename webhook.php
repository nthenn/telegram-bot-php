<?php
// 🎬 HiddenKino Bot — Telegram ichidagi kinolarni saqlash va yuborish

$botToken = "8324379957:AAG3NTnOJFzvO7IesQ9jMXgP7uXBUFTXAEk";
$api = "https://api.telegram.org/bot$botToken/";

// Baza (saqlangan kinolar ro'yxati)
$dbFile = __DIR__ . "/films.json";
if (!file_exists($dbFile)) file_put_contents($dbFile, "{}");

$update = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "✅ HiddenKino bot ishlayapti!";
    exit;
}

if (isset($update["message"])) {
    $msg = $update["message"];
    $chatId = $msg["chat"]["id"];
    $text = trim($msg["text"] ?? "");
    $video = $msg["video"]["file_id"] ?? null;

    // 🎥 Agar video yuborilgan bo‘lsa (film qo‘shish uchun)
    if ($video) {
        $caption = $msg["caption"] ?? "";
        if (preg_match('/#(\d+)/', $caption, $m)) {
            $id = $m[1];
            $db = json_decode(file_get_contents($dbFile), true);
            $db[$id] = $video;
            file_put_contents($dbFile, json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            send($chatId, "✅ Film #$id saqlandi!");
        } else {
            send($chatId, "ℹ️ Film qo‘shish uchun video captioniga masalan: #413 deb yozing.");
        }
        exit;
    }

    // 👋 /start buyrug‘i
    if ($text === "/start") {
        send($chatId, "👋 Salom! Menga kino raqamini yozing (#123 kabi), men sizga filmni yuboraman!");
        exit;
    }

    // 🔍 Kino raqami orqali qidirish (#123)
    if (preg_match('/#(\d+)/', $text, $m)) {
        $id = $m[1];
        $db = json_decode(file_get_contents($dbFile), true);

        if (isset($db[$id])) {
            $file_id = $db[$id];
            sendVideo($chatId, $file_id, "🎬 Film #$id");
        } else {
            send($chatId, "❌ Film topilmadi: #$id");
        }
        exit;
    }

    // Boshqa hollarda
    send($chatId, "ℹ️ Kino olish uchun #raqam yozing yoki yangi video yuboring (#ID bilan captionda).");
}

function send($chatId, $text) {
    global $api;
    file_get_contents($api . "sendMessage?chat_id=$chatId&text=" . urlencode($text));
}

function sendVideo($chatId, $file_id, $caption = "") {
    global $api;
    $data = [
        'chat_id' => $chatId,
        'video' => $file_id,
        'caption' => $caption
    ];
    $options = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => http_build_query($data)]];
    file_get_contents($api . "sendVideo", false, stream_context_create($options));
}
?>
