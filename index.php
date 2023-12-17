<?php
use app\TelegramBot;
use DataBase\Database;


require_once 'TelegramBot.php';
require_once 'Database.php';

$token = '6813131583:AAHhfKYcObFrXsuzZ-7oZD_ldi6X2rU4K-k';



// Remove the last underscore and the number next to it


$bot = new TelegramBot($token);
$users = $bot->getAllUsers('ts2.kroute.site:8423');



$content = file_get_contents('php://input');
$update = json_decode($content, true);

$db = new Database();
$bot = new TelegramBot($token);
$chat_id = $update['message']['chat']['id'];
$user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
$command = $user['command'];










if (isset($update['message']) && $update['message']['text'] == '/start' || $update['message']['text'] == '🌍 کانفیگ های پیشنهادی' )
{
    $is_set = false;
    $text = $update['message']['text'];
    $chat_id = $update['message']['chat']['id'];
    $messageId = $update['message']['message_id'];


    $user = $bot->findUser($chat_id);


    if($user == null)
    {
        $db->insert('users', ['id', 'name', 'username'] , [$chat_id, $update['message']['chat']['first_name'], $update['message']['chat']['username']]);
    }


    $message_id = $bot->sendMessage($chat_id, 'خوش آمدید');



    $buttons = [];
    $db = new Database();
    $panels = $db->selectAll("SELECT * FROM panel_users WHERE user_id = ? ", [$chat_id]);

    foreach ($panels as $panel)
    {
        $selected_panels = $db->join('*' , 'panel_users' , 'panels' , 'panel_users.panel_id = panels.id' , 'panel_users.panel_id = ' . $panel['panel_id'] );
        $buttons[] = [['text' => $selected_panels['name'], 'callback_data' => strval($panel['panel_id'] . ' panel ' . $message_id)]];
    }


    if($panels == NULL)
    {

        $panels = $bot->findDefaultPanels();
        $buttons = [];


        foreach ($panels as $panel)
        {
            $buttons[] = [['text' => $panel['name'], 'callback_data' => strval($panel['id'] . ' panel ' . $message_id)]];
        }
    }

    // Assuming here that you want to send a welcome message along with a button
    $replyMarkup = json_encode([
        'inline_keyboard' =>
            $buttons

    ]);

    $message_id = $bot->sendMessage($chat_id, 'پنل های پیشنهادی شما:', $replyMarkup);
    $db->update('users', $chat_id, ['message_id'], [$message_id]);
}






elseif (isset($update['callback_query'])) {

    $callbackData = $update['callback_query']['data'];
    $callbackQueryId = $update['callback_query']['id'];
    $chat_id = $update['callback_query']['message']['chat']['id'];



    if (explode( " " ,$callbackData)[1] == 'config')
    {

        $db = new Database();
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        $message_id = $user['message_id'];
        $bot->deleteMessage($token,$chat_id,$message_id);

        $configs = $db->selectAll("SELECT * FROM configs WHERE user_id = ? ", [$chat_id]);
        $countConfigs = count($configs);

        $templates_id = [];
        $templates = $bot->findTemplatesForUser($chat_id);
        foreach ($templates as $template)
        {
            array_push($templates_id, $template["template_id"]);
        }

        if(in_array($callbackData[0], $templates_id))
        {
            $selected = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.user_id = ' . $chat_id . ' AND user_templates.template_id = ' . $callbackData[0]);
            $price = $selected['price'];
            $price_show = $price/1000;
        }

        else
        {
            $selected = $db->select("SELECT * FROM templates WHERE id = ? ", [$callbackData[0]]);
            $price = $selected['default_price'];
            $price_show = $price/1000;
        }



        $username = $update['callback_query']['message']['chat']['username'] . '_'. $countConfigs+1;

        $proxies = json_decode($selected['proxy'] , true);
        $nameprotocol = array();
        $nameprotocol['vless']['flow'] = $proxies['vless']['flow'];
        $proxies = $nameprotocol;

        $currentDate = new DateTime();

        // Add the specified number of days
        $currentDate->add(new DateInterval("P{$selected['expire']}D"));

        // Get the Unix timestamp for the date after adding the days
        $newTimestamp = strtotime($currentDate->format('Y-m-d'));


        $expire = $newTimestamp;
        $data_limit = intval($selected['limitation']);
        $data_show = $selected['limitation'];




        $message = " 
                    💻 کانفیگ {$selected['expire']} روزه  

            🦅 حجم : {$data_show} گیگ  

            ❄️ قیمت : {$price_show} هزار تومن 

            ✔️ در صورت مطمئن بودن روی خرید کلیک کنید";


        $reply = json_encode([
            'inline_keyboard' =>
                [
                    [
                        ['text' => "خرید", 'callback_data' => "خرید " . $callbackData]
                    ]
                ]

        ]);


        $message_id = $bot->sendMessage($chat_id, $message , $reply);
        $db->update('users', $chat_id, ['message_id'], [$message_id]);



    }

    elseif(explode( " " ,$callbackData)[1] == 'panel')
    {


        $buttons = [];
        $db = new Database();
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        $message_id = $user['message_id'];
        $bot->deleteMessage($token,$chat_id,explode( " " ,$callbackData)[2]);
        $bot->deleteMessage($token,$chat_id,$message_id);


        $templates = $db->selectAll("SELECT * FROM templates WHERE panel_id = ? ", [$callbackData[0]]);
        foreach ($templates as $template)
        {
            $buttons[] = [['text' => $template['name'], 'callback_data' => strval($template['id'] . ' config')]];
        }

        $replyMarkup = json_encode([
            'inline_keyboard' =>
                $buttons

        ]);

        $message_id = $bot->sendMessage($chat_id, 'کانفیگ های این پنل:', $replyMarkup);
        $db->update('users', $chat_id, ['message_id'], [$message_id]);
    }

    elseif(explode( " " ,$callbackData)[0] == 'خرید')
    {


        $db = new Database();

        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        $message_id = $user['message_id'];
        $bot->deleteMessage($token,$chat_id,$message_id);



        $configs = $bot->search($user['username']);
        if(count($configs) > 0)
        {
            $last_number = end(explode('_', $configs[sizeof($configs) -1]['name']));
        }

        else
        {
            $last_number = 0;
        }


        $number = $last_number +1;



        $selected = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.user_id = ' . $chat_id . ' AND user_templates.template_id = ' . explode( " " ,$callbackData)[1]);
        $price = $selected['price'];
        $price_show = $price/1000;

        if($selected == null)
        {

            $selected = $db->select("SELECT * FROM templates WHERE `id` = ? " , [explode( " " ,$callbackData)[1]]);
            $price = $selected['default_price'];
            $price_show = $price/1000;

        }

        $panel = $db->join('*' , 'templates' , 'panels' , 'templates.panel_id = panels.id' , 'templates.panel_id = ' . $selected['panel_id'] );

        $username = $update['callback_query']['message']['chat']['username'] . '_'. $number;

        $proxies = json_decode($selected['proxy'] , true);
        $nameprotocol = array();
        $nameprotocol['vless'] = $proxies['vless'];
        $proxies = $nameprotocol;

        $currentDate = new DateTime();
        $currentDate->add(new DateInterval("P{$selected['expire']}D"));
        $newTimestamp = strtotime($currentDate->format('Y-m-d'));

        $expire = $newTimestamp;
        $data_limit = intval($selected['limitation'] * (1024 ** 3));
        $data_show = $selected['limitation'];

        $config = $bot->getuser($username, $panel['url']);

        if($config['detail'] != 'User not found')
        {
            $bot->sendMessage($chat_id , 'این کانفیگ قبلا ساخته شده است');
        }

        else
        {
            $result = $bot->makeUser($chat_id ,$username, $proxies, $expire, $data_limit, $panel['url']);

            $config = $bot->getuser($username, $panel['url'])['subscription_url'];
            $config = $panel['url'] . $config;

            if($config)
            {
                $result =$db->insert('configs', ['name', 'expire', 'limitation','proxy', 'user_id', 'price'],[$username, $selected['expire'], $data_limit, $selected['proxy'], $chat_id, $price ]);
                $QRCode = $bot->makeQRcode($config);
                $bot->sendImage($chat_id, $QRCode, $config, $username );

                $user = $db->selectAll("SELECT * FROM users WHERE `id` = ? " , [$chat_id]);
                $indebtedness = $user[0]['indebtedness'];
                $indebtedness += $price;
                $db->update('users' , $chat_id, ['indebtedness'] , [$indebtedness]);
            }

            else
            {
                $bot->sendMessage($chat_id, "مشکلی پیش آمده لطفا دوباره امتحان کنید و در صورت عدم رفع مشکل با ادمین تماس حاصل فرمایید");
            }

        }







    }


    else
    {
        // Log unexpected callback data for debugging
        error_log('Received unexpected callback data: ' . $callbackData);
    }
}

elseif (isset($update['message']) && $update['message']['text'] == 'میزان بدهی شما')
{

    $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
    $indebtedness = $user['indebtedness'];
    $bot->sendMessage($chat_id,'میزان بدهی شما :'. $indebtedness/1000 . 'هزار تومان' );

}

elseif (isset($update['message']) && $update['message']['text'] == '🧔 کانفیگ های شما')
{
    $bot->sendMessage($chat_id, 'نام کانفیگ مورد نظر خود را وارد کنید :');
    $db->update('users', $chat_id , ['command'] , ['set_name'] );
}

elseif ($command == 'set_name')
{


    $search = $update['message']['text'] ;

    $config = $bot->directSearch($search);

    if($config)
    {
        [['text' => 'حذف کانفیگ', 'callback_data' => 'delete']];


        $bot->sendMessage($chat_id, $config['name']);

    }
    else
    {
        $bot->sendMessage($chat_id, 'کانفیگی برای شما با این نام موجود نیست');
    }


    $db->update('users', $chat_id , ['command'] , [''] );

}




?>
