<?php

namespace app;
require __DIR__.'/vendor/autoload.php';


use DataBase\Database;
use DateInterval;
use DateTime;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;


require_once 'Database.php';
include 'PhpQrCode/qrlib.php';





class TelegramBot
{


    private $apiUrl;

    private $panel_username;

    private $panel_password;
    private $botToken;
    public $username;
    public $limitation;
    public $proxies;
    public $expire;
    private $dbUsername;
    private $dbPassword;
    public $panelUrl;



    private $command;



    public function __construct($botToken , $panel_username , $panel_password, $dbUsername , $dbPassword)
    {
        $this->botToken = $botToken;
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";
        $this->panel_username = $panel_username;
        $this->panel_password = $panel_password;
        $this->dbUsername = $dbUsername;
        $this->dbPassword = $dbPassword;
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
    
    
    public function testQr($text)
    {
        $writer = new PngWriter();

        // Create QR code
        $qrCode = QrCode::create($text)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        
        // Create generic logo
        $logo = Logo::create(__DIR__.'/assets/symfony.jpg')
            ->setResizeToWidth(50)
            ->setPunchoutBackground(true)
        ;
        
        // Create generic label
        $label = Label::create('Here you are')
            ->setTextColor(new Color(0, 0, 0));
        
        $result = $writer->write($qrCode, $logo, $label);
        
        
        // Validate the result
        $validated_result = $writer->validateResult($result, $text);
       
        return $result->getString();
        

    }
    
     public function sendMessage($chatId, $text , $Myreply = null )
    {



        $url = $this->apiUrl . "sendMessage";
        $db = new Database($this->dbUsername , $this->dbPassword);
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chatId] );

        if($user['is_verified'] == 'approved' && $Myreply == null)
        {
            $keyboard = [
                ['🌍 کانفیگ های پیشنهادی'],
                ['🧔 کانفیگ های شما'],
                ['میزان بدهی شما'],
                ['تماس با پشتیبانی'],
            ];

            if($user['is_admin'] == 1)
            {
                array_push($keyboard, ['پنل مدیریت']);
            }

            $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
            $reply = json_encode($response);
        }

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

            $result = json_decode($response, true);
            $messageId = $result['result']['message_id'];
            curl_close($ch);
            return $messageId;
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

            $result = json_decode($response, true);
            $messageId = $result['result']['message_id'];
            curl_close($ch);
            return $messageId;
        }





    }

    public function forwardMessage($chat_id , $message_id , $Myreply = null )
    {

        $db = new Database($this->dbUsername , $this->dbPassword);
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );

        if($user['is_verified'] == 'approved' && $Myreply == null)
        {
            $keyboard = [
                ['🌍 کانفیگ های پیشنهادی'],
                ['🧔 کانفیگ های شما'],
                ['میزان بدهی شما'],
                ['تماس با پشتیبانی'],
            ];

            if($user['is_admin'] == 1)
            {
                array_push($keyboard, ['پنل مدیریت']);
            }

            $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
            $reply = json_encode($response);
        }
       
        
        $apiUrl = "https://api.telegram.org/bot{$this->botToken}";

        if ($Myreply != null)
        {
            $forwardMessageRequest = [
                'chat_id' => 135629482,
                'from_chat_id' => $chat_id, 
                'message_id' => $message_id,
                'reply_markup' => $Myreply,
            ];
        }

        else
        {
            $forwardMessageRequest = [
                'chat_id' => 135629482,
                'from_chat_id' => $chat_id, 
                'message_id' => $message_id,
                'reply_markup' => $reply,
            ];
        }
        

        
        $ch = curl_init("$apiUrl/forwardMessage");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $forwardMessageRequest);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        
        $response = curl_exec($ch);
        $result = json_decode($response, true);
            $messageId = $result['result']['message_id'];
            curl_close($ch);
            return $messageId;


        
        if ($response === false) {
            echo 'Error occurred while forwarding the message: ' . curl_error($ch);
        } else {
            
            $responseData = json_decode($response, true);
            if ($responseData['ok']) {
                echo 'Message forwarded successfully!';
            } else {
                echo 'Error forwarding the message: ' . $responseData['description'];
            }
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
    //     $db = new Database($this->dbUsername , $this->dbPassword);
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

    // public function setExpire($chatId, $text, $before = null)
    // {



    //     // Check if the command to set proxy is received
    //       if ( $this->command == 'setExpireDate' )
    //     {


    //         $reply = json_encode([
    //             'inline_keyboard' => [
    //                 [
    //                     ['text' => 'یک ماه', 'callback_data' => 'oneMonth'] ,
    //                     ['text' => 'دو ماه', 'callback_data' => 'twoMonth']
    //                 ]
    //             ]
    //                 ]);

    //         $this->sendMessage(
    //             $chatId,
    //             'مدت زمان مورد نظر را انتخاب کنید :',
    //             $reply
    //         );
    //     }



    //     else {

    //         // If none of the expected options are received, provide guidance
    //         $this->sendMessage(
    //             $chatId,
    //             'لطفا یک پیام درست انتخاب کنید'
    //         );
    //     }
    // }

    // private function handlExpireOption($chatId)
    // {
    //     $db = new Database($this->dbUsername , $this->dbPassword);

    //     $content = file_get_contents('php://input');
    //     $update = json_decode($content, true);
    //     $callbackData = $update['callback_query']['data'];

    //     // Perform actions based on the selected proxy option
    //     if ($callbackData === 'oneMonth') {

    //         $currentDateTime = new DateTime();
    //         $currentDateTime->add(new DateInterval('P30D'));
    //         $timestamp = $currentDateTime->getTimestamp();
    //         $db->update('users', $chatId, ['expire', 'command'], [$timestamp, 'make']);

    //         $this->sendMessage(
    //             $update['callback_query']['message']['chat']['id'],
    //             '  زمان تنظیم شد : یک ماه ',
    //         );

    //         $reply = json_encode([
    //             'inline_keyboard' => [
    //                 [
    //                     ['text' => 'بریم', 'callback_data' => 'letsGo']
    //                 ]
    //             ]
    //         ]);

    //         $this->sendMessage(
    //             $chatId,
    //             'بریم ؟',
    //             $reply
    //         );
    //     }

    //     if ($callbackData === 'twoMonth') {
    //         $currentDateTime = new DateTime();
    //         $currentDateTime->add(new DateInterval('P60D'));
    //         $timestamp = $currentDateTime->getTimestamp();
    //         $db->update('users', $chatId, ['expire', 'command'], [$timestamp, 'make']);

    //         $this->sendMessage(
    //             $update['callback_query']['message']['chat']['id'],
    //             'زمان تنظیم شد : دو ماه'
    //         );

    //         $reply = json_encode([
    //             'inline_keyboard' => [
    //                 [
    //                     ['text' => 'بریم', 'callback_data' => 'letsGo']
    //                 ]
    //             ]
    //         ]);

    //         $this->sendMessage(
    //             $chatId,
    //             'بریم ؟',
    //             $reply
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

        $token = $this->token_panel($this->panel_username , $this->panel_password , $panel_url);
        var_dump($token);

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



    function token_panel($username_panel,$password_panel, $panel_url)
    {


        $url_panel = $panel_url;
        $url_get_token = $panel_url.'/api/admin/token';
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
        $token = $this->token_panel($this->panel_username , $this->panel_password, $url_panel);

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
        $db = new Database($this->dbUsername , $this->dbPassword);

        $user = $db->select("SELECT * FROM users WHERE `id` = ? " , [$chat_id] );

        if(empty($user))
        {
            return false;
        }

        return $user;
    }

    function findTemplatesForUser($chat_id)
    {
        $db = new Database($this->dbUsername , $this->dbPassword);
        $result = $db->fetching($chat_id);
        return $result;
    }

    function findTemplatesId($chat_id)
    {
        $db = new Database($this->dbUsername , $this->dbPassword);
        $result = $db->fetching($chat_id);
        return $result;
    }

    function findDefaultTemplates()
    {
        $db = new Database($this->dbUsername , $this->dbPassword);
        $result = $db->fetchDefault();
        return $result;
    }

    function findDefaultPanels()
    {
        $db = new Database($this->dbUsername , $this->dbPassword);
        $panels = $db->selectAll("SELECT * FROM panels WHERE is_default = ?" , [1]);

        return $panels;
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

    function deleteMessage($chatId, $messageId)
    {

        $apiUrl = "https://api.telegram.org/bot{$this->botToken}/";


        $deleteUrl = $apiUrl . "deleteMessage?chat_id=$chatId&message_id=$messageId";



        $ch = curl_init($deleteUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);


        $result = json_decode($response, true);


        if ($result['ok']) {
            return true;
        } else {
            return false;
        }
    }

    public function makeQRcode($url)
    {
        $url = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=http%3A%2F%2F' . $url . '%2F&choe=UTF-8';
        


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
            return false;
        }

        curl_close($ch);
        
        return $response;
    }

    public function sendImage($chat_id ,$imageData, $caption, $username , $Myreply = null)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendPhoto";

        // Create a temporary file to store the image data
        $tempFile = tempnam(sys_get_temp_dir(), 'telegram_image');
        file_put_contents($tempFile, $this->testQr($imageData));

        $db = new Database($this->dbUsername , $this->dbPassword);
        $user = $db->select("SELECT * FROM users WHERE id = ? ", [$chat_id] );
        if($user['is_verified'] == 'approved' && $Myreply == null)
        {
            $keyboard = [
                ['🌍 کانفیگ های پیشنهادی'],
                ['🧔 کانفیگ های شما'],
                ['میزان بدهی شما'],
                ['تماس با پشتیبانی'],
            ];

            if($user['is_admin'] == 1)
            {
                array_push($keyboard, ['پنل مدیریت']);
            }




            $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
            $reply = json_encode($response);
        }


        $response = ['keyboard' => $keyboard, 'resize_keyboard' => true];
        $reply = json_encode($response);







        if ($Myreply != null)
        {
            $reply = $Myreply;

            $postFields = [
                'chat_id' => $chat_id,
                'photo' => curl_file_create($tempFile, 'image/png', 'image.png'),
                'caption' => "{$caption}
                
                
                {$username}",
                'reply_markup' => $reply,
            ];


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);

            $result = json_decode($response, true);
            $messageId = $result['result']['message_id'];
            curl_close($ch);
            unlink($tempFile);
            return $messageId;
        }

        else
        {

            $postFields = [
                'chat_id' => $chat_id,
                'photo' => curl_file_create($tempFile, 'image/png', 'image.png'),
                'caption' => "{$caption}
                
                
                {$username}",
                'reply_markup' => $reply,
            ];


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);

            $result = json_decode($response, true);
            $messageId = $result['result']['message_id'];
            curl_close($ch);
            unlink($tempFile);
            return $messageId;
        }




    }

    public function search($username)
    {
        $db = new Database($this->dbUsername , $this->dbPassword);
        $trimmedString = preg_replace('/_\d+$/', '', $username);
        $configs = $db->selectAll("SELECT * FROM `configs` WHERE `name` LIKE ?" , ["%$trimmedString%"]);
        return $configs;

    }

    public function searchConfigsBYChatId($chat_id , $panel_id)
    {
        $db = new Database($this->dbUsername , $this->dbPassword);
        $configs = $db->selectAll("SELECT * FROM `configs` WHERE `user_id` = ? AND `panel_id` = ?  ORDER BY `created_at` DESC LIMIT 30" , [$chat_id , $panel_id]);
        return $configs;
    }

    public function directSearch($username, $panel_id)
    {
        $db = new Database($this->dbUsername , $this->dbPassword);
        $config = $db->select("SELECT * FROM `configs` WHERE `name` = ? AND `panel_id` = ?", [$username, $panel_id]);
        return $config;
    }


    function getAllUsers($urlPanel)
    {




        $url = 'http://ts1.kroute.site:8423/api/users';
        $token = $this->token_panel($this->panel_username , $this->panel_password, $urlPanel);




        $options = [
            'http' => [
                'header' => "Authorization: Bearer " . $token['access_token']
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = file_get_contents($url, false, $context);

        if ($response !== false) {
            // Process the response
            echo $response;
        } else {
            // Handle error
            echo 'Error fetching data';
        }


    }

    function removeuser($url_panel,$username)
    {



        $url =  $url_panel.'/api/user/'.$username;
        $header_value = 'Bearer ';
        $token = $this->token_panel($this->panel_username , $this->panel_password , $url_panel);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
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

    function canDelete()
    {

        $yesterday = date('Y-m-d H:s:i', strtotime('-1 day'));
        return $yesterday;

    }

    function Modifyuser($url_panel,$username,array $data)
    {


        $token = $this->token_panel($this->panel_username , $this->panel_password, $url_panel);

        $url =  $url_panel.'/api/user/'.$username;
        $payload = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Authorization: Bearer '.$token['access_token'];
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        curl_close($ch);
        $data_useer = json_decode($result, true);
        return $data_useer;
    }

    function Extend ($panel,$username)
    {
        $db = new Database($this->dbUsername , $this->dbPassword);
        $config = $db->select("SELECT * FROM `configs` WHERE `name` = ? AND `panel_id` = ?", [$username, $panel['id']]);

        $originalDate = new DateTime($config['expires_at']);
        $minutesInOneMonth = (30 * 24 * 60);
        $modifiedDate = $originalDate->add(new DateInterval("PT{$minutesInOneMonth}M"));
        $timestamp = $modifiedDate->getTimestamp();
        $timestampString = date('Y-m-d H:i:s', $timestamp);

        $db->update('configs', $config['id'] , ['expires_at'] , [$timestampString]);
        $extended = $this->Modifyuser($panel['url'] , $username , ['expire' => $timestamp]);
        return $extended;
    }

    function ResetUserDataUsage($username,$url_panel)
    {


        $token = $this->token_panel($this->panel_username , $this->panel_password , $url_panel);
        $usernameac = $username;
        $url =  $url_panel.'/api/user/' . $usernameac.'/reset';
        $header_value = 'Bearer ';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST , true);
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

    public function validName($username , $url_panel , $number)
    {
        $chat_id = 5628273659;
        $user = $this->getuser($username, $url_panel);
        $i = $number;
        while ($user['detail'] != "User not found")
        {
            $list = (explode('_', $username));
            $last_part = end($list);
            if ($last_part == '2u' || $last_part == '2m' || $last_part == '3u' || $last_part == '3m')
            {
                $wtest1 = $bot->sendMessage($chat_id, 'detected as 2u2m '.$uesrname);
                array_pop($list);
                array_push($list, $i);
            }
            elseif (is_int($last_part)){
                $wtest1 = $bot->sendMessage($chat_id, 'detected as int '.$uesrname);
                $list[count($list) - 1] = $i ;
            }else{
                array_push($list, $i);
            }
            $username = implode('_', $list);
            $user = $this->getuser($username, $url_panel);
            $i++;
        }

        return $username;

    }







}












