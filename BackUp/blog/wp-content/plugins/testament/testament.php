<?php
/*
Plugin Name: Testament 
Plugin URI: http://olegmironenko.ru
Description: BackUp Site & Db
Version: 1.0
Author: Oleg Mironenko
Author URI: http://www.olegmironenko.ru
License: GPL2
*/

/*
2016-02-20

chgrp  -R www-data /var/www/html/blog && chown -R www-data:www-data /var/www/html/blog && chmod -R 775 /var/www/html/blog
========================================================================================================================

*/
define( 'TESTAMENT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
require_once( TESTAMENT_PLUGIN_DIR . "inc/testament.inc" ) ;

add_action( 'admin_menu', 'testament_admin_menu' ) ;

register_activation_hook( __FILE__, 'testament_install' );
register_deactivation_hook( __FILE__, 'testament_deactivation' );
register_uninstall_hook( __FILE__, 'testament_uninstall' );


function testament_install() {

    global $wpdb ; 

    $tbl = $wpdb->prefix.'testament_conf' ;
    $sql = "CREATE TABLE " . $tbl . " (
                    id INT NOT NULL AUTO_INCREMENT,
                    db_user  VARCHAR(32) ,
                    ya_login VARCHAR(32) ,
                    ya_pass VARCHAR(32) ,
                    ya_dir VARCHAR(32) ,
                    delNdays INT ,
                    saveNfails INT ,
                    activBackup VARCHAR(32) ,
                    PRIMARY KEY (id) 
                ) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; " ;

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql ) ;
    
    $sql = "INSERT INTO  $tbl  ( db_user ) VALUES ( '%s' ) " ;
    $run = $wpdb->prepare( $sql, DB_USER ) ;
    $out = $wpdb->query( $run ) ;    
            
/**************************************************************/
   
    $tbl = $wpdb->prefix.'testament_history' ;
    $sql = "CREATE TABLE " . $tbl . " (
                    id INT NOT NULL AUTO_INCREMENT,
                    name VARCHAR(32) ,
                    comment VARCHAR(32) ,
                    yandex CHAR(1) ,
                    PRIMARY KEY (id) 
                ) 
                ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ; " ;
            
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql ) ;   


  /**************** STEP ONE DB и делаем копию *******/   
    //mysql -u root -p1 -e "CREATE DATABASE TESTAMENT_DB"
    $run = "mysql -u" . DB_USER ." -p" . DB_PASSWORD . " -e 'CREATE DATABASE TESTAMENT_DB' " ;
    $out = shell_exec( $run ) ;

 // Копируем DB
    $run = "mysqldump -u" . DB_USER ." -p" . DB_PASSWORD . " " . DB_NAME . " --tables wp_testament_history " . 
                " | mysql -u" . DB_USER . " -p" . DB_PASSWORD . " " .  "TESTAMENT_DB" ;
    $out = shell_exec( $run ) ;
     
    if ( !is_dir( ABSPATH . BACKUPDIR ))  {
        mkdir( ABSPATH . BACKUPDIR, 0777 ) ;    
        chmod( ABSPATH . BACKUPDIR, 0755);   
    }
}

function testament_deactivation() {
 
    global $wpdb ; 
    
    $tbl = $wpdb->prefix.'testament_history' ;
    $sql = "DROP TABLE IF EXISTS $tbl" ;
    $wpdb->query($sql) ;   
  
    $tbl = $wpdb->prefix.'testament_conf' ;
    $sql = "DROP TABLE IF EXISTS $tbl" ;
    $wpdb->query($sql) ;  
  
  /*******************************************************/              
    $run = "mysql -u" . DB_USER ." -p" . DB_PASSWORD . " -e 'DROP DATABASE TESTAMENT_DB' " ;
    $out = shell_exec( $run ) ;
    
    removeDir( ABSPATH . BACKUPDIR ) ;
}

function testament_uninstall() {

    removeDir( ABSPATH . BACKUPDIR ) ; 
    if ( __FILE__ != WP_UNINSTALL_PLUGIN ) return ; 
}
//############################################################## HOOKS

function testament_admin_menu() {
    add_menu_page( '☂ Testament', 'Testament', 8,  'testament',  'testament_editor', plugins_url( 'testament/img/zont.png' ) ) ;
/*    
                                1. Текст, который будет использован в теге <title> на странице, относящейся к пункту меню. В капшине страницы
                                2. Название пункта меню в сайдбаре админ-панели.
                                3. 8 - Права пользователя
                                4. Уникальное название пункта меню ( будет в url адресе )
                                5. Функция которая отработает при нажатии в меню ( testament_editor )
                                6. Иконка в меню
                            */
}

function testament_editor() {

    $err = read_conf() ;
 //   echo "<br>---"  . $dbtables .  "---<br>" .     "<br>" . YANDEX_PASS . "<br>" . YANDEX_DIR . "<br>"   ; 
/************************************************************************/  
 
            switch ( $_GET['c'] ) {
            
                    case 'view' :
                        $action = 'view' ;
                        break ;
                    case 'create' :
                        $action = 'create' ;
                        break ;
                    case 'delete' :
                        $action = 'delete' ;
                        break ;
                    case 'restore' :
                        $action = 'restore' ;
                        break ;
                    case 'config' :
                        $action = 'config_choice' ;   
                        break ;

                    default :
                        $action = 'view' ;            
                        break ;           
            } 
            
            include_once( "$action.php") ;
}