<?php




use app\TelegramBot;
require_once 'TelegramBot.php';

$url_panel = '';
$proxies = array();
$expire = '';
$data_limit = '';
$username = '';



$token = "6813131583:AAHhfKYcObFrXsuzZ-7oZD_ldi6X2rU4K-k";
$offset = 861718664;
$url = "https://api.telegram.org/bot6813131583:AAHhfKYcObFrXsuzZ-7oZD_ldi6X2rU4K-k/getUpdates?offset{$offset}";
$action = "sendMessage";

$bot = new TelegramBot($token);

$data = file_get_contents("php://input");
$update = json_decode($data, true);
vardump($update);



$result = file_get_contents($url);


$arrResult = json_decode($result, true);
$length = count($arrResult['result']);
$chatId = $arrResult['result'][0]["message"]["chat"]["id"];
$text = $arrResult['result'][$length - 1]['message']['text'] ;









// $bot->handleInOrder($chatId , $text);

// Process the received data


function get_update_id($updates)
    {
        $num_updates = count($updates["result"]);
        $last_update = $num_updates - 1 ;
        $update_id = $updates["result"][$last_update]["update_id"];
        return ($update_id);
    }


function get_updates($offset)
{

    $url =   "https://api.telegram.org/bot6809114912:AAEnGhJ_em9lf9I1uofJAXfkiiVd8AgFOyE/getUpdates?offset={$offset}";
    $json = file_get_contents($url);
    return json_decode($json, true);
}








