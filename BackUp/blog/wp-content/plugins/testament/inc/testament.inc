<?php
/*
Oleg Mironenko 
2016-02-17

chgrp  -R www-data /var/www/html/blog && chown -R www-data:www-data /var/www/html/blog && chmod -R 775 /var/www/html/blog
========================================================================================================================
/*  Цвета
http://flatcolors.net/palettes
http://flatcolors.net/palette/373-flat-on-your-head#
------------------------------------------------------------------
    home_url()                              http://localhost/blog
    ABSPATH                                   /var/www/html/blog/
    dirname( ABSPATH )                  /var/www/html
    $_SERVER['DOCUMENT_ROOT']   /var/www/html
    DIRHTML                                   /var/www/html
    
    DB_NAME
    DB_USER
    DB_PASSWORD

    show_admin_bar(false);

    wp_parse_str()
    wp_mkdir_p()
    get_dirsize() 
    
    home_url() 
    print_r( $out ) ;

Только три простых действия.
создать восстановить удалить

Only three simple actions.
☂ Creat ↻ Restory ✕ Delete

Cron
http://webformyself.com/wpmanual/files/wp_schedule_event.html

******************************* define *******************************************
define( 'DIRHTML', dirname( ABSPATH )  ) ;
define( 'SITENAME', siteName()  ) ;
define( 'YANDEX_LOGIN', $out[ 'ya_login' ]  ) ;
define( 'YANDEX_PASS',    $out[ 'ya_pass' ]  ) ;
define( 'YANDEX_DIR',    $out[ 'ya_dir' ]  ) ;
define( 'DELNDAYS',    $out[ 'delNdays' ]  ) ;
    
******************************* const *******************************************    
const BACKUPDIR =  'BackUp/'  ;
const TMPDIR =  'Tmp_Db/' ;

*/

function deleteOldFiles( $dir, $days ) {
//    $folderName = '/home/site.com/domains/catalog/photo';
  $days = '3';
    if (file_exists($dir)) {
        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if ($fileInfo->isDot()) {
            continue;
            }
            if (time() - $fileInfo->getCTime() >= $days*24*60*60) {
                unlink($fileInfo->getRealPath());
            }
        }
    }
}

function deleteOldFiles1( $dir, $days ) {

 //   $dir="./ZZZZ";  //читаем эту директорию
 //   $todel=300; // время на удаление
    if ( $OpenDir = opendir( $dir ) ) {
        while( ( $file = readdir( $OpenDir ) ) !== false ) {
            if ( $file != "." && $file != ".." ) {
                $dtime = intval( time() - filectime( "{$dir}/{$file}" ) );
                echo $dtime ;
                if ( $dtime >= $days *24*60*60 ) unlink( "{$dir}/{$file}" ) ;
            }
        }
        closedir( $OpenDir ) ; 
    } 
}

function siteName() {
    $site_name = substr( ABSPATH, 0, -1 );
    $site_name = substr(strrchr( $site_name, "/" ), 1 );
    return $site_name ;
}
/////////////////////////////////////////////////////////////////////////////////
const BACKUPDIR =  'BackUp/'  ;
const TMPDIR =  'Tmp_Db/' ;

define( 'DIRHTML', dirname( ABSPATH )  ) ;
define( 'SITENAME', siteName()  ) ;

/*                             *** Yandex.Disk ***                                 */
require_once( "yd.inc" ) ;

/////////////////////////////////////////////////////////////////////////////////

function read_conf() {

global $wpdb ;   

    $tbl = $wpdb->prefix.'testament_conf' ;
    $sql = "SELECT *  FROM $tbl " ; 
    $out = $wpdb->get_row( $sql, ARRAY_A ) ;

    define( 'YANDEX_LOGIN',  $out[ 'ya_login' ]  ) ;
    define( 'YANDEX_PASS',    $out[ 'ya_pass' ]  ) ;
    define( 'YANDEX_DIR',      $out[ 'ya_dir' ]  ) ;
    define( 'DELNDAYS',         $out[ 'delNdays' ]  ) ;
    define( 'SAVENFILES',       $out[ 'saveNfails' ]  ) ;
    define( 'ACTIV_BACKUP',  $out[ 'activBackup' ]  ) ;
}

function convert_to_MB_GB(  $bytes  ) {
    $si_prefix = array( 'B', 'Kb', 'Mb', 'Gb', 'Tb', 'Eb' ) ;
    $base = 1024;
    $class = min((int)log( $bytes , $base) , count($si_prefix) - 1 ) ;
    $out = sprintf( ' %1.2f' , $bytes / pow( $base,$class )) . ' ' . $si_prefix[$class] ;
    return $out ;
}

function diskFree() {
    $bytes = disk_free_space( "." ) ;
    $si_prefix = array(  'B', 'Kb', 'Mb', 'Gb', 'Tb', 'Eb'  ) ;
    $base = 1024;
    $class = min( (int)log( $bytes , $base ) , count($si_prefix) - 1 ) ;
    $out =  sprintf( '%1.2f' , $bytes / pow( $base,$class )) . ' ' . $si_prefix[$class] . '<br>' ;
    return $out ;
}

function removeDir( $dir ) {
    if ($objs = glob($dir."/*")) {
        foreach( $objs as $obj ) {
            is_dir( $obj ) ? removeDir( $obj ) : unlink( $obj );
        }
    }
    rmdir( $dir );
}
