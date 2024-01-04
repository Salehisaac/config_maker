<?php

require __DIR__.'/vendor/autoload.php';

date_default_timezone_set('Asia/Tehran');


use app\TelegramBot;
use DataBase\Database;
use Dotenv\Dotenv;



use function PHPSTORM_META\type;
require_once 'TelegramBot.php';
require_once 'Database.php';



$bot = new TelegramBot("6844787417:AAGPxqzJjxu7PcgS32nYesxv8je-QKSgb_s" , "kian", "kian1381");

var_dump($bot->sendMessage(291109889 , 'salam'));
return 0;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../idea');
$dotenv->load();



$token = $_ENV['BOT_TOKEN'];
$panel_username = $_ENV['PANEL_USERNAME'];
$panel_paswword = $_ENV['PANEL_PASSWORD'];












$content = file_get_contents('php://input');
$update = json_decode($content, true);

$db = new Database();
$bot = new TelegramBot($token , $panel_username , $panel_paswword);
$chat_id = $update['message']['chat']['id'];
$user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
$command = $user['command'];
$message_id = $user['message_id'];
$bot->deleteMessage($chat_id,$message_id);

$user = $bot->findUser($chat_id);







if($user == null)
{
    $db->insert('users', ['id', 'name', 'username'] , [$chat_id, $update['message']['chat']['first_name'], $update['message']['chat']['username']]);
    $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
}



if (isset($update['callback_query']))
{

    $callbackData = $update['callback_query']['data'];
    $callbackQueryId = $update['callback_query']['id'];
    $chat_id = $update['callback_query']['message']['chat']['id'];
    $bot->answerCallbackQuery($callbackQueryId, $callbackData);





    if (explode( " " ,$callbackData)[1] == 'config')
    {

        $db = new Database();
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        $message_id = $user['message_id'];
        $bot->deleteMessage($chat_id,$message_id);

        $configs = $db->selectAll("SELECT * FROM configs WHERE user_id = ? ", [$chat_id]);
        $countConfigs = count($configs);

        $templates_id = [];
        $templates = $bot->findTemplatesForUser($chat_id);
        foreach ($templates as $template)
        {
            array_push($templates_id, $template["template_id"]);
        }


        if(in_array(explode( " " ,$callbackData)[0], $templates_id))
        {

            $selected = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.user_id = ' . $chat_id . ' AND user_templates.template_id = ' . explode( " " ,$callbackData)[0]);
            $price = $selected['price'];
            $price_show = $price;
        }

        else
        {

            $selected = $db->select("SELECT * FROM templates WHERE id = ? ", [explode( " " ,$callbackData)[0]]);
            $price = $selected['default_price'];
            $price_show = $price;
        }



        $username = $update['callback_query']['message']['chat']['username'] . '_'. $countConfigs+1;

        $proxies = json_decode($selected['proxy'] , true);
        $nameprotocol = array();
        $nameprotocol['vless']['flow'] = $proxies['vless']['flow'];
        $proxies = $nameprotocol;

        if($selected['expire'] !== null)
        {
            $currentDate = new DateTime();


            $currentDate->add(new DateInterval("P{$selected['expire']}D"));


            $newTimestamp = strtotime($currentDate->format('Y-m-d'));
            $expire = $newTimestamp;
            $expire_show = $selected['expire'];
        }
        else
        {
            $expire = null;
            $expire_show = '∞';
        }

        if($selected['limitation'] !== null)
        {
            $data_limit = intval($selected['limitation']);
            $data_show = $selected['limitation'];
        }
        else
        {
            $data_limit = null;
            $data_show = '∞';

        }






        $message = " 
                        💻 کانفیگ {$expire_show} روزه  
    
                🦅 حجم : {$data_show} گیگ  
    
                ❄️ قیمت : {$price_show}  هزار تومن 
    
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

    elseif(explode( " " ,$callbackData)[1] == 'approve')
    {

        $userId = explode(" ", $callbackData)[0];
        $selected_user = $db->select("SELECT * FROM `users` WHERE `id` = ?", [$userId]);
        $message_id = $selected_user['message_id'];
        $bot->deleteMessage($chat_id, $message_id);
        $db->update('users', explode( " " ,$callbackData)[0], ['is_verified' , 'message_id'], ['approved' , null]);


    }

    elseif(explode( " " ,$callbackData)[1] == 'ban')
    {

        $userId = explode(" ", $callbackData)[0];
        $selected_user = $db->select("SELECT * FROM `users` WHERE `id` = ?", [$userId]);
        $message_id = $selected_user['message_id'];
        $bot->deleteMessage($chat_id, $message_id);
        $db->update('users', explode( " " ,$callbackData)[0], ['is_verified' , 'message_id'], ['baned' , null]);


    }

    elseif(explode( " " ,$callbackData)[1] == 'panel')
    {



        $db = new Database();
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        $message_id = $user['message_id'];
        $bot->deleteMessage($chat_id,explode( " " ,$callbackData)[2]);
        $bot->deleteMessage($chat_id,$message_id);


        $templates = $db->selectAll("SELECT * FROM templates WHERE panel_id = ? ", [explode( " " ,$callbackData)[0]]);
        foreach ($templates as $template)
        {
            $name_array = explode(" ", $template["name"]);
            $special_template = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.user_id = ' . $chat_id . ' AND user_templates.template_id = ' . $template['id']);
            $new_price = $template['default_price'];
            if($special_template && $special_template['price'] !== null)
            {
                $new_price = $special_template['price'];
            }
            if ($new_price == -1 || $new_price == '-1'){
                continue;
            }
            $name = $template['name'];
            foreach ($name_array as $name_part)
            {
                if(is_numeric($name_part))
                {
                    $name = str_replace($name_part, number_format($new_price), $name);
                    $name_array = explode(" ", $name);
                }

            }
            $buttons[] = [['text' => $name, 'callback_data' => strval($template['id'] . ' config')]];
        }
        $replyMarkup = json_encode([
            'inline_keyboard' =>
                $buttons

        ]);

        $message_id = $bot->sendMessage($chat_id, 'کانفیگ های این پنل:', $replyMarkup);
        $db->update('users', $chat_id, ['message_id'], [$message_id]);
    }

    elseif(explode( " " ,$callbackData)[1] == 'panel_search')
    {
        $user= $db->select('SELECT * FROM `users` WHERE `id` = ?', [$chat_id]);
        $message_id = $user['message_id'];
        $bot->deleteMessage($chat_id,$message_id);

        $panel = $db->select("SELECT * FROM `panels` WHERE `id` = ?", [explode( " " ,$callbackData)[0]]);
        $panel_id = $panel['id'];


        $configs = $bot->searchConfigsBYChatId($chat_id , $panel_id);

        if (count($configs) > 0)
        {
            foreach ($configs as $config)
            {
                $buttons[] = [
                    ['text' => $config['name'], 'callback_data' => strval($config['id'] . ' ' . $panel_id . ' config_search')]
                ];
            }
            $keyboard = [
                'inline_keyboard' => $buttons
            ];

            $replyMarkup = json_encode($keyboard);

            $message_id = $bot->sendMessage($chat_id, 'کانفیگ های شما :', $replyMarkup);
        }
        else
        {
            $bot->sendMessage($chat_id, 'کانفیگی برای نمایش وجود ندارد');
        }
        $db->update('users', $chat_id , ['message_id'] , [$message_id] );

    }



    elseif(explode( " " ,$callbackData)[0] == 'خرید')
    {
        $db = new Database();
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        $message_id = $user['message_id'];
        $bot->deleteMessage($chat_id,$message_id);
        $wait_message = "لطفا کمی صبر کنید ...";
        $wait_message_id = $bot->sendMessage($chat_id, $wait_message);


        $selected = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.user_id = ' . $chat_id . ' AND user_templates.template_id = ' . explode( " " ,$callbackData)[1]);
        $price = $selected['price'];
        $price_show = $price;

        if($selected == null)
        {

            $selected = $db->select("SELECT * FROM templates WHERE `id` = ? " , [explode( " " ,$callbackData)[1]]);
            $price = $selected['default_price'];
            $price_show = $price;

        }

        $panel = $db->join('*' , 'templates' , 'panels' , 'templates.panel_id = panels.id' , 'templates.panel_id = ' . $selected['panel_id'] );

        $username = $user['username'];

        if($user['alter_name'] == 1 && explode(' ' ,$user['message'])[0] == 'alter_name')
        {
            $special_template = $db->select('SELECT * FROM `user_templates` WHERE `user_id` = ? AND `template_id` = ?',[$chat_id , explode( " " ,$callbackData)[1]] );

            if($special_template)
            {
                $username = explode(' ' ,$user['message'])[1] . '_' . $special_template['name'];
            }
            else
            {
                $username = explode(' ' ,$user['message'])[1];
            }

        }
        $configs = $bot->search($username);
        if(count($configs) > 0)
        {

            $last_configs_name = explode('_', $configs[sizeof($configs) -1]['name']);
            $last_part_of_name = end($last_configs_name);
            $last_part_of_name = intval($last_part_of_name);

            if (is_int($last_part_of_name))
            {
                $last_number = $last_part_of_name;
            }else
            {
                $last_number = 0;
            }
        }else
        {
            $last_number = 0;
        }

        $number = $last_number + 1 ;
        if ($number !== 1){
            $username = $username .'_'. $number;
        }

        $username = $bot->validName($username , $panel['url'] , $number);



        $proxies = json_decode($selected['proxy'] , true);
        $nameprotocol = array();
        $nameprotocol['vless'] = $proxies['vless'];
        $proxies = $nameprotocol;



        $currentDate = new DateTime();
        $futureDateTime = clone $currentDate;


        if($selected['expire'] !== null)
        {
            $hours =($selected['expire'] * 24) + 2 ;
            $futureDateTime->modify('+' . $hours . 'hours');
            $futureTimestamp = $futureDateTime->getTimestamp();
            $timestampString = date('Y-m-d H:i:s', $futureTimestamp);
            $expire = $futureTimestamp;
        }
        else
        {
            $expire = null ;
            $timestampString = null ;
        }

        if($selected['limitation'] !== null)
        {
            $data_limit = intval($selected['limitation'] * (1024 ** 3));
            $data_show = $selected['limitation'];
        }

        else
        {
            $data_limit = null ;
        }

        $result = $bot->makeUser($chat_id ,$username, $proxies, $expire, $data_limit, $panel['url']);

        $config = $bot->getuser($username, $panel['url'])['subscription_url'];

        if($config == null)
        {
            for ($i= 0 ; $i<3 ; $i++)
            {
                $config = $bot->getuser($username, $panel['url'])['subscription_url'];
                if($config !== null)
                {
                    break;
                }
                $sleep_message = "...";
                $sleep_message_id = $bot->sendMessage($chat_id, $sleep_message);
                sleep(7);
                $bot->deleteMessage($chat_id,$sleep_message_id);
            }
        }

        if(strpos($config , '://') == false && $config !== null)
        {
            $config = $panel['url'] . $config;
        }

        elseif($config == null)
        {
            $config = false ;
        }

        $bot->deleteMessage($chat_id,$wait_message_id);
        if($config)
        {
            $result =$db->insert('configs', ['name', 'expire', 'limitation','proxy', 'user_id', 'price' , 'panel_id' , 'expires_at'],[$username, $selected['expire'], $data_limit, $selected['proxy'], $chat_id, $price , $panel['id'], $timestampString  ]);
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

    elseif(explode( " " ,$callbackData)[0] == 'delete')
    {

        $panel = $db->select("SELECT * FROM `panels` WHERE `id` = ?", [explode( " " ,$callbackData)[2]] );
        $db_user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        $user = $bot->getuser(explode( " " ,$callbackData)[1] , $panel['url']);
        $config = $db->select("SELECT * FROM `configs` WHERE `name` = ? AND `panel_id`=?" , [explode( " " ,$callbackData)[1] , $panel['id']]);
        $price = $config['price'];
        $indebtedness = $db_user['indebtedness'];
        $indebtedness -= $price;
        $db->update('users' , $chat_id, ['indebtedness'] , [$indebtedness]);
        $message_id = $db_user['message_id'];
        $bot->deleteMessage($chat_id,$message_id);



        if($user['detail'] !== 'User not found')
        {
            $bot->removeuser($panel['url'] , $user['username']);
            $db->deleteConfig('configs' , $user['username'] , $panel['id']);
            $bot->sendMessage($chat_id, 'کانفیگ شما حذف شد');
        }

        else
        {
            $db->deleteConfig('configs' , explode( " " ,$callbackData)[1] , $panel['id']);
            $bot->sendMessage($chat_id, ' کانفیگ شما حذف شد');
        }

    }

    elseif(explode( " " ,$callbackData)[0] == 'sub_link')
    {
        $db = new Database();
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        $message_id = $user['message_id'];
        $bot->deleteMessage($chat_id,$message_id);


        $panel = $db->select("SELECT * FROM `panels` WHERE `id` = ?", [explode( " " ,$callbackData)[2]] );
        $config = $bot->getuser(explode( " " ,$callbackData)[1], $panel['url'])['subscription_url'];
        $url = $panel['url'] . $config;
        $QRCode = $bot->makeQRcode($url);
        $bot->sendImage($chat_id, $QRCode, $config, explode( " " ,$callbackData)[1]);
    }

    elseif(explode( " " ,$callbackData)[0] == 'update')
    {
        $config = $db->select("SELECT * FROM `configs` WHERE `name` = ? AND `panel_id` = ? " , [explode( " " ,$callbackData)[1],explode( " " ,$callbackData)[2]] );
        $panel = $db->select("SELECT * FROM `panels` WHERE `id` = ?" , [explode( " " ,$callbackData)[2]] );
        $marzban_config = $bot->getuser(explode( " " ,$callbackData)[1], $panel['url'])['subscription_url'];
        $user = $db->select("SELECT * FROM `users` WHERE `id` = ?" , [$chat_id]);
        $message_id = $user['message_id'];
        $bot->deleteMessage($chat_id,$message_id);
        if($config['expires_at'] !== null)
        {
            $bot->Extend($panel , explode( " " ,$callbackData)[1]);
        }
        $bot->ResetUserDataUsage(explode( " " ,$callbackData)[0] , $panel["url"]);
        $price = $config['price'];
        $indebtedness = $user['indebtedness'];
        $indebtedness += $price;
        $db->update('users' , $chat_id, ['indebtedness'] , [$indebtedness]);
        $bot->sendMessage($chat_id , 'کانفیگ شما اپدیت شد');
        $url = $panel['url'] . $marzban_config;
        $QRCode = $bot->makeQRcode($url);
        $bot->sendImage($chat_id, $QRCode, $url, explode( " " ,$callbackData)[1]);


    }

    elseif(explode( " " ,$callbackData)[0] == 'return')
    {
        $message_id = $user['message_id'];
        $bot->deleteMessage($chat_id,$message_id);
        $message_id = $bot->sendMessage($chat_id ,'خوش آمدید دستور مورد نظر خود را انتخاب کنید');
        $db->update('users', $chat_id, ['message_id'], [$message_id]);
    }

    elseif(explode( " " ,$callbackData)[2] == 'config_search')
    {
        $user= $db->select('SELECT * FROM `users` WHERE `id` = ?', [$chat_id]);
        $panel_id = explode(" " , $callbackData)[1];
        $panel = $db->select("SELECT * FROM `panels` WHERE `id` = ?", [$panel_id] );
        $config = $db->select('SELECT * FROM `configs` WHERE `id` = ?' , [explode(" " , $callbackData)[0]]);
        $marzban_config = $bot->getuser($config['name'], $panel['url'])['subscription_url'];

        $message_id = $user['message_id'];
        $bot->deleteMessage($chat_id,$message_id);

        if ($config !== null) {
            $buttons = [
                [
                    ['text' => 'تمدید', 'callback_data' => 'update ' . $config['name'] . ' ' . $config['panel_id']],
                    ['text' => 'بازگشت', 'callback_data' => 'return '],
                ]
            ];


            $can_delete = strtotime($config['created_at']) > strtotime('-1 day');

            if ($can_delete)
            {

                $buttons[] = [['text' => 'حذف', 'callback_data' => 'delete ' . $config['name'] . ' ' . $config['panel_id']]];

            }

            $keyboard = [
                'inline_keyboard' => $buttons
            ];

            $replyMarkup = json_encode($keyboard);
            if ($marzban_config == null)
            {
                $message_id = $bot->sendMessage($chat_id, 'لطفا دوباره تلاش کنید ');
                $db->update('users', $chat_id, ['message_id', 'command'], [$message_id , '']);
            }
            else
            {
                $url = $panel['url'] . $marzban_config;
                $QRCode = $bot->makeQRcode($url);
                $message_id = $bot->sendImage($chat_id, $QRCode, $url, $config['name'] , $replyMarkup);
                $db->update('users', $chat_id, ['message_id', 'command'], [$message_id , '']);
            }

        }
    }

    elseif(explode( " " ,$callbackData)[0] == 'answer')
    {
        $db->update('users' , $chat_id , ['command' , 'message'] , ['answer' , explode( " " ,$callbackData)[1] . ' ' . explode( " " ,$callbackData)[2] ]);
        $bot->sendMessage($chat_id , 'پاسخ خود را به این مشتری بنویسید');
    }

    else
    {

        error_log('Received unexpected callback data: ' . $callbackData);
    }

}


if($user["is_verified"] == "approved")
{
    if (isset($update['message']) && $update['message']['text'] == '/start' )
    {
        $bot->sendMessage($chat_id,' سلام خوش آمدید');
        $db->update('users', $chat_id, ['message' , 'command'], [null , null]);
    }

    elseif (isset($update['message']) && $update['message']['text'] == '🌍 کانفیگ های پیشنهادی' )
    {
        $is_set = false;
        $text = $update['message']['text'];
        $chat_id = $update['message']['chat']['id'];
        $messageId = $update['message']['message_id'];







        if($user['alter_name'] == 1)
        {
            $bot->deleteMessage($chat_id,$message_id);
            $message_id = $bot->sendMessage($chat_id,'نام کانفیگ را وارد کنید');
            $db->update('users', $chat_id, ['command' , 'message_id'], ['alter_name' , $message_id]);
        }

        else
        {
            $buttons = [];
            $db = new Database();
            $panels = $db->selectAll("SELECT * FROM panel_users WHERE user_id = ? ", [$chat_id]);

            if(count($panels) == 1)
            {
                $db = new Database();
                $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
                $message_id = $user['message_id'];
                $bot->deleteMessage($chat_id,$message_id);
                $panel = $db->join('*' , 'panel_users' , 'panels' , 'panel_users.panel_id = panels.id' , 'panel_users.panel_id = ' . $panels[0]['panel_id']);


                $templates = $db->selectAll("SELECT * FROM templates WHERE panel_id = ? ", [$panel['id']]);
                foreach ($templates as $template)
                {
                    $name_array = explode(" ", $template["name"]);
                    $special_template = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.user_id = ' . $chat_id . ' AND user_templates.template_id = ' . $template['id']);
                    $new_price = $template['default_price'];
                    if($special_template && $special_template['price'] !== null)
                    {
                        $new_price = $special_template['price'];
                    }
                    if ($new_price == -1 || $new_price == '-1'){
                        continue;
                    }
                    $name = $template['name'];
                    foreach ($name_array as $name_part)
                    {
                        if(is_numeric($name_part))
                        {
                            $name = str_replace($name_part, number_format($new_price), $name);
                            $name_array = explode(" ", $name);
                        }

                    }
                    $buttons[] = [['text' => $name, 'callback_data' => strval($template['id'] . ' config')]];
                }

                $replyMarkup = json_encode([
                    'inline_keyboard' =>
                        $buttons

                ]);

                $message_id = $bot->sendMessage($chat_id, 'کانفیگ های این پنل:', $replyMarkup);
                $db->update('users', $chat_id, ['message_id'], [$message_id]);
            }

            elseif($panels == NULL)
            {

                $panels = $bot->findDefaultPanels();
                if(count($panels) == 1)
                {
                    $db = new Database();
                    $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
                    $message_id = $user['message_id'];
                    $bot->deleteMessage($chat_id,$message_id);



                    $templates = $db->selectAll("SELECT * FROM templates WHERE panel_id = ? ", [$panels[0]['id']]);
                    foreach ($templates as $template)
                    {
                        $name_array = explode(" ", $template["name"]);
                        $special_template = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.user_id = ' . $chat_id . ' AND user_templates.template_id = ' . $template['id']);
                        $new_price = $template['default_price'];
                        if($special_template && $special_template['price'] !== null)
                        {
                            $new_price = $special_template['price'];
                        }
                        if ($new_price == -1 || $new_price == '-1'){
                            continue;
                        }
                        $name = $template['name'];
                        foreach ($name_array as $name_part)
                        {
                            if(is_numeric($name_part))
                            {
                                $name = str_replace($name_part, number_format($new_price), $name);
                                $name_array = explode(" ", $name);
                            }
                        }
                        $buttons[] = [['text' => $name, 'callback_data' => strval($template['id'] . ' config')]];
                    }
                    $replyMarkup = json_encode([
                        'inline_keyboard' =>
                            $buttons

                    ]);

                    $message_id = $bot->sendMessage($chat_id, 'کانفیگ های این پنل:', $replyMarkup);
                    $db->update('users', $chat_id, ['message_id'], [$message_id]);
                }

                else
                {
                    $buttons = [];


                    foreach ($panels as $panel)
                    {
                        $buttons[] = [['text' => $panel['name'], 'callback_data' => strval($panel['id'] . ' panel ' . $message_id)]];
                    }

                    $replyMarkup = json_encode([
                        'inline_keyboard' =>
                            $buttons

                    ]);

                    $message_id = $bot->sendMessage($chat_id, 'پنل های پیشنهادی شما:', $replyMarkup);
                    $db->update('users', $chat_id, ['message_id'], [$message_id]);
                }

            }

            else
            {
                foreach ($panels as $panel)
                {
                    $selected_panels = $db->join('*' , 'panel_users' , 'panels' , 'panel_users.panel_id = panels.id' , 'panel_users.panel_id = ' . $panel['panel_id'] );
                    $buttons[] = [['text' => $selected_panels['name'], 'callback_data' => strval($panel['panel_id'] . ' panel ' . $message_id)]];
                }



                // Assuming here that you want to send a welcome message along with a button
                $replyMarkup = json_encode([
                    'inline_keyboard' =>
                        $buttons

                ]);

                $message_id = $bot->sendMessage($chat_id, 'پنل های پیشنهادی شما:', $replyMarkup);
                $db->update('users', $chat_id, ['message_id'], [$message_id]);
            }


        }

    }

    elseif (isset($update['callback_query']))
    {

        $callbackData = $update['callback_query']['data'];
        $callbackQueryId = $update['callback_query']['id'];
        $chat_id = $update['callback_query']['message']['chat']['id'];
        $bot->answerCallbackQuery($callbackQueryId, $callbackData);





        if (explode( " " ,$callbackData)[1] == 'config')
        {

            $db = new Database();
            $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
            $message_id = $user['message_id'];
            $bot->deleteMessage($chat_id,$message_id);

            $configs = $db->selectAll("SELECT * FROM configs WHERE user_id = ? ", [$chat_id]);
            $countConfigs = count($configs);

            $templates_id = [];
            $templates = $bot->findTemplatesForUser($chat_id);
            foreach ($templates as $template)
            {
                array_push($templates_id, $template["template_id"]);
            }

            if(in_array(explode( " " ,$callbackData)[0], $templates_id))
            {
                $selected = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.user_id = ' . $chat_id . ' AND user_templates.template_id = ' . explode( " " ,$callbackData)[0]);
                $price = $selected['price'];
                $price_show = $price;
            }

            else
            {
                $selected = $db->select("SELECT * FROM templates WHERE id = ? ", [explode( " " ,$callbackData)[0]]);
                $price = $selected['default_price'];
                $price_show = $price;
            }



            $username = $update['callback_query']['message']['chat']['username'] . '_'. $countConfigs+1;

            $proxies = json_decode($selected['proxy'] , true);
            $nameprotocol = array();
            $nameprotocol['vless']['flow'] = $proxies['vless']['flow'];
            $proxies = $nameprotocol;

            if($selected['expire'] !== null)
            {
                $currentDate = new DateTime();


                $currentDate->add(new DateInterval("P{$selected['expire']}D"));


                $newTimestamp = strtotime($currentDate->format('Y-m-d'));
                $expire = $newTimestamp;
                $expire_show = $selected['expire'];
            }
            else
            {
                $expire = null;
                $expire_show = '∞';
            }

            if($selected['limitation'] !== null)
            {
                $data_limit = intval($selected['limitation']);
                $data_show = $selected['limitation'];
            }
            else
            {
                $data_limit = null;
                $data_show = '∞';

            }








            $message = " 
                            💻 کانفیگ {$expire_show} روزه  
        
                    🦅 حجم : {$data_show} گیگ  
        
                    ❄️ قیمت : {$price_show}   هزار  تومن
        
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

        elseif(explode( " " ,$callbackData)[1] == 'approve')
        {
            $bot->sendMessage($chat_id, 'hi');
            $message_id = $db->select("SELECT * FROM `users` WHERE `id` = ?" , explode( " " ,$callbackData)[0])['message_id'];
            $bot->deleteMessage($chat_id, $message_id);
            $db->update('users', explode( " " ,$callbackData)[0], ['is_verified' , 'message_id'], ['approved' , null]);

        }



        // elseif(explode( " " ,$callbackData)[1] == 'panel_search')
        // {

        //    $user= $db->select('SELECT * FROM `users` WHERE `id` = ?', [$chat_id]);
        //    $last_message_id = $user['message_id'];
        //    $bot->deleteMessage($chat_id,$last_message_id);
        //    $db->update('users', $chat_id , ['message' , 'command' , 'message_id'] , ['panel_id ' . explode( " " ,$callbackData)[0] , 'set_name' , $message_id] );
        // }



        // elseif(explode( " " ,$callbackData)[0] == 'خرید')
        // {




        //     $db = new Database();

        //     $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        //     $message_id = $user['message_id'];
        //     $bot->deleteMessage($chat_id,$message_id);







        //     $selected = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.user_id = ' . $chat_id . ' AND user_templates.template_id = ' . explode( " " ,$callbackData)[1]);
        //     $price = $selected['price'];
        //     $price_show = $price;

        //     if($selected == null)
        //     {

        //         $selected = $db->select("SELECT * FROM templates WHERE `id` = ? " , [explode( " " ,$callbackData)[1]]);
        //         $price = $selected['default_price'];
        //         $price_show = $price;

        //     }





        //     $panel = $db->join('*' , 'templates' , 'panels' , 'templates.panel_id = panels.id' , 'templates.panel_id = ' . $selected['panel_id'] );

        //     $username = $update['callback_query']['message']['chat']['username'];

        //     if($user['alter_name'] == 1 && explode(' ' ,$user['message'])[0] == 'alter_name')
        //     {
        //         $special_template = $db->select('SELECT * FROM `user_templates` WHERE `user_id` = ? AND `template_id` = ?',[$chat_id , explode( " " ,$callbackData)[1]] );

        //         if($special_template)
        //         {
        //             $username = explode(' ' ,$user['message'])[1] . '_' . $special_template['name'];
        //         }
        //         else
        //         {
        //             $username = explode(' ' ,$user['message'])[1];
        //         }

        //     }



        //     $configs = $bot->search($username);
        //     if(count($configs) > 0)
        //     {

        //         $last_configs_name = explode('_', $configs[sizeof($configs) -1]['name']);
        //         $last_part_of_name = end($last_configs_name);
        //         $last_part_of_name = intval($last_part_of_name);

        //         if (!is_int($last_part_of_name))
        //         {
        //             unset($last_configs_name[count($last_configs_name) - 1]);
        //         }
        //         $last_number = end($last_configs_name);
        //     }

        //     else
        //     {
        //         $last_number = 0;
        //     }




        //     $number = $last_number +1;

        //     $username = $username .'_'. $number;





        //     $username = $bot->validName($username , $panel['url'] , $number);

        //     $proxies = json_decode($selected['proxy'] , true);
        //     $nameprotocol = array();
        //     $nameprotocol['vless'] = $proxies['vless'];
        //     $proxies = $nameprotocol;


        //     $currentDate = new DateTime();
        //     $futureDateTime = clone $currentDate;


        //     if($selected['expire'] !== null)
        //     {
        //         $hours =($selected['expire'] * 24) + 2 ;
        //         $futureDateTime->modify('+' . $hours . 'hours');
        //         $futureTimestamp = $futureDateTime->getTimestamp();
        //         $timestampString = date('Y-m-d H:i:s', $futureTimestamp);
        //         $expire = $futureTimestamp;
        //     }
        //     else
        //     {
        //         $expire = null ;
        //         $timestampString = null ;
        //     }



        //     if($selected['limitation'] !== null)
        //     {
        //         $data_limit = intval($selected['limitation'] * (1024 ** 3));
        //         $data_show = $selected['limitation'];
        //     }

        //     else
        //     {
        //         $data_limit = null ;
        //     }





        //         $result = $bot->makeUser($chat_id ,$username, $proxies, $expire, $data_limit, $panel['url']);

        //         $config = $bot->getuser($username, $panel['url'])['subscription_url'];

        //         if($config == null)
        //         {
        //             $config = $bot->getuser($username, $panel['url'])['subscription_url'];
        //         }
        //         if(strpos($config , '://') == false)
        //             {
        //                 $config = $panel['url'] . $config;
        //             }


        //         if($config)
        //         {
        //             $result =$db->insert('configs', ['name', 'expire', 'limitation','proxy', 'user_id', 'price' , 'panel_id' , 'expires_at'],[$username, $selected['expire'], $data_limit, $selected['proxy'], $chat_id, $price , $panel['id'], $timestampString  ]);
        //             $QRCode = $bot->makeQRcode($config);
        //             $bot->sendImage($chat_id, $QRCode, $config, $username );

        //             $user = $db->selectAll("SELECT * FROM users WHERE `id` = ? " , [$chat_id]);
        //             $indebtedness = $user[0]['indebtedness'];
        //             $indebtedness += $price;
        //             $db->update('users' , $chat_id, ['indebtedness'] , [$indebtedness]);
        //         }
        //         else
        //         {
        //             $bot->sendMessage($chat_id, "مشکلی پیش آمده لطفا دوباره امتحان کنید و در صورت عدم رفع مشکل با ادمین تماس حاصل فرمایید");
        //         }

        // }

        elseif(explode( " " ,$callbackData)[0] == 'delete')
        {

            $panel = $db->select("SELECT * FROM `panels` WHERE `id` = ?", [explode( " " ,$callbackData)[2]] );
            $db_user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
            $user = $bot->getuser(explode( " " ,$callbackData)[1] , $panel['url']);
            $config = $db->select("SELECT * FROM `configs` WHERE `name` = ? AND `panel_id`=?" , [explode( " " ,$callbackData)[1] , $panel['id']]);
            $price = $config['price'];
            $indebtedness = $db_user['indebtedness'];
            $indebtedness -= $price;
            $db->update('users' , $chat_id, ['indebtedness'] , [$indebtedness]);
            $message_id = $db_user['message_id'];
            $bot->deleteMessage($chat_id,$message_id);



            if($user['detail'] !== 'User not found')
            {
                $bot->removeuser($panel['url'] , $user['username']);
                $db->deleteConfig('configs' , $user['username'] , $panel['id']);
                $bot->sendMessage($chat_id, 'کانفیگ شما حذف شد');
            }

            else
            {
                $db->deleteConfig('configs' , explode( " " ,$callbackData)[1] , $panel['id']);
                $bot->sendMessage($chat_id, ' کانفیگ شما حذف شد');
            }

        }

        elseif(explode( " " ,$callbackData)[0] == 'sub_link')
        {
            $db = new Database();
            $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
            $message_id = $user['message_id'];
            $bot->deleteMessage($chat_id,$message_id);


            $panel = $db->select("SELECT * FROM `panels` WHERE `id` = ?", [explode( " " ,$callbackData)[2]] );
            $config = $bot->getuser(explode( " " ,$callbackData)[1], $panel['url'])['subscription_url'];
            $url = $panel['url'] . $config;
            $QRCode = $bot->makeQRcode($url);
            $bot->sendImage($chat_id, $QRCode, $config, explode( " " ,$callbackData)[1]);
        }

        elseif(explode( " " ,$callbackData)[0] == 'update')
        {
            $config = $db->select("SELECT * FROM `configs` WHERE `name` = ? AND `panel_id` = ? " , [explode( " " ,$callbackData)[1],explode( " " ,$callbackData)[2]] );
            $panel = $db->select("SELECT * FROM `panels` WHERE `id` = ?" , [explode( " " ,$callbackData)[2]] );
            $marzban_config = $bot->getuser(explode( " " ,$callbackData)[1], $panel['url'])['subscription_url'];
            $user = $db->select("SELECT * FROM `users` WHERE `id` = ?" , [$chat_id]);
            $message_id = $user['message_id'];
            $bot->deleteMessage($chat_id,$message_id);
            if($config['expires_at'] !== null)
            {
                $bot->Extend($panel , explode( " " ,$callbackData)[1]);
            }
            $bot->ResetUserDataUsage(explode( " " ,$callbackData)[0] , $panel["url"]);
            $price = $config['price'];
            $indebtedness = $user['indebtedness'];
            $indebtedness += $price;
            $db->update('users' , $chat_id, ['indebtedness'] , [$indebtedness]);
            $bot->sendMessage($chat_id , 'کانفیگ شما اپدیت شد');
            $url = $panel['url'] . $marzban_config;
            $QRCode = $bot->makeQRcode($url);
            $bot->sendImage($chat_id, $QRCode, $url, explode( " " ,$callbackData)[1]);


        }

        elseif(explode( " " ,$callbackData)[0] == 'return')
        {
            $message_id = $user['message_id'];
            $bot->deleteMessage($chat_id,$message_id);
            $message_id = $bot->sendMessage($chat_id ,'خوش آمدید دستور مورد نظر خود را انتخاب کنید');
            $db->update('users', $chat_id, ['message_id'], [$message_id]);
        }

        else
        {

            error_log('Received unexpected callback data: ' . $callbackData);
        }

    }

    elseif (isset($update['message']) && $update['message']['text'] == 'میزان بدهی شما')
    {
        $db->update('users' , $chat_id , ['command'] , [null]);
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        $indebtedness = $user['indebtedness'];
        $bot->sendMessage($chat_id,'میزان بدهی شما : '. $indebtedness . ' هزار تومان ' );

    }
    //this fs for support panel
    elseif (isset($update['message']) && $update['message']['text'] == 'تماس با پشتیبانی')
    {

        $bot->sendMessage($chat_id,' لطفا پیغام خود را بفرستید' ,$reply);
        $db->update('users' , $chat_id , ['command'] , ['support']);

    }
    elseif (isset($update['message']) && $update['message']['text'] == '🧔 کانفیگ های شما')
    {
        $db->update('users' , $chat_id , ['command'] , [null]);
        $panels = $db->selectAll("SELECT * FROM `panel_users` WHERE `user_id` = ?", [$chat_id]);
        $panels_number = count($panels);

        if ($panels_number > 1)
        {
            $buttons = [];
            foreach ($panels as $panel)
            {
                $selected_panels = $db->join('*' , 'panel_users' , 'panels' , 'panel_users.panel_id = panels.id' , 'panel_users.panel_id = ' . $panel['panel_id'] );

                $buttons[] = [
                    ['text' => $selected_panels['name'], 'callback_data' => strval($selected_panels['id'] . ' panel_search')]
                ];
            }

            $keyboard = [
                'inline_keyboard' => $buttons
            ];

            $replyMarkup = json_encode($keyboard);

            $message_id = $bot->sendMessage($chat_id, 'پنل مورد نظر خود را انتخاب کنید:', $replyMarkup);
            $db->update('users', $chat_id, ['message_id'], [$message_id]);
        }

        else
        {

            $panel = $db->select("SELECT * FROM `panel_users` WHERE `user_id` = ?", [$chat_id]);
            $panel_id = $panel['panel_id'];
            if (!$panel)
            {
                $panels = $bot->findDefaultPanels();
                $panel = $panels[0];
                $panel_id = $panel["id"];
            }

            $configs = $bot->searchConfigsBYChatId($chat_id , $panel_id);

            if (count($configs) > 0)
            {
                foreach ($configs as $config)
                {
                    $buttons[] = [
                        ['text' => $config['name'], 'callback_data' => strval($config['id'] . ' ' . $panel_id . ' config_search')]
                    ];
                }
                $keyboard = [
                    'inline_keyboard' => $buttons
                ];

                $replyMarkup = json_encode($keyboard);

                $message_id = $bot->sendMessage($chat_id, 'کانفیگ های شما :', $replyMarkup);
            }
            else
            {
                $bot->sendMessage($chat_id, 'کانفیگی برای نمایش وجود ندارد');
            }
            $db->update('users', $chat_id , ['message_id'] , [$message_id] );
        }




    }
    //this is for back keyborad button


    elseif (isset($update['message']) && $update['message']['text'] == 'پنل مدیریت')
    {

        $db->update('users' , $chat_id , ['command'] , [null]);
        $keyboard = [
            ['درخواست ها'],
            ['پیام های مشتریان'],
            ['🌍 بازگشت به منوی اصلی'],
        ];
        $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
        $reply = json_encode($response);

        $bot->sendMessage($chat_id, 'پنل مدیریت',$reply);
    }

    elseif (isset($update['message']) && $update['message']['text'] == 'درخواست ها')
    {
        $db->update('users' , $chat_id , ['command'] , [null]);
        $requests = $db->selectAll("SELECT * FROM `users` WHERE `is_verified` = ?", ['unchecked']);
        foreach ($requests as $request)
        {
            $buttons = [];

            $buttons[] = [
                ['text' => 'تایید', 'callback_data' => $request['id'] . ' approve'],
                ['text' => 'رد', 'callback_data' => $request['id'] . ' ban']
            ];

            $replyMarkup = json_encode([
                'inline_keyboard' => $buttons
            ]);

            $message_id=$bot->sendMessage($chat_id, $request['username'], $replyMarkup);
            $db->update("users" , $request['id'] , ['message_id'] , [$message_id] );
        }
        if(count($requests) == 0)
        {
            $bot->sendMessage($chat_id, 'درخواستی وجود ندارد' , $replyMarkup);
        }

    }

    elseif (isset($update['message']) && $update['message']['text'] == '🌍 بازگشت به منوی اصلی')
    {
        $bot->sendMessage($chat_id , 'به منوی قبلی بازگشتید');
        $db->update('users' , $chat_id , ['command'] , [null]);
    }

    elseif (isset($update['message']) && $update['message']['text'] == 'پیام های مشتریان')
    {
        $messages = $db->selectAll('SELECT * FROM `support`');

        if(count($messages) == 0)
        {
            $bot->sendMessage($chat_id , 'پیامی برای نمایش وجود ندارد' );
        }
        else
        {

            foreach($messages as $message)
            {

                $buttons[] = [['text' => 'پاسخ', 'callback_data' => 'answer ' . $message['chat_id'] . ' ' . $message['id']]];




                $replyMarkup = json_encode([
                    'inline_keyboard' =>
                        $buttons

                ]);



                $sent_message = $message['message'] ;
                $text = "شما یک پیغام جدید از طرف {$message['name']} دارید
                    {$sent_message}";

                $bot->sendMessage($chat_id , $text , $replyMarkup );
                $buttons = [];
            }
        }

    }






    elseif (isset($update['message']) && $command == 'alter_name')
    {
        $message_id = $user['message_id'];
        $bot->deleteMessage($chat_id,$message_id);
        $message_id = $bot->sendMessage($chat_id,'نام کانفیگ شما ست شد ');
        $db->update('users', $chat_id, ['message' , 'message_id'], ['alter_name ' . $update['message']['text'] , $message_id ]);


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

        $bot->deleteMessage($chat_id,$message_id);
        $message_id = $bot->sendMessage($chat_id, 'پنل های پیشنهادی شما:', $replyMarkup);
        $db->update('users', $chat_id, ['message_id'], [$message_id]);
        $db->update('users' , $chat_id , ['command'] , [null]);
    }

    elseif (isset($update['message']) && $command == 'support')
    {

        $bot->sendMessage($chat_id , 'پیغام شما با موفقیت ارسال شد به زودی پاسخ خود را از طریق همین ربات دریافت خواهید کرد');
        $db->insert('support' , ['chat_id' , 'message' , 'name'] , [$chat_id , $update['message']['text'] ,$user['name'] ]);
        $db->update('users' , $chat_id , ['command'] , [null]);

    }

    elseif (isset($update['message']) && $command == 'answer')
    {

        $target_user_id = $user['message'];
        $message = $db->select('SELECT * FROM `support` WHERE `id` = ?' , [explode( " " ,$target_user_id)[1]]);

        $text = "ادمین در جواب این پیام شما پاسخی ارسال کرد 

        ----------------------------------------------------------------
        
        {$message['message']}

        -----------------------------------------------------------------

        {$update['message']['text']}
        
        
        ";


        $bot->sendMessage(explode( " " ,$target_user_id)[0] , $text );
        $db->deleteMessage('support' ,explode( " " ,$target_user_id)[1] );


    }





}

elseif($user['is_verified'] == 'unchecked')
{


    $buttons[] = [['text' => 'تماس با پشتیبانی', 'url' => 'https://t.me/RajaTeam_support' ]];

    $replyMarkup = json_encode([
        'inline_keyboard' =>
            $buttons

    ]);

    $bot->sendMessage($chat_id , 'لطفا منتظر تایید ادمین بمانید' , $replyMarkup);
}









?>
