<?php
/*
Oleg Mironenko 
2016-02-17
*/
require_once( TESTAMENT_PLUGIN_DIR . "inc/testament.inc" ) ;

?> 
    <br><br><br><br>
    <table border='1' bordercolor='#ccc'  align='center' cellspacing='1' style= 'border-collapse:collapse' >
            <tr style="background-color: #EFF7E0" >
                <td style='text-align: center;'>
                    <a href="<?=$_SERVER[ 'PHP_SELF' ]?>?page=testament&c=config"><img alt=' ' src="<?=plugins_url( 'testament/img/config.png' )?>" title="Config testament" />
                        <span style='color:#077700'></span>
                    </a>
                </td>
                <td colspan='3' dir='rtl' rowspan='1' style='text-align: center;' >
                    <h1>
                        <span style='color:#000000'>Testament BackUp</span>
                    </h1>
                </td>
                <td colspan='2' rowspan='1' style='text-align: center;'> 
                    <a href="<?=$_SERVER[ 'PHP_SELF' ]?>?page=testament&c=create"><img alt=' ' src="<?=plugins_url( 'testament/img/zont.png' )?>" title="Create archiv"/>
                        <span style='color:#077700'></span>
                    </a>
                </td>
            </tr>
            <tr style="background-color: #EFF7E0" >  
                <td colspan='2' rowspan='1' style='text-align: right;'><span style='color:#000000'>Total Disk Space</span></td>
                <td><span style='color:#0000CD'><?=diskFree() ?></span></td>   
                <td style='text-align: center;'>Сomments</td>
                <td colspan='2' rowspan='1' ><span style='color:#0000CD'></span></td>        
            </tr>
 <?php
  
    date_default_timezone_set( 'Europe/Moscow' ) ;
    $wpBackUpDir = ABSPATH . BACKUPDIR ;
    $mainDir = getcwd() ;                   // Запомнить текущий каталог
    chdir( $wpBackUpDir ) ;                // Меняем текущий каталог
    $i=1 ;
    $dirBackUpSize = 0 ;                     // Размер директории BACKUPDIR
    foreach(glob( '*' ) as $fileArch)  {
            $bytes = filesize( $wpBackUpDir . $fileArch ) ;
            $dirBackUpSize += $bytes ;
            $MB = convert_to_MB_GB( $bytes ) ; 
            
            /******** DataBase *********/

            global $wpdb ;
            $tbl = $wpdb->prefix.'testament_history' ;
            $sql  = "SELECT * FROM $tbl   WHERE name = '%s'  " ;
            $run = $wpdb->prepare( $sql,  $fileArch ) ;
            $out = $wpdb->get_row( $run, ARRAY_A ) ;         
            
            $out['comment'] = ( $out['comment'] ) ? $out['comment'] : '☛ It is possible restore ☚' ; 
            // Если копия на Yandex.disk изменить цвет
            if (  $out['yandex'] == "y" )  
            { ?> <tr style="background-color: #def1c8" > <?php  } // Розовый #FFE0E5 / #F5EDED
            else {  ?><tr style="background-color: #EFF7E0" ><?php }
   
            if ( $fileArch == ACTIV_BACKUP ) { 
                    ?><td dir='rtl' >&nbsp;&nbsp;<?=$i?>➜&nbsp;&nbsp;</td><?php  
                }
            else { 
                    ?><td dir='rtl' >&nbsp;&nbsp;<?=$i?>&nbsp;&nbsp;</td><?php 
            } 
            ?>
                    <td>&nbsp;&nbsp;&nbsp;<?=$fileArch?>&nbsp;&nbsp;&nbsp;</td>
                    <td><?=$MB?>&nbsp;&nbsp;&nbsp;</td>
                    <td><?=$out['comment']?></td>
                    <td><span style='color:#FF0000'>
                        <a href="<?=$_SERVER[ 'PHP_SELF' ]?>?page=testament&c=delete&id=<?=$fileArch?>">
                            <img alt=' ' src="<?=plugins_url( 'testament/img/delete.png' )?>"  title="Delete archiv"/>
                        </a></span>
                    </td> 
                    <td><span style='color:#FF0000'>
                        <a href="<?=$_SERVER[ 'PHP_SELF' ]?>?page=testament&c=restore&id=<?=$fileArch?>">
                            <img alt=' ' src="<?=plugins_url( 'testament/img/restore.png' )?>"  title="Restore archiv"/>
                        </a></span>
                    </td>  
            </tr>
<?php
            $i++ ;
    }   // foreach
?>
            <tr style="background-color: #EFF7E0" >
                    <td colspan='2' dir='rtl' rowspan='1' ><span style='color:#000000'>Total Archiv Space</td>
                    <td> <span style='color:#0000CD'><?=convert_to_MB_GB( $dirBackUpSize ) ?></span></td> 
                    <td colspan='3' rowspan='1' > <span style='color:#0000CD'></span></td>     
            </tr>
        </tbody></table> 
<?php
    chdir( $mainDir ) ; // Возвращаем текущий каталог
