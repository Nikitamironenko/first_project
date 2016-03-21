<?php
/*
Oleg Mironenko 
2016-02-17
*/
require_once( TESTAMENT_PLUGIN_DIR . "inc/testament.inc" ) ;
        global $wpdb ;  
        
        $id = trim( $_GET[ 'id' ] ) ;
        $fileName = ABSPATH . BACKUPDIR . $id ;
        $result = unlink(  $fileName );
        
        /* Yandex.Disk */
    
        $tbl = $wpdb->prefix.'testament_history' ;
        $sql  = "SELECT * FROM $tbl   WHERE name = '%s'  " ; //                                                                              ?????????
        $run = $wpdb->prepare( $sql,  $id ) ;
        $out = $wpdb->get_row( $run, ARRAY_A ) ;         

        if ( $out[ 'yandex' ] == 'y' ) {
                $disk = new yd( YANDEX_LOGIN, YANDEX_PASS ) ;
                if ( !$disk->delete(  '/' . YANDEX_DIR . '/' . $id ) ) {  
                    echo '<P style="text-align: center;"><font color="red">*** Yandex.Disk ERROR *** <br>No file. May be file is already removed or bed login, password.</font></P>';
                }
        } 

        $tbl = $wpdb->prefix.'testament_history' ;
        $sql = "DELETE FROM  $tbl  WHERE name = '%s' " ;
        $run = $wpdb->prepare( $sql, $id ) ;
        $out = $wpdb->query( $run ) ;
        
?>       
        <br><br><br><br><br>
        <ul>
            <li style="text-align: center;"><a href="<?=$_SERVER[ 'PHP_SELF' ]?>?page=testament&c=view"> Archiv [ <?=$id?> ] deleted. Return...</a></li>
        </ul>