<?php


phpinfo();

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



$result = file_get_contents($url);


$arrResult = json_decode($result, true);
$length = count($arrResult['result']);
$chatId = $arrResult['result'][0]["message"]["chat"]["id"];
$text = $arrResult['result'][$length - 1]['message']['text'] ;


/*if (!empty($arrResult['result'][$length - 2]['message']['text']))
{

    $before_text = $arrResult['result'][$length - 2]['message']['text'];
}*/

/*var_dump($arrResult['result'][$length - 2]['message']['text']);*/


/*if ($arrResult['result'][$length - 1]['message']['text'] == '/start')
{
    $newUrl = "https://api.telegram.org/bot6809114912:AAEnGhJ_em9lf9I1uofJAXfkiiVd8AgFOyE" ;
    $userMsg = 'سلام خوش آمدید';
    $chat_id = $arrResult['result'][0]["message"]["chat"]["id"];
    $action = '/sendmessage';
    $finalUrl = $newUrl . $action . '?chat_id=' . $chat_id . '&text=' . $userMsg ;
    file_get_contents($finalUrl);

}

if (empty($bot->getUserName()))
{

    $bot->sendMessage($chat_id , 'نام را وارد کنید : ');

    $bot->setUserName('hi');
    $newUrl = "https://api.telegram.org/bot6809114912:AAEnGhJ_em9lf9I1uofJAXfkiiVd8AgFOyE" ;
    $userMsg = ' نام شما ' . $bot->getUserName() . ' است ';
    $chat_id = $arrResult['result'][0]["message"]["chat"]["id"];
    $action = '/sendmessage';
    $finalUrl = $newUrl . $action . '?chat_id=' . $chat_id . '&text=' . $userMsg ;
    file_get_contents($finalUrl);

}*/


$bot = new TelegramBot($token);



$bot->handleInOrder($chatId , $text);

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








