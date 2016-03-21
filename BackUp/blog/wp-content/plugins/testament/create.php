<?php
/*
Oleg Mironenko 
2016-02-20
*/
require_once( TESTAMENT_PLUGIN_DIR . "inc/testament.inc" ) ;
global $wpdb ; 

if ( isset($_POST['comment'] ) ) { 

            $comment = trim( $_POST['comment'] );           
            date_default_timezone_set( 'Europe/Moscow' );
            $date_arch = date( "Y-m-d_H-i-s" )  ;
            $arch_name_db = $date_arch . "_DB.sql" ;
            $tempDir = ABSPATH . TMPDIR ;
            $arch_path_name_db = $tempDir . $arch_name_db ;
            $arch_name_wp = $date_arch . "_WP.tgz" ;
            $wpBackUpDir = ABSPATH . BACKUPDIR  ;
            $arch_path_name_wp = $wpBackUpDir . $arch_name_wp ;
   
/********************************************************   Insert DB  *****************************************************************************/  

            $tbl = $wpdb->prefix.'testament_history' ;
            $sql = "INSERT INTO  $tbl  ( name,  comment, yandex ) VALUES ( '%s' , '%s' , '%s' ) " ;
            $run = $wpdb->prepare( $sql, $arch_name_wp, $comment, $_POST[ 'checkbox_ya' ] ) ;
            $out = $wpdb->query( $run ) ;      
            
            // Устанавливаем Активный BackUp
            $tbl = $wpdb->prefix.'testament_conf' ;
            $wpdb->update( 
                        $tbl,   // таблица
                        array( // меняем 
                                "activBackup"   => $arch_name_wp 
                        ),  
                        array( // где  
                                "db_user" => DB_USER
                        )
            );
            
/****************************************************************************************************************************************************/     
            if ( !is_dir( $tempDir ))  { mkdir( $tempDir, 0755 ) ; }  //  chmod( $tempDir, 0755) ;
            
                                                /*  BackUp SQL */
            // mysqldump -uroot -p1--ignore-table=wordpress.wp_testament_conf --add-drop-database -B wordpress --result-file=/var/www/html/blog/Tmp_Db/2016-02-19_01-53-22_DB.sql 
            // $run = "mysqldump -u" . DB_USER ." -p" . DB_PASSWORD . " --ignore-table=" . DB_NAME . "." . $tbl . " --add-drop-database -B " . DB_NAME . "  --result-file=" . $arch_path_name_db ;
            
            $run = "mysqldump -u" . DB_USER ." -p" . DB_PASSWORD . " --add-drop-database -B " . DB_NAME . "  --result-file=" . $arch_path_name_db ;
            $out = shell_exec( $run ) ;
         
                                                /*  BackUp ALL */
            $mainDir = getcwd() ; // Запомнить текущий каталог
            chdir( DIRHTML );
            $run = "tar -zcf " . $arch_path_name_wp .  " --exclude='BackUp'  " . SITENAME ;
            $out = shell_exec( $run ) ;
            chdir( $mainDir ) ;     // Возвращаем текущий каталог
            removeDir( $tempDir );
            
/* ?????????????????????????????????                  Rsync  */
            $run = "rsync -auhv  /var/www/html/blog/BackUp/ /media/DB/BackUp/Test/" ;
            $out = shell_exec( $run ) ;      
           // Удалить старше 11 дней (-mtime +11)
           //   $run = "find /media/DB/BackUp/Test/ -type f -mtime +11 -exec rm -f {}" ;
           //  $out = shell_exec( $run ) ;      
?>                       
            <html>
            <body> <br><br><br><br><br>  
                <form action="<?=$_SERVER[ 'PHP_SELF' ]?>?page=testament&c=view"  method="post">
                    <table  border='1' bordercolor='#ccc'  align='center' cellspacing='1' style= 'border-collapse:collapse' >
                        <tbody>
                            <tr style="background-color: #EFF7E0"><td style='text-align: center;'><h1>Testament BackUp Log</h1></td></tr>
                            <tr style="background-color: #EFF7E0" ><td>☂ Created local archive [<?=$arch_name_wp?>]  [<?=$comment ?> ]</td></tr> 
<?php 

//***********************************  Yandex.Disk *********************************
/*                             
                            1. $ya_login
                            2. $ya_pass
                            3. $arch_name_wp - Имя архива
                            4. $ya_dir - Директория на yandex.disk куда писать
*/
            if ( isset( $_POST[ 'checkbox_ya' ] ) ) {
  
                $tbl = $wpdb->prefix.'testament_conf' ;
                $sql  = "SELECT * FROM $tbl" ; //                                                                              ?????????
                $run = $wpdb->prepare( $sql ) ;
                $out = $wpdb->get_row( $run, ARRAY_A ) ; 
                
                if ( $out[ 'ya_login' ]  and $out[ 'ya_pass' ] ) {
                
                    $disk = new yd( YANDEX_LOGIN, YANDEX_PASS ) ; // Что бы проверить YANDEX_LOGIN и YANDEX_PASS правильны
                    if ( $size = $disk->size() ) {
                        $run = "nohup php  " . TESTAMENT_PLUGIN_DIR . "yandex_upload.php " . YANDEX_LOGIN . " " . YANDEX_PASS . " " . $arch_name_wp . " " . YANDEX_DIR 
                                . " > script.log 2>&1 &" ;
                        $out = shell_exec( $run ) ;     
?> 
                        <tr style="background-color: #EFF7E0"><td>&nbsp;↻ Yandex.Disk Backup started as a separate task</td></tr> 
                        <tr style="background-color: #EFF7E0">
                                <td>&nbsp;☛ Yandex.Disk Used:<?= convert_to_MB_GB( $size["quota-used-bytes"] )?> / Free:<?= convert_to_MB_GB( $size["quota-available-bytes"] )?></td>                                   
                        </tr> 
<?php                     
                    } else // $out[ 'ya_login' ]  YANDEX_LOGIN и YANDEX_PASS правильны
                        { echo '<P style="text-align: center;"><font color="red">*** Yandex.Disk ERROR *** <br>You are not authorized.<br>Error login or password.</font></P>' ; }                
                } // $out[ 'ya_login' ] 
                else  {   echo  '<P style="text-align: center;"><font color="red">*** Yandex.Disk ERROR *** <br>Config->Not login or password.</font></P>'; }
            } // $_POST[ 'checkbox_ya' ]
?>            
                        <tr style="background-color: #EFF7E0" ><td style='text-align: right;'><input type="submit"  name="submit" value="Return"></td></tr>
                    </tbody>
                </table>
            </form>
        </body>
    </html>    
    
<?php               
            
} // $_POST['comment']
else  {

?>
    <html>
    <body> <br><br><br><br><br>
        <form action="#"  method="post">
            <table  border='1' bordercolor='#ccc'  align='center' cellspacing='1' style= 'border-collapse:collapse'>
                <tbody>
                    <tr style="background-color: #EFF7E0"><td colspan='3'  style='text-align: center;'><h1>Enter comments for archive</h1></td></tr>
                    <tr style="background-color: #EFF7E0" >
                            <td colspan='3' style='text-align: center;' ><input  name="checkbox_ya" type="checkbox" value="y" />Copy archive on Yandex.Disk?</td>
                    </tr> 
                    <tr style="background-color: #EFF7E0" >
                            <td style="text-align: right;">Comments:</td>
                            <td><input type="text"    name="comment"></td>
                            <td><input type="submit"  name="submit" value="Save"></td>
                    </tr> 
                </tbody>
            </table>
        </form>
    </body>
    </html>  
<?php

} // $_POST['comment']




