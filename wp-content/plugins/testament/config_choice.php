<?php
/*
Oleg Mironenko 
2016-02-17
*/
    require_once( TESTAMENT_PLUGIN_DIR . "inc/testament.inc" ) ;

    // Читаем есть ли в Db записи о Yandex.Disk
        global $wpdb ;     
        
        $tbl = $wpdb->prefix.'testament_conf' ;
        $sql  = "SELECT * FROM $tbl " ;
        $run = $wpdb->prepare( $sql ) ;
        $out = $wpdb->get_row( $run, ARRAY_A ) ;   
        
       // есть в Db Update // нет в Db Create
        if ( $out[ 'ya_login' ]  and $out[ 'ya_pass' ] ) { include_once( "config_update.php") ;  }  
        else  { include_once( "config_create.php") ; } 
