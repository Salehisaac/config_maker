<?php

namespace app;

use DataBase\Database;
use DateInterval;
use DateTime;

require_once 'Database.php';

class TelegramBot
{


    private $apiUrl;
    private $botToken;
    public $username;
    public $limitation;
    public $proxies;
    public $expire;

    public $panelUrl;



    private $command;



    public function __construct($botToken)
    {
        $this->botToken = $botToken;
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";
    }

    /*    public function handelUpdate($update)
        {
            $message = $update['message'];
            $chatId = $message['chat']['id'];
            $text = $message['text'];

            if (strtolower($text) == "saleh")
            {
                $this->sendMessage($chatId, "salam saleh");
            }
            else
            {
                $this->sendMessage($chatId, "salam gooz");
            }



        }*/
    public function sendMessage($chatId, $text , $Myreply = null)
    {



        $url = $this->apiUrl . "sendMessage";

        $keyboard = [
            ['🌍 کانفیگ های پیشنهادی'],
            ['🧔 کانفیگ های شما'],
            ['💰 میزان بدهی شما'],
        ];

        if ($chatId == 291109889)
        {
            array_push($keyboard, ['🌍 ساخت توکن']);
        }
        $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
        $reply = json_encode($response);



        if ($Myreply != null)
        {
            $reply = $Myreply;

            $data = [
                'chat_id' => $chatId,
                'text' => $text,
                'reply_markup' => $reply,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            echo $response;
            curl_close($ch);
        }

        else
        {

            $data = [
                'chat_id' => $chatId,
                'text' => $text,
                'reply_markup' => $reply,

            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            echo $response;
            curl_close($ch);
        }




    }




    // public function setProxy($chatId, $text, $before = null)
    // {



    //     // Check if the command to set proxy is received
    //     if ($_SESSION['command'] == 'setProxy')
    //     {
    //         // Provide options for the user to choose from
    //         $keyboard = [
    //             ['Vless', 'Vmess' , 'none'],
    //         ];
    //         $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
    //         $reply = json_encode($response);

    //         $this->sendMessage(
    //             $chatId,
    //             'پروکسی مورد نظر را انتخاب کنید :',
    //             $reply
    //         );


    //     }



    //   else {

    //         // If none of the expected options are received, provide guidance
    //         $this->sendMessage(
    //             $chatId,
    //             'Sorry, I didn\'t understand. Please choose a valid option.'
    //         );
    //     }
    // }

    // private function handleProxyOption($chatId, $option)
    // {
    //     $db = new Database();
    //     $user = $db->select("SELECT * FROM users WHERE username = ? " , [$_SESSION['username']]);
    //     $user_id = $user['id'];

    //     $nameprotocol = array();

    //     // Perform actions based on the selected proxy option
    //     if ($option === 'Vless') {



    //         $keyboard = [
    //             ['بله' , 'خیر'],
    //         ];
    //         $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
    //         $reply = json_encode($response);
    //         $this->sendMessage(
    //             $chatId,
    //             'میخواهید فلو اضافه کنید ؟',
    //             $reply
    //         );

    //     }
    //     elseif ($option === 'Vmess') {

    //         $existingArray = $db->selectProxiesById($user_id);
    //         $existingArray['vmess'] = array();
    //         $serializedData = json_encode($existingArray);
    //         $db->update('users' , $user_id , ['proxies'] , [$serializedData]);

    //         $keyboard = [
    //             ['ادامه', 'پایان'],
    //         ];
    //         $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
    //         $reply = json_encode($response);
    //         $this->sendMessage(
    //             $chatId,
    //             'میخواهید پروکسی دیگه اضافه کنید ؟',
    //             $reply
    //         );
    //         $_SESSION['command'] = 'wannaContinue';


    //     }
    // }

    public function setExpire($chatId, $text, $before = null)
    {



        // Check if the command to set proxy is received
        if ( $this->command == 'setExpireDate' )
        {


            $reply = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'یک ماه', 'callback_data' => 'oneMonth'] ,
                        ['text' => 'دو ماه', 'callback_data' => 'twoMonth']
                    ]
                ]
            ]);

            $this->sendMessage(
                $chatId,
                'مدت زمان مورد نظر را انتخاب کنید :',
                $reply
            );
        }



        else {

            // If none of the expected options are received, provide guidance
            $this->sendMessage(
                $chatId,
                'لطفا یک پیام درست انتخاب کنید'
            );
        }
    }

    private function handlExpireOption($chatId)
    {
        $db = new Database();

        $content = file_get_contents('php://input');
        $update = json_decode($content, true);
        $callbackData = $update['callback_query']['data'];

        // Perform actions based on the selected proxy option
        if ($callbackData === 'oneMonth') {

            $currentDateTime = new DateTime();
            $currentDateTime->add(new DateInterval('P30D'));
            $timestamp = $currentDateTime->getTimestamp();
            $db->update('users', $chatId, ['expire', 'command'], [$timestamp, 'make']);

            $this->sendMessage(
                $update['callback_query']['message']['chat']['id'],
                '  زمان تنظیم شد : یک ماه ',
            );

            $reply = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'بریم', 'callback_data' => 'letsGo']
                    ]
                ]
            ]);

            $this->sendMessage(
                $chatId,
                'بریم ؟',
                $reply
            );
        }

        if ($callbackData === 'twoMonth') {
            $currentDateTime = new DateTime();
            $currentDateTime->add(new DateInterval('P60D'));
            $timestamp = $currentDateTime->getTimestamp();
            $db->update('users', $chatId, ['expire', 'command'], [$timestamp, 'make']);

            $this->sendMessage(
                $update['callback_query']['message']['chat']['id'],
                'زمان تنظیم شد : دو ماه'
            );

            $reply = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'بریم', 'callback_data' => 'letsGo']
                    ]
                ]
            ]);

            $this->sendMessage(
                $chatId,
                'بریم ؟',
                $reply
            );
        }
    }



    // public function handel($chatId , $text, $before = null)
    // {

    //     $db = new Database();

    //     if ($text === '/start')
    //     {

    //         // Start a conversation
    //         $this->sendMessage(
    //             $chatId,
    //             'خوش آمدید لطفا اطلاعات لازم را وارد کنید'
    //         );
    //     }


    //     elseif (strpos($text, 'set username') === 0)
    //     {

    //         // Extract username from the command
    //         $this->sendMessage(
    //             $chatId,
    //             'نام کاربری را وارد کنید :',
    //         );


    //     }
    //     elseif (preg_match('/^[a-zA-Z ]*$/', $text) && strpos($before, 'set username') === 0 )
    //     {


    //         $db->insert('users' , ['username'] , [$text]);

    //         $this->sendMessage(
    //             $chatId,
    //             'thank you username just set'
    //         );

    //         $_SESSION['username'] = $text;

    //     }


    //     elseif (strpos($text, 'set panel url') === 0 ) {
    //         // Extract password from the command
    //         $this->sendMessage(
    //             $chatId,
    //             'Please provide your panel_url:'
    //         );
    //     }

    //     elseif (preg_match('/^(https?|ftp):\/\/([a-zA-Z0-9.-]+)(:[0-9]+)?$/', $text) && strpos($before, 'set panel url') === 0)
    //     {
    //         $db->update('users' , $chatId , ['panel_url'] , [$text]);
    //         $this->sendMessage(
    //             $chatId,
    //             'thank you url just set'
    //         );

    //     }


    //     elseif (strpos($text, 'set limit') === 0) {
    //         // Extract password from the command
    //         $this->sendMessage(
    //             $chatId,
    //             'Please provide your limitation:'
    //         );
    //     }

    //     elseif (is_numeric($text) && strpos($before, 'set limit') === 0)
    //     {
    //         $username = $_SESSION['username'];
    //         $db->update('users' , $chatId , ['limitation'] , [$text]);
    //         $this->sendMessage(
    //             $chatId,
    //             'thank you limit just set ' . $username
    //         );


    //     }

    //     elseif (strpos($text, 'set proxy') === 0) {
    //         // Handle setting proxies
    //         $this->setProxy($chatId, $text, $before);
    //     }

    //     elseif (in_array($text, ['Vless', 'Vmess']) && strpos($before, 'set proxy') === 0) {

    //         // Handle the user's choice based on the selected option
    //         $this->handleProxyOption($chatId, $text);
    //     }

    //     elseif (strpos($text, 'بله') === 0 && strpos($before, 'Vless') === 0) {

    //         // Handle the user's choice based on the selected option
    //         $nameprotocol = array();
    //         $nameprotocol['vless']['flow'] = 'xtls-rprx-vision';
    //         $serializedData = json_encode($nameprotocol);
    //         $db->update('users' , $chatId , ['proxies'] , [$serializedData]);

    //         $this->sendMessage(
    //             $chatId,
    //             'پروکسی تنظیم شد'
    //         );
    //         $_SESSION['command'] = 'wannaContinue';

    //     }

    //     elseif (strpos($text, 'خیر') === 0 && strpos($before, 'Vless') === 0) {


    //         $nameprotocol = array();
    //         $existingArray['vless'] = array();
    //         $serializedData = json_encode($nameprotocol);
    //         $db->update('users' , $chatId , ['proxies'] , [$serializedData]);

    //         $this->sendMessage(
    //             $chatId,
    //             'پروکسی تنظیم شد'
    //         );
    //         $_SESSION['command'] = 'wannaContinue';
    //     }

    //     elseif (strpos($text, 'set expire') === 0) {
    //         // Handle setting proxies
    //         $this->setExpire($chatId, $text, $before);
    //     }

    //     elseif (in_array($text, ['30 روزه', '60 روزه']) && strpos($before, 'set expire') === 0) {

    //         // Handle the user's choice based on the selected option
    //         $this->handlExpireOption($chatId, $text);
    //     }





    //     else {

    //         echo '<pre>';
    //         var_dump($text);

    //         // If none of the expected commands or responses are received, provide guidance
    //         $this->sendMessage(
    //             $chatId,
    //             'Sorry, I didn\'t understand. Please follow the instructions.'
    //         );
    //     }

    // }

    public function makeUser($chatId , $username , $proxies, $expire , $data_limit, $panel_url )
    {


        $this->proxies = $proxies;
        $this->expire = $expire;
        $this->limitation = $data_limit;
        $this->username = $username;

        $url_panel = $panel_url;
        $url = $url_panel."/api/user";
        $header_value = 'Bearer ';


        $data = array(
            "proxies" => $proxies,
            "expire" => $expire,
            "data_limit" => $data_limit ,
            "username" => $username
        );

        echo "<pre>";
        var_dump($data);

        $token = $this->token_panel("kian" , 'kian1381', $panel_url);

        $payload = json_encode($data);


        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'JSON error: ' . json_last_error_msg();
        }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Authorization: ' . $header_value .  $token['access_token'],
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($ch);
        var_dump($response);
        if(curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }


        return $response;
    }

    public function handleInOrder($chatId , $text, $before = null)
    {

        $db = new Database();
        $user = $db->select("SELECT * FROM users WHERE id = ? " , [$chatId] );
        $this->command = $user['command'];
        var_dump($this->command);

        if ($this->command == null)
        {

            // Start a conversation
            $this->sendMessage(
                $chatId,
                'خوش آمدید لطفا نام کانفیگ را وارد کنید' ,
            );

            $db->insert('users',['id' , 'command'] , [$chatId , 'set_name'] );
        }

        elseif ($this->command == 'set_name') {

            $db->update('users', $chatId ,['name', 'command'], [$text, 'setLimitation' ]);

            $this->sendMessage
            (
                $chatId,
                'نام کاربری شما ثبت شد'
                . 'حجم کانفیگ را وارد نمایید'
            );


        }
        elseif ($this->command == 'setLimitation' && is_numeric($text)) {


            $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chatId]);
            $user_id = $user['id'];
            $db->update('users', $chatId, ['limitation' , 'command'], [$text, 'make' ]);
            $this->command = 'setExpireDate' ;

            $this->sendMessage(
                $chatId,
                'مقدار حجم مورد نظر شما ثبت شد'
            );

            $this->setExpire($chatId, $text);

        }

        elseif ($this->command = 'make')
        {
            var_dump('hi');
            $this->handlExpireOption($chatId);

        }
        // elseif (($_SESSION['response'] == 'Vless' || $_SESSION['response'] == 'Vmess') && $_SESSION['command'] = 'setProxy')
        // {

        //     $this->handleProxyOption($chatId, $_SESSION['response']);


        // }
        // elseif ($_SESSION['response'] == 'بله' && $_SESSION['command'] = 'setProxy') {
        //     $user = $db->select("SELECT * FROM users WHERE username = ? ", [$_SESSION['username']]);
        //     $user_id = $user['id'];


        //     $existingArray = $db->selectProxiesById($user_id);
        //     $existingArray['vless']['flow'] = 'xtls-rprx-vision';
        //     $serializedData = json_encode($existingArray);
        //     $db->update('users', $user_id, ['proxies'], [$serializedData]);

        //     $keyboard = [
        //         ['پایان', 'ادامه'],
        //     ];
        //     $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
        //     $reply = json_encode($response);
        //     $this->sendMessage(
        //         $chatId,
        //         'میخواهید پروکسی دیگه اضافه کنید ؟',
        //         $reply
        //     );
        //     $_SESSION['command'] = 'wannaContinue';

        // }
        // elseif ($_SESSION['response'] == 'خیر' && $_SESSION['command'] = 'setProxy')
        // {
        //     $user = $db->select("SELECT * FROM users WHERE username = ? ", [$_SESSION['username']]);
        //     $user_id = $user['id'];


        //     $existingArray = $db->selectProxiesById($user_id);
        //     $existingArray['vless'] = array();
        //     $serializedData = json_encode($existingArray);
        //     $db->update('users', $user_id, ['proxies'], [$serializedData]);

        //     $keyboard = [
        //         ['پایان', 'ادامه'],
        //     ];
        //     $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
        //     $reply = json_encode($response);
        //     $this->sendMessage(
        //         $chatId,
        //         'میخواهید پروکسی دیگه اضافه کنید ؟',
        //         $reply
        //     );
        //     $_SESSION['command'] = 'wannaContinue';


        // }


        elseif ($this->command == 'make')
        {

            $this->sendMessage(
                $chatId,
                'میسازمش برات',

            );



            $this->makeUser($chatId);
            $db->update('users', $chatId, ['command'], ['fenish' ]);


        }

        // elseif ($_SESSION['response'] == 'ادامه' && $_SESSION['command'] == 'wannaContinue')
        // {
        //     echo '<pre>';
        //     var_dump('hi');
        //     $_SESSION['command'] = 'setProxy';
        //     $this->setProxy($chatId, $text);
        // }

        // elseif ($_SESSION['response'] == 'پایان' && $_SESSION['command'] == 'wannaContinue')
        // {
        //     $_SESSION['command'] = 'make';
        //     $keyboard = [
        //         ['بریم'],
        //     ];
        //     $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
        //     $reply = json_encode($response);
        //     $this->sendMessage(
        //         $chatId,
        //         'بریم؟',
        //         $reply
        //     );

        // }

        else
        {
            echo "<pre>";
            var_dump($_SESSION['response']);
            var_dump($_SESSION['command']);
        }


    }

    function token_panel($username_panel,$password_panel, $panel_url){

        $url_panel = $panel_url;
        $url_get_token = $url_panel.'/api/admin/token';
        $data_token = array(
            'username' => $username_panel,
            'password' => $password_panel
        );
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT_MS => 3000,
            CURLOPT_POSTFIELDS => http_build_query($data_token),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'accept: application/json'
            )
        );
        $curl_token = curl_init($url_get_token);
        curl_setopt_array($curl_token, $options);
        $token = curl_exec($curl_token);
        curl_close($curl_token);

        $body = json_decode( $token, true);
        return $body;
    }

    function getuser($username,$url_panel)
    {
        $usernameac = $username;
        $url =  $url_panel.'/api/user/' . $usernameac;
        $header_value = 'Bearer ';
        $token = $this->token_panel("kian" , 'kian1381', $url_panel);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Authorization: ' . $header_value .  $token['access_token']
        ));

        $output = curl_exec($ch);
        curl_close($ch);
        $data_useer = json_decode($output, true);
        return $data_useer;
    }

    function findUser($chat_id)
    {
        $db = new Database();

        $user = $db->select("SELECT * FROM users WHERE `id` = ? " , [$chat_id] );

        if(empty($user))
        {
            return false;
        }

        return $user;
    }

    function findTemplatesForUser($chat_id)
    {
        $db = new Database();
        $result = $db->fetching($chat_id);
        return $result;
    }

    function findTemplatesId($chat_id)
    {
        $db = new Database();
        $result = $db->fetching($chat_id);
        return $result;
    }

    function findDefaultTemplates()
    {
        $db = new Database();
        $result = $db->fetchDefault();
        return $result;
    }

    public function answerCallbackQuery($callbackQueryId, $text)
    {
        $apiURL = 'https://api.telegram.org/bot6813131583:AAHhfKYcObFrXsuzZ-7oZD_ldi6X2rU4K-k/answerCallbackQuery';

        $response = [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ];

        $ch = curl_init($apiURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $response);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            echo $result;
        }

        curl_close($ch);
    }

    function generateRandomString($length = 15) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }









}

$botToken = "6809114912:AAEnGhJ_em9lf9I1uofJAXfkiiVd8AgFOyE";
$bot = new TelegramBot($botToken);
$update = json_decode(file_get_contents("php://input"), true);
/*$bot->handelUpdate($update);*/









