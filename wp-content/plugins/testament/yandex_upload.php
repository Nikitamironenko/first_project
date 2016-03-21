<?php
/*
Oleg Mironenko 
2016-02-17

Денег вообще нет!!!
Ходил в рекрутовую компанию.

nohup php -q script.php $arch_name_wp > script.log 2>&1 &
php  /var/www/html/blog/wp-content/plugins/testament/ya_upload.php morse@yandex.ru Ua3 2016-02-10_00-12-06_WP.tgz my

1. $ya_login
2. $ya_pass
3. $arch_name_wp - Имя архива
4. $ya_dir - Директория на yandex.disk куда писать
*/
        require_once( __DIR__ . "/inc/yd.inc" ) ; 
        $wpBackUpDir = realpath( __DIR__ .  '/../../..'  ) . '/BackUp/' ;
        
        $ya_login =  $argv[1] ;        
        $ya_pass  =  $argv[2] ;        
        $arch_name_wp = $argv[3] ;
        $ya_dir = $argv[4] ;

        $disk = new yd( $ya_login, $ya_pass ) ;
        $disk->put( $wpBackUpDir . $arch_name_wp, '/' . $ya_dir . '/' . $arch_name_wp ) ; 
?>


