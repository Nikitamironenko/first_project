<?php
$login = "ole@yandex.ru"; //можно и без @yandex.ru
$password = "Ua3";

$disk = new yd($login , $password);

echo "<br>Создать папку в хранилище";
if ($disk->mkdir('/dir')){
    echo '<P>OK</P>';
}else{
    echo '<P><font color="red">ERROR</font></P>';
}


echo "<br>Закачать файл в хранилище";
if ($disk->put($_SERVER['DOCUMENT_ROOT'] . '/download/pngcrush.zip', '/backup/pngcrush.zip')){
	 echo '<P>OK</P>';
}else{
	echo '<P><font color="red">ERROR</font></P>';
}


echo "<br>получить список файлов в указанной директории";
if ($ls = $disk->dir('/backup/')){
    echo '<P>OK</P>'; var_dump($ls);
    /*{ [0]=> {
        ["href"]=> "/backup/pngcrush.zip" // исходное имя файла c путем от корня
        ["resourcetype"]=> ""
        ["getlastmodified"]=> "Fri, 26 Sep 2014 21:26:39 GMT"
        ["getetag"]=> "6af2c93801e3ca8a3eb7e00a9763945b"
        ["getcontenttype"]=> "application/x-zip-compressed" // mime/type файла, к примеру, может быть image/gif
        ["getcontentlength"]=> "139608" // размер файла в байтах
        ["displayname"]=> "pngcrush.zip"
        ["creationdate"]=> "2014-09-26T21:26:39Z" }
    }*/
    //echo "\n<br><br>".htmlspecialchars($disk->ansver,false,'windows-1251');
} else {
    echo '<P><font color="red">ERROR</font></P>'; echo "\n<br>".htmlspecialchars($disk->ansver,false,'windows-1251');
}

echo "<br>Предоставить публичный доступ к файлу ".$ls[0]['href'];
if ($u = $disk->pub($ls[0]['href'])){
    echo '<P>OK</P>'; var_dump($u);
} else {
    echo '<P><font color="red">ERROR</font></P>'; var_dump($disk->info); echo "\n<br>".htmlspecialchars($disk->ansver,false,'windows-1251');
    exit;
}

echo "<br>Проверить публичный доступ к файлу ".$ls[0]['href'];
if ($u = $disk->is_pub($ls[0]['href'])){
    echo '<P>OK</P>'; var_dump($u);
} else {
    echo '<P><font color="red">ERROR</font></P>'; var_dump($disk->info); echo "\n<br>".htmlspecialchars($disk->ansver,false,'windows-1251');
}

echo "<br>Закрыть публичный доступ к файлу ".$ls[0]['href'];
if ($disk->pub($ls[0]['href'],!0)===true){
    echo '<P>OK</P>';
} else {
    echo '<P><font color="red">ERROR</font></P>'; var_dump($disk->info);echo "\n<br>".htmlspecialchars($disk->ansver,false,'windows-1251');
}


echo "<br>Размер диска";
if ($size = $disk->size()){
    echo '<P>OK</P>';
    echo "<br>Занято: ".floor($size["quota-used-bytes"]/1024).'Кб, свободно: '. floor($size["quota-available-bytes"]/1024)."Кб";
} else {
    echo '<P><font color="red">ERROR</font></P>'; echo "\n<br>".htmlspecialchars($disk->ansver,false,'windows-1251');
}



// скачать файл из хранилища
if ($data = $disk->get('/backup/pngcrush.zip')) {
	echo '<P>OK</P>';
	file_put_contents('~test.zip',$data);
} else {
	echo '<P><font color="red">ERROR</font></P>';
}

echo "<br>Удалить файл или папку";
if ($disk->delete('/backup/pngcrush.zip')){
	echo '<P>OK</P>';
}else{
	echo '<P><font color="red">ERROR</font></P>';
}

