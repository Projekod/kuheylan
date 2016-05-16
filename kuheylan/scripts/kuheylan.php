<?php
$configFile = realpath(__DIR__."/../config.yaml");
$contentContent = file_get_contents($configFile);
$config = yaml_parse($contentContent);

echo "\n";



/* Sites */
if(isset($config["kuheylan-default"]["vm"]["sites"])){

    $sitesEnable='/etc/apache2/sites-enabled/';
    $sitesAvailable='/etc/apache2/sites-available/';

    $vhScript = realpath(__DIR__."/virtualhost.sh");
    $sites = $config["kuheylan-default"]["vm"]["sites"];

    array_reduce(glob($sitesAvailable."*"),function($result,$item) use($sitesAvailable){
        if(!strpos($item,"000-default.conf") && !strpos($item,"default-ssl.conf")){
            $domain = str_replace([".conf",$sitesAvailable],"",$item);
            shell_exec("sudo a2dissite $domain");
            unlink($item);
        }
    });

    foreach($sites as $site){
        $siteName = trim($site["name"]);
        $siteDir = trim($site["dir"]);

        @mkdir($site["dir"],0777,true);
        shell_exec('chmod 755 '.$site["dir"]);

        $fileContent = "
        <VirtualHost *:80>
            ServerAdmin admin@admin.com
            ServerName $site[name]
            ServerAlias $site[name]
            DocumentRoot $site[dir]
            <Directory />
                AllowOverride All
            </Directory>
            <Directory $site[dir]>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride all
                Require all granted
            </Directory>
            ErrorLog /var/log/apache2/$site[name]-error.log
            LogLevel error
            CustomLog /var/log/apache2/$site[name]-access.log combined
        </VirtualHost>";

        file_put_contents($sitesAvailable."/$site[name].conf",$fileContent);

        shell_exec("sudo a2ensite $site[name]");

    }
    shell_exec("/etc/init.d/apache2 reload");

    echo "\033[0m[ \033[32mOK\033[0m ] Virtual Host\n";
}

/*mysql*/
if(isset($config["kuheylan-default"]["vm"]["databases"])){
    $vhScript = realpath(__DIR__."/mysql.sh");

    $dbs = $config["kuheylan-default"]["vm"]["databases"];

    foreach($dbs as $db){
        exec("sudo bash $vhScript $db");
    }
    echo "\033[0m[ \033[32mOK\033[0m ] Database\n";
}


echo "\n";