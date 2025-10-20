<?php
// 🎬 Hiddenkino Bot (kino raqam orqali topuvchi versiya)

// Token
$botToken = "8324379957:AAG3NTnOJFzvO7IesQ9jMXgP7uXBUFTXAEk";
$api = "https://api.telegram.org/bot$botToken/";

// 🔸 Kino bazasi uchun JSON fayl (Render’da ham saqlanadi)
$dbFile = __DIR__ . "/movies.json";
if (!file_exists($dbFile)) file_put_contents($dbFile, "{}");

// 🔸 Telegram so‘rovini o‘qish
$update = json_decode(file_get_contents("php://input"), true);

// 🔸 Brauzerda ochilganda test uchun
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "✅ Hiddenkino bot (kino versiyasi) ishlayapti.";
    exit;
}

// 🔸 Foydalanuvchi xabari
if (isset($update["message"])) {
    $msg = $update["message"];
    $chatId = $msg["chat"]["id"];
    $text = trim($msg["text"] ?? "");

    // /start
    if ($text === "/start") {
        send($chatId, "👋 Salom! Men @Hiddenkino_bot.\n🎥 Kinoning raqamini yozing (#413 kabi), men topib beraman!");
        exit;
    }

    // /add (faqat siz qo‘shasiz)
    if (strpos($text, "/add") === 0) {
        // Format: /add 413 | Nom | Link
        if (preg_match('/^\/add\s+(\d+)\s*\|\s*(.+?)\s*\|\s*(https?:\/\/\S+)/', $text, $m)) {
            [$all, $id, $title, $url] = $m;
            $db = json_decode(file_get_contents($dbFile), true);
            $db[$id] = ["title" => $title, "url" => $url];
            file_put_contents($dbFile, json_encode($db, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            send($chatId, "✅ Kino qo‘shildi: #$id — $title");
        } else {
            send($chatId, "❗ Format noto‘g‘ri!\nTo‘g‘ri: /add 413 | Kino nomi | https://link");
        }
        exit;
    }

    // #raqam yuborilsa
    if (preg_match('/#(\d+)/', $text, $m)) {
        $id = $m[1];
        $db = json_decode(file_get_contents($dbFile), true);
        if (isset($db[$id])) {
            $film = $db[$id];
            send($chatId, "🎬 {$film['title']} (#$id)\n📽️ {$film['url']}");
        } else {
            send($chatId, "❌ #$id kodi topilmadi.");
        }
        exit;
    }

    // Boshqa so‘zlar
    send($chatId, "ℹ️ Kino topish uchun raqam yuboring (#413) yoki yangi kino qo‘shing (/add).");
}

// 🔸 Xabar yuboruvchi yordamchi funksiya
function send($chatId, $text) {
    global $api;
    file_get_contents($api . "sendMessage?chat_id=$chatId&text=" . urlencode($text));
}
?>
