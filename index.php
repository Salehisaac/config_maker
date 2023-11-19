<?php

use app\TelegramBot;

require_once 'TelegramBot.php';

$token = "6813131583:AAHhfKYcObFrXsuzZ-7oZD_ldi6X2rU4K-k";
$action = "sendMessage";
$text = 'salam';

$bot = new TelegramBot($token);

$data = file_get_contents("php://input");
$update = json_decode($data, true);

// Check if the 'message' key is present in the update
if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];

    $url = "https://api.telegram.org/bot$token/$action";

    $data = [
        'chat_id' => $chatId,
        'text' => $text,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    }

    echo $response;
    curl_close($ch);
} else {
    echo 'No valid message in the update.';
}
