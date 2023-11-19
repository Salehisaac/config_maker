<?php




use app\TelegramBot;
require_once 'TelegramBot.php';




$token = "6813131583:AAHhfKYcObFrXsuzZ-7oZD_ldi6X2rU4K-k";
$action = "sendMessage";

$bot = new TelegramBot($token);

$data = file_get_contents("php://input");
$update = json_decode($data, true);
vardump($update);
























