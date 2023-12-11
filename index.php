<?php
use app\TelegramBot;
use DataBase\Database;


require_once 'TelegramBot.php';
require_once 'Database.php';

$token = '6813131583:AAHhfKYcObFrXsuzZ-7oZD_ldi6X2rU4K-k';





// Fetching and decoding incoming webhook data
$content = file_get_contents('php://input');
$update = json_decode($content, true);

// Create a new instance of your TelegramBot class
$bot = new TelegramBot($token);

// Check if it's a message or callback query
if (isset($update['message']))
{
    $text = $update['message']['text'];
    $chat_id = $update['message']['chat']['id'];

    $user = $bot->findUser($chat_id);

    // Check if the user exists
    if (!$user)
    {
        $db = new Database();
        $db->insert('users', ['id', 'name', 'username'], [$chat_id, $update['chat']['first_name'], $update['chat']['username']] );
    }

    $bot->sendMessage($chat_id, 'خوش آمدید');





    $buttons = [];
    $db = new Database();
    $panels = $db->selectAll("SELECT * FROM panel_users WHERE user_id = ? ", [$chat_id]);

    foreach ($panels as $panel)
    {
        $selected_panels = $db->join('*' , 'panel_users' , 'panels' , 'panel_users.panel_id = panels.id' , 'panel_users.panel_id = ' . $panel['panel_id'] );
        $buttons[] = [['text' => $selected_panels['name'], 'callback_data' => strval($panel['id'] . ' panel')]];
    }


    if($panels == NULL)
    {
        $bot->sendMessage($chat_id,'hi');
        $templates = $bot->findDefaultTemplates();
        $buttons = [];


        foreach ($templates as $template)
        {
            $buttons[] = [['text' => $template['name'], 'callback_data' => strval($template['id'] . ' config')]];
        }
    }

    // Assuming here that you want to send a welcome message along with a button
    $replyMarkup = json_encode([
        'inline_keyboard' =>
            $buttons

    ]);

    $bot->sendMessage($chat_id, 'پنل های پیشنهادی شما:', $replyMarkup);
}


elseif (isset($update['callback_query'])) {
    // It's a callback query; get the callback data
    $callbackData = $update['callback_query']['data'];
    $callbackQueryId = $update['callback_query']['id'];
    $chat_id = $update['callback_query']['message']['chat']['id']; // Correctly retrieve the chat_id from the callback_query object

    // Check for your specific callback data
    if (explode( " " ,$callbackData)[1] == 'config')
    {



        // Respond to the callback query first
        $bot->answerCallbackQuery($callbackQueryId, 'You clicked on the ' .  $callbackData . ' button!');

        // Send a follow-up message to the chat indicating which button was clicked


        $db = new Database();

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
            $selected = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.template_id = ' . $callbackData[0] );
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
        $data_show = $selected['limitation'] / 1000000000;




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


        $bot->sendMessage($chat_id, $message , $reply);


    }

    elseif(explode( " " ,$callbackData)[1] == 'panel')
    {
        $bot->answerCallbackQuery($callbackQueryId, 'You clicked on the ' .  $callbackData . ' button!');

        $buttons = [];
        $db = new Database();
        $templates = $db->selectAll("SELECT * FROM templates WHERE panel_id = ? ", [$callbackData[0]]);
        foreach ($templates as $template)
        {
            $buttons[] = [['text' => $template['name'], 'callback_data' => strval($template['id'] . ' config')]];
        }

        $replyMarkup = json_encode([
            'inline_keyboard' =>
                $buttons

        ]);

        $bot->sendMessage($chat_id, 'کانفیگ های این پنل:', $replyMarkup);
    }

    elseif(explode( " " ,$callbackData)[0] == 'خرید')
    {


        $db = new Database();

        $configs = $db->selectAll("SELECT * FROM configs WHERE user_id = ? ", [$chat_id]);
        $countConfigs = count($configs);


        $selected = $db->join('*' , 'user_templates' , 'templates' , 'user_templates.template_id = templates.id' , 'user_templates.id = ' . explode( " " ,$callbackData)[1] );
        if($selected == null)
        {

            $selected = $db->select("SELECT * FROM templates WHERE `id` = ? " , [explode( " " ,$callbackData)[1]]);

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
        $data_show = $selected['limitation'] / 1000000000;
        $price = $selected['price'];
        $price_show = $price/1000;



        $result = $bot->makeUser($chat_id ,$username, $proxies, $expire, $data_limit);

        $config = $bot->getuser($username)['links'][0];

        if($config)
        {
            $result =$db->insert('configs', ['name', 'expire', 'limitation','proxy', 'user_id', 'price'],[$username, $selected['expire'], $data_limit, $selected['proxy'], $chat_id, $price ]);
            $bot->sendMessage($chat_id, $config);
            $user = $db->selectAll("SELECT * FROM users WHERE `id` = ? " , [$chat_id]);
            $indebtedness = $user[0]['indebtedness'];
            $indebtedness += $price;
            $bot->sendMessage($chat_id, 'بدهی شما در حال حاضر : '. $indebtedness / 1000 . ' هزار تومان  ');
            $db->update('users' , $chat_id, ['indebtedness'] , [$indebtedness]);
        }

        else
        {
            $bot->sendMessage($chat_id, "مشکلی پیش آمده لطفا دوباره امتحان کنید و در صورت عدم رفع مشکل با ادمین تماس حاصل فرمایید");
        }



    }


    else
    {
        // Log unexpected callback data for debugging
        error_log('Received unexpected callback data: ' . $callbackData);
    }
}

// ... Rest of your bot logic
?>
