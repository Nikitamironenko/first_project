<?php
/*
Oleg Mironenko 
2016-02-17
*/
    require_once( TESTAMENT_PLUGIN_DIR . "inc/testament.inc" ) ;
    
    global $wpdb ;    
    
    if  (isset( $_POST['ya_login']  ) and isset( $_POST['ya_pass']  ) )   {                           //если все переменные  эаполнены вводим в DB
 
        $ya_login   = trim( $_POST['ya_login'] ) ;
        $ya_pass    = trim( $_POST['ya_pass']  ) ; 
        $ya_dir      = trim( $_POST['ya_dir'] ) ;  
        $delNdays  = trim( $_POST['delNdays'] ) ;  
        $saveNfails= trim( $_POST['saveNfails'] ) ;  
//        $activBackup= trim( $_POST['activBackup'] ) ;  
        
        $wpdb->update( 
                                $tbl,   // таблица
                                array( // меняем 
                                        "ya_login"   => $ya_login, 
                                        "ya_pass"    => $ya_pass, 
                                        "ya_dir"      => $ya_dir, 
                                        "delNdays"  => $delNdays, 
                                        "saveNfails" => $saveNfails 
                                ),  
                                array( // где  
                                         "db_user" => DB_USER
                                )
        );
 /*     
        $sql = "INSERT INTO  $tbl   ( ya_login,  ya_pass ,   ya_dir,   delNdays, saveNfails ) 
                                    VALUES (  '%s' ,           '%s' ,           '%s' ,        '%s'  ,      '%s'        ) " ;        
        $run = $wpdb->prepare( $sql, $ya_login, $ya_pass , $ya_dir, $delNdays, $saveNfails ) ;
        $out = $wpdb->query( $run ) ;
        if ( $out === false )  die( "Error insert DB" ) ;
        
*/        
        ?>       
                <br><br><br><br><br>
                <ul>
                    <li style="text-align: center;"><a href="<?=$_SERVER[ 'PHP_SELF' ]?>?page=testament&c=view"> Configuration is saved. Return...</a></li>
                </ul>          
        <?php                      
    }
    else  {                   //если    данные еще не введены   
    
                $tbl = $wpdb->prefix.'testament_conf' ;
                $sql  = "SELECT * FROM $tbl " ;
                $run = $wpdb->prepare( $sql ) ;
                $out = $wpdb->get_row( $run, ARRAY_A ) ;  

                ?>               
                <html>
                    <body> <br><br><br><br><br>
                                <form action="#"  method="post">
                                        <table  border='1' bordercolor='#ccc'  align='center' cellspacing='1' style= 'border-collapse:collapse' >
                                                        <caption><h1>Configuration Testament</h1></caption>
                                            <tbody>
                                                <tr style="background-color: #EFF7E0">
                                                    <td style="text-align: right;">Yandex.Disk Login:</td>
                                                    <td><input type="text"  name="ya_login" value=<?=$out[ 'ya_login' ]?>>&nbsp;</td>
                                                    <td><span style="color:#A9A9A9">mylog@yandex.ru</td>
                                                </tr>
                                                <tr style="background-color: #EFF7E0">
                                                    <td style="text-align: right;">Yandex.Disk password:</td> 
                                                    <td><input type="password"  name="ya_pass" value=<?=$out[ 'ya_pass' ]?>>&nbsp;</td>
                                                    <td><span style="color:#A9A9A9">pass</td>
                                                </tr>
                                                <tr style="background-color: #EFF7E0">
                                                    <td style="text-align: right;">Yandex.Disk dir:</td>
                                                    <td><input type="text" name="ya_dir" value=<?=$out[ 'ya_dir' ]?>>&nbsp;</td>  
                                                    <td><span style="color:#A9A9A9">myArch or empty then in root</td>
                                                </tr>
                                                <tr style="background-color: #ffecfd">
                                                    <td style="text-align: right;">Delete old files N days:</td>
                                                    <td><input type="text" name="delNdays" value=<?=$out[ 'delNdays' ]?>>&nbsp;</td>  
                                                    <td><span style="color:#A9A9A9">7</td>
                                                </tr>
                                                <tr style="background-color: #ffecfd">
                                                    <td style="text-align: right;">Store at min N files:</td>
                                                    <td><input type="text" name="saveNfails" value=<?=$out[ 'saveNfails' ]?>>&nbsp;</td>  
                                                    <td><span style="color:#A9A9A9">5</td>
                                                </tr>
                                                <tr style="background-color: #EFF7E0">
                                                    <td colspan="3" rowspan="1" style="text-align: right;"><input type="submit"  name="submit" value="Save config">&nbsp;</td>
                                                </tr>
                                        </tbody>
                                    </table>
                                </form>
                        </body>
                </html>
    <?php 
}
