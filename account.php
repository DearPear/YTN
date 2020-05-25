<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config =  include('./config.php');


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

    public static function validateUser($channelID, $token){
        self::initialize(); 
        $sth = self::$pdo->prepare("SELECT * FROM users WHERE channelID = :channelID AND token = :token");
        $params[":channelID"] = $channelID;
        $params[":token"] = $token;
        $sth->execute($params);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        if($result){
            return true;
        }
        return false;
    }
    public static function activateTracking($channelID, $token){
        self::initialize(); 

        if(self::validateUser($channelID, $token)){
            $sth = self::$pdo->prepare("UPDATE users SET trackPosition = 1 WHERE channelID = :channelID AND token = :token");
            $params = array();
            $params[":channelID"] = $channelID;
            $params[":token"] = $token;
            $sth->execute($params);
            return true;
        }
        else return false;        
    }
    
    public static function deactivateTracking($channelID, $token){
        self::initialize(); 
        if(self::validateUser($channelID, $token)){
            $sth = self::$pdo->prepare("UPDATE users SET trackPosition = 0 WHERE channelID = :channelID AND token = :token");
            $params = array();
            $params[":channelID"] = $channelID;
            $params[":token"] = $token;
            $sth->execute($params);
            return true;
        }
        else return false;        
    }

    public static function activateNotifications($channelID, $token){
        self::initialize(); 

        if(self::validateUser($channelID, $token)){
            $sth = self::$pdo->prepare("UPDATE users SET active = 1 WHERE channelID = :channelID AND token = :token");
            $params = array();
            $params[":channelID"] = $channelID;
            $params[":token"] = $token;
            $sth->execute($params);
            return true;
        }
        else return false;        
    }

    public static function deactivateNotifications($channelID, $token){
        self::initialize(); 

        if(self::validateUser($channelID, $token)){

            $sth = self::$pdo->prepare("UPDATE users SET active = 0 WHERE channelID = :channelID AND token = :token");
            $params = array();
            $params[":channelID"] = $channelID;
            $params[":token"] = $token;
            $sth->execute($params);
            $result = $sth->fetch();
         
            return true;
        }
        else return false;        
    }

    
    public static function deleteAccount($channelID, $token){
        self::initialize(); 
        if(self::validateUser($channelID, $token)){
            $sth = self::$pdo->prepare("DELETE FROM users WHERE channelID = :channelID AND token = :token");
            $params = array();
            $params[":channelID"] = $channelID;
            $params[":token"] = $token;
            $sth->execute($params);
            return true;
        }
        else return false;        
    }

}

$actions = array(
    'activateTracking',
    'deactivateTracking',
    'deleteAccount',
    'deactivateNotifications',
    'activateNotifications',
);

$action = $_GET['action'];
if(!in_array($action,$actions)) {
    echo `
    Erreur : L'action n'existe pas.
    <br><hr><br>
    Pour toute question, contactez-moi soit par  <a href="mailto:contact@lacherepoire.fr">mail</a> soit sur <a href="https://twitter.com/PearDear">@PearDear</a>
    <br><br>
    Bonne journée !
    `;
    die();}

if(!isset($_GET['channelID']) || !isset($_GET['token'])) {
    echo `
    Erreur : ID de chaîne ou token manquant.
    <br><hr><br>
    Pour toute question, tu peux me contacter soir sur Twitter <a href=\"https://twitter.com/PearDear\">@PearDear</a>, soit par mail sur <a href=\"mailto:contact@lacherepoire.fr\">contact@lacherepoire.fr</a> .
    <br><br>
    Bonne journée !
    `;
    die();}

if(myDB::$action($_GET['channelID'], $_GET['token'])){

 
    if($action == "activateTracking")  echo "Le tracking de l'évolution de tes vidéos a bien été <strong>activé</strong>.";
    if($action == "deactivateTracking")  echo "Le tracking de l'évolution de tes vidéos a bien été <strong>désactivé</strong>.";
    if($action == "deleteAccount")  echo "Ton compte a bien été <strong>supprimé</strong> du site.";
    if($action == "activateNotifications")  echo "Les notifications d'entrée de vidéos en tendance FR ont bien été <strong>activées</strong>.";
    if($action == "deactivateNotifications")  echo "Les notifications d'entrée de vidéos en tendance FR ont bien été <strong>désactivées</strong>.";
    echo `<br><hr><br>
    Pour toute question, contactez-moi soit par  <a href="mailto:contact@lacherepoire.fr">mail</a> soit sur <a href="https://twitter.com/PearDear">@PearDear</a>
    <br><br>
    Bonne journée !
    `;
    die();
} else {

    echo `Une erreur est survenue. Soit les données (ID de chaîne, token) sont erronées, soit le serveur rencontre des problèmes.
    <br><hr><br>
    Pour toute question, contactez-moi soit par  <a href="mailto:contact@lacherepoire.fr">mail</a> soit sur <a href="https://twitter.com/PearDear">@PearDear</a>
    <br><br>
    Bonne journée !
    `;
    die();
}
