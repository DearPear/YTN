<?php
use PHPMailer\PHPMailer\PHPMailer;
require('./vendor/autoload.php');
$config =  include('./config.php');
/* Classes utilitaires */
class myDB
{
    private static $pdo;
    private static $initialized = false;
    private static $config;
    private function __construct(){}
    private static function initialize(){
        if(self::$initialized) return;
        self::$initialized = true;
        self::$config =  include('./config.php');
        $host = self::$config['DB_HOST'];
        $db   = self::$config['DB_DATABASE'];
        $user = self::$config['DB_USERNAME'];
        $pass = self::$config['DB_PASSWORD'];
        $port = self::$config['DB_PORT'];
        $charset = self::$config['DATABASE_CHARSET'];
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        self::$pdo = new PDO($dsn, $user, $pass, $options);
    }

    public static function getUsersID(){
        self::initialize();
        $sth = self::$pdo->query("SELECT channelID, email, token, trackPosition FROM users");
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    public static function getVideoPosition($videoID){
        self::initialize(); 
        $sth = self::$pdo->query("SELECT position FROM videos WHERE videoID = '$videoID'");
        $result = $sth->fetch(PDO::FETCH_ASSOC);
  
        return $result['position'];
    }
    
    public static function getVideosID(){
        self::initialize();
        $sth = self::$pdo->query("SELECT videoID FROM videos");
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        $return = [];
        foreach($results as $result){
            $return[] = $result['videoID'];
        }
        return $return;
    }

    public static function addVideo($video){
        self::initialize();
        $sth = self::$pdo->query("INSERT INTO videos (videoID, position) VALUES('$video->id','$video->position')");
        $results = $sth->fetch(PDO::FETCH_ASSOC);
        return $results;
    }

    public static function updateVideoPos($video){
        self::initialize();
        $sth = self::$pdo->query("UPDATE videos set position = $video->position WHERE videoID = '$video->id'");
        $results = $sth->fetch(PDO::FETCH_ASSOC);
        return $results;
    }
}


class myMail
{

    private static $pdo;
    private static $initialized = false;
    private static $config;
    private function __construct(){}
    private static function initialize(){
        if(self::$initialized) return;
        self::$initialized = true;
        self::$config =  include('./config.php');
     

    }

    public static function sendNewNotification($video, $user){
        self::initialize();

        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->isHTML(true);
        $mail->Host       = self::$config["mail_host"];
        $mail->SMTPAuth   = true;
        $mail->Username   =  self::$config["mail_username"];
        $mail->Password   =  self::$config["mail_password"];
        $mail->SMTPSecure = self::$config['mail_security'];
        $mail->Port       =  self::$config["mail_port"];
        $mail->setFrom(self::$config["mail_username"],self::$config["mail_name"]);
        $mail->addAddress($user['email'], $user['email']);

        
        $subject = "[YTN] Vidéo passée en tendance FR : ". substr($video->snippet->title, 0, 50) . '…';
        $message =`<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                        <title>Notification tendance YouTube FR</title>
                    </head>
                    <body style="width:100%;margin:0;padding:0; max-width:640px;">
                    `;

        $message .="Ta vidéo \"". $video->snippet->title . "\" est passée en tendances FR à la position <span style=\"font-size:125%\">".$video->position."</span>. Tu peux y accéder ici <a href=\"https://www.youtube.com/watch?v=".$video->id."\"></a>.
        <br><br>
        Pour ne plus recevoir de notifications, tu peux soit envoyer un message privé à <a href=\"https://twitter.com/PearDear\">@PearDear</a>, ou répondre à <a href=\"mailto:contact@lacherepoire.fr\">contact@lacherepoire.fr</a> avec le message suivant : \"Unsubscribe [".$user['channelID'].":".$user['token']."]\".<br><br>
        Bonne journée !" ;
        $message.="</body>";
        $mail->Subject = $subject;
        $mail->Body = $message;
        return $mail->send();
    }


    public static function sendTrackNotification($video, $user, $oldpos){
        self::initialize();

        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->isHTML(true);
        $mail->Host       = self::$config["mail_host"];
        $mail->SMTPAuth   = true;
        $mail->Username   =  self::$config["mail_username"];
        $mail->Password   =  self::$config["mail_password"];
        $mail->SMTPSecure = self::$config['mail_security'];
        $mail->Port       =  self::$config["mail_port"];
        $mail->setFrom(self::$config["mail_username"],self::$config["mail_name"]);
        $mail->addAddress($user['email'], $user['email']);

        
        $subject = "[YTTP] Video : " . substr($video->snippet->title, 0, 50) . '… en position : ' . $video->position;
        $message =`<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                        <title>Notification tendance YouTube FR</title>
                    </head>
                    <body style="width:100%;margin:0;padding:0; max-width:640px;">s
                    `;

        $message .="Ta vidéo \"". $video->snippet->title . "\" est maintenant en position <span style=\"font-size:125%\">".$video->position."</span>, anciennement en position <span style=\"font-size:125%\">".$oldpos."</span> des tendances FR. Tu peux y accéder ici <a href=\"https://www.youtube.com/watch?v=".$video->id."\"></a>.
        <br><br>
        Pour ne plus recevoir de notifications, tu peux soit envoyer un message privé à <a href=\"https://twitter.com/PearDear\">@PearDear</a>, ou répondre à <a href=\"mailto:contact@lacherepoire.fr\">contact@lacherepoire.fr</a> avec le message suivant : \"Unsubscribe [".$user['channelID'].":".$user['token']."]\". N'hésite pas à me contacter avec ce même code pour activer / désactiver les notifications lors de la progression d'une vidéo en tendance !
            <br><br>
        Bonne journée !" ;
        $message.="</body>";
        $mail->Subject = $subject;
        $mail->Body = $message;
        return $mail->send();
    }
}



/* Construction des données */

$videosAlreadyNotified = myDB::getVideosID();
$users = myDB::getUsersID();
$channelsToNotify = [];
foreach($users as $user){
    $channelsToNotify[] = $user['channelID'];
}

/* Récupération du top 50 tendances */

$opts = [
    "http" => [
        "method" => "GET",
        "header" =>  "Accept: application/json\r\n"
    ]
];

$context = stream_context_create($opts);

$result = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,statistics&chart=mostPopular&locale=fr-FR&maxResults=50&regionCode=FR&key='.$config["yt-key"], false, $context);
$videos = json_decode($result);

/* Logique de tri, d'enregistrement et d'envoi de mail */

foreach($videos->items as $key => $item){

    $videos->items[$key]->position = $key+1;
    if(in_array($videos->items[$key]->snippet->channelId, $channelsToNotify)){ 
        if(!in_array($videos->items[$key]->id, $videosAlreadyNotified)){
            $userToNotify;
            foreach($users as $user){
                if($user['channelID'] == $videos->items[$key]->snippet->channelId)
                    $userToNotify = $user;
                }
            if(myMail::sendNewNotification($videos->items[$key],$user)){
                myDB::addVideo($videos->items[$key]);
            }
            else {        
            }        
        } else{
            $oldpos = myDB::getVideoPosition($videos->items[$key]->id);
            if($user['trackPosition'] && $videos->items[$key]->position < $oldpos){
                if(myMail::sendTrackNotification($videos->items[$key], $user,$oldpos)){
                    myDB::updateVideoPos($videos->items[$key]);
                }
            }
            else {
            }
        }
    }   
}





