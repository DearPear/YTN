# YTN

Pour faire des notifications, via mail, automatiquement de l'entrée et de la progression des vidéos d'une chaîne dans les tendances FR de YouTube.

L'outil est conçu pour spécifiquement aider les vidéastes à être notifiés de l'avancée en tendance de leurs vidéos.

Fonctionnement :

- Importer le fichier yt_trendings.sql dans une base de données MySQL.
- Une chaîne doit être ajoutée à la main dans la base de données. Le champ channelID doit récupérer l'ID réel de la chaîne, et non pas le nom de la chaîne. Exemple : UCUXSbfKSaR3hoYh54Gt6e6w
- Le champ "token" n'est pour l'instant pas utilisé, sauf pour permettre à un vidéaste de faire état de son identité. Il est fourni au vidéaste dans les mails envoyés.
- Le champ "trackPosition" doit être mis à 1 si le vidéaste souhaite avoir, en plus, des mails lors de la progression de sa vidéo en tendance. Mettre à 0 sinon.

La base de données ne conserve pas de lien entre le vidéaste et ses vidéos. Le lien est fait uniquement au travers de l'API de YouTube.

- Remplir le fichier config.php :

    'DB_HOST' => '',
    
    'DB_USERNAME' => '', 
    
    'DB_PORT' => 3306, 
    
    'DB_PASSWORD' => '', 
    
    'DB_DATABASE' => 'yt_trendings', 
    
    'DATABASE_CHARSET' => 'utf8',
    
    'yt-key' => '', 
        
    'mail_host' => '', 
    
    'mail_port' => 465, 
    
    'mail_security' => '', // généralement soit "ssl", soit "STARTTLS".
    
    'mail_username' => "",
    
    'mail_name' => "", // Le nom affiché à la place de l'adresse mail dans la boite mail du réceptionneur.
    
    'mail_password' => ''
    
    
Mettre en place un job cron toutes les 15 minutes "php -q pathtoproject/ytn.php"
