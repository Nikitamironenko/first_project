<?php
/*
Oleg Mironenko 
2016-02-20
*/
require_once( TESTAMENT_PLUGIN_DIR . "inc/testament.inc" ) ;
 
/************************ STEP ONE ************************************
 1. Удаляем таблицу wp_testament_conf из TESTAMENT_DB
 2. Копируем wp_testament_conf из wordpress (DB_NAME) в TESTAMENT_DB
*/   
    $run =  " mysql -u" . DB_USER . " -p" . DB_PASSWORD . " " .  "TESTAMENT_DB" .  " -e 'drop table wp_testament_history' " . // Удалили DB
                " && mysqldump -u" . DB_USER ." -p" . DB_PASSWORD . " " . DB_NAME .  " --tables wp_testament_history " . // Копируем DB 
                " | mysql -u" . DB_USER . " -p" . DB_PASSWORD . " " .  "TESTAMENT_DB" ;

    $out = shell_exec( $run ) ;
   
 /************************** Восстанавливаем из Архива**************/
    $id = trim( $_GET[ 'id' ] ) ;         // 2016-02-01_01-12-29_WP.tgz  
    $dbFile =  substr( $id, 0, 19 ) ;    // 2016-02-01_01-12-29  отбросили расширение
    $dbFile =  $dbFile . "_DB.sql" ;   // 2016-02-01_01-12-29_DB.sql
    
    $run =   " rm -f "           . DIRHTML . "/*.tgz" . 
                " && mv -f "     . ABSPATH . BACKUPDIR . "* " . DIRHTML. 
                " && rm -rf "    . DIRHTML . "/". SITENAME . 
                " && tar -xf "   . DIRHTML . "/" . $id . " -C " . DIRHTML. 
                " && cd "         . DIRHTML . "/". SITENAME . 
                " && if ! [ -d BackUp ] ; then  mkdir BackUp ; fi " .
                " && cd "         . DIRHTML . 
                " && mv -f "     . DIRHTML . "/*.tgz " . ABSPATH . BACKUPDIR .
                " && mysql -u" . DB_USER . " -p" . DB_PASSWORD . " " .  DB_NAME . " < " . ABSPATH . TMPDIR . $dbFile  .
                " && rm -rf "    . ABSPATH . TMPDIR
           //     . " && mysql -u" . DB_USER . " -p" . DB_PASSWORD . " " .  DB_NAME . " < " . ABSPATH . TMPDIR . $dbFile  . "_My"
    ;

    $out = shell_exec( $run ) ;

    $wpBackUpDir = ABSPATH . BACKUPDIR ;
    $mainDir = getcwd() ;                // Запомнить текущий каталог                                          ???
    chdir( $wpBackUpDir ) ;             // Меняем текущий каталог

/************************ STEP TWO ************************************
 1. Удаляем таблицу wp_testament_conf из wordpress (DB_NAME)
 2. Копируем wp_testament_conf из TESTAMENT_DB в wordpress (DB_NAME)   // CREATE TABLE IF NOT EXISTS
 */ 
     $run =  " mysql -u" . DB_USER . " -p" . DB_PASSWORD . " " . DB_NAME . " -e 'drop table wp_testament_history' " .              // Удалили DB
                " && mysqldump -u" . DB_USER ." -p" . DB_PASSWORD . " " . "TESTAMENT_DB" . " --tables wp_testament_history " .   // Копируем DB
                " | mysql -u" . DB_USER . " -p" . DB_PASSWORD . " " . DB_NAME ;

    $out = shell_exec( $run ) ;   
     
 /************************** DataBase **************************/   
    global $wpdb ;   
    $tbl = $wpdb->prefix.'testament_history' ;
    $sql = "SELECT *  FROM $tbl" ;
    $out = $wpdb->get_results( $sql, ARRAY_A ) ;

    // Усли файлов на диске нет -> Удаляем в DB
    foreach ( $out as $file ) { 
        if ( file_exists(  $file[ 'name' ] ) !== true ) {
            $sql  = "DELETE FROM  $tbl  WHERE name = '%s' " ;
            $run = $wpdb->prepare( $sql, $file[ 'name' ] ) ;
            $err = $wpdb->query( $run ) ;        // $out      
        }  
    }
        // Устанавливаем Активный BackUp
    $tbl = $wpdb->prefix.'testament_conf' ;
    $wpdb->update( 
                $tbl,   // таблица
                array( // меняем 
                        "activBackup"   => $id
                ),  
                array( // где  
                        "db_user" => DB_USER
                )
    );  
    chdir( $mainDir ) ; // Возвращаем текущий каталог   
/********************************************************************************************************/ 

?>    
    <br><br><br><br><br>
    <ul>
        <li style="text-align:center"><a href="<?=$_SERVER[ 'PHP_SELF' ]?>?page=testament&c=view"> Archiv  [ <?=$id?> ]  restore. Return...</a></li>
    </ul>