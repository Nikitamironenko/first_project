<?php

/**
 * Class yd - Класс работы с €ндекс-диском
 */
class yd
{

    const url = 'https://webdav.yandex.ru';
    //const url = 'https://webdav.cloud.mail.ru' //дл€ Mail.Ru
    var $auth;
    var $ch;
    var $ansver, $info;
    var $headers;

    function __construct($user, $pass)
    {
        $this->auth="Authorization: Basic " . base64_encode($user . ":" . $pass);
    }

    private function start($url,$request=null)
    {
        $this->headers = array($this->auth);
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_URL, self::url . str_replace(' ','%20',$url));
        if($request)curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $request);
    }

    private function exec()
    {
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        $this->ansver = curl_exec($this->ch);
        $this->info = curl_getinfo($this->ch);
        curl_close($this->ch);
    }

    /** получить файл http://api.yandex.ru/disk/doc/dg/reference/get.xml
     * @param string $url путь на яндекс-диске.
     * @return bool
     */
    function get($url)
    {
        $this->start($url);
        $this->exec();
        if ($this->info['http_code'] != '200') return FALSE; // Error!
        return $this->ansver;

    }

    /** удалить файл
     * @param string $url путь на яндекс-диске.
     * @return bool
     */
    function delete($url)
    {
        $this->start($url, 'DELETE');
        $this->exec();
        if ($this->info['http_code'] != '204') return FALSE; // Error!
        return TRUE;
    }

    /** отправить файл
     * @param string $file файл с путем дл€ загрузки на яндекс-диске.
     * @param string $url путь на яндекс-диске. ≈сли не задан, то кладу в корень с тем же именем
     * @return bool
     */
    function put($file, $url='')
    {
        if(empty($url))$url=basename($file);
        $this->start($url, 'PUT');
        // ѕоддерживаетс€ закачка сжатых файлов (заголовок Content-Encoding: gzip) и закачка без объ€влени€ размера файла (Transfer-Encoding: chunked).
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, file_get_contents($file));
        $this->exec();
        if ($this->info['http_code'] != '201') return FALSE; // Error!
        // ≈сли места не хватает, сервис возвращает ответ с кодом 507 Insufficient Storage.

        return TRUE;
    }

    /** получить список файлов в директории
     * @param string $dir
     * @return array|bool
     */
    function dir($dir = '')
    {
        $this->start($dir,'PROPFIND');
        $this->headers[] = 'Depth: 1';
        $this->exec();

        if ($this->info['http_code'] != '207') return FALSE; // Error!
/*<?xml version="1.0" encoding="utf-8"?>
    <d:multistatus xmlns:d="DAV:">
        <d:response>
            <d:href>/backup/pngcrush.zip</d:href>
            <d:propstat>
                <d:status>HTTP/1.1 200 OK</d:status>
                <d:prop>
                    <d:resourcetype/>
                    <d:getlastmodified>Fri, 26 Sep 2014 21:26:39 GMT</d:getlastmodified>
                    <d:getetag>6af2c93801e3ca8a3eb7e00a9763945b</d:getetag>
                    <d:getcontenttype>application/x-zip-compressed</d:getcontenttype>
                    <d:getcontentlength>139608</d:getcontentlength>
                    <d:displayname>pngcrush.zip</d:displayname>
                    <d:creationdate>2014-09-26T21:26:39Z</d:creationdate>
                </d:prop>
            </d:propstat>
        </d:response>
    </d:multistatus>

<?xml version="1.0" encoding="utf-8"?>
<d:multistatus xmlns:d="DAV:">
<d:response>
<d:href>/web.old/</d:href>
<d:propstat>
	<d:status>HTTP/1.1 200 OK</d:status>
	<d:prop>
		<d:resourcetype>
			<d:collection/>
		</d:resourcetype>
		<d:getlastmodified>Sat, 02 Aug 2014 09:25:33 GMT</d:getlastmodified>
		<d:displayname>web.old</d:displayname>
		<d:creationdate>2014-08-02T09:25:33Z</d:creationdate>
	</d:prop>
</d:propstat></d:response>
*/
//var_dump($this->ansver);

        $res=array();
        foreach(self::xml_decode($this->ansver) as $v){
            $res[]=array_merge(array('href'=>$v["response"]['href']),$v["response"]["propstat"]["prop"]);
        }
        return $res;
    }


    /** создать папку
     * @param $dir
     * @return bool
     */
    function mkdir($dir)
    {
        $this->start($dir, 'MKCOL');
        $this->exec();
        if ($this->info['http_code'] != '201') return FALSE; // Error!
        return TRUE;
    }


    /** получить размер и зан€тое пространство
     * @return array|bool -  ["quota-used-bytes"], ["quota-available-bytes"]
     */
    function size(){
        $this->start('','PROPFIND');
        $this->headers[] = 'Depth: 0';
$xml=<<<XML
<D:propfind xmlns:D="DAV:">
  <D:prop>
    <D:quota-available-bytes/>
    <D:quota-used-bytes/>
  </D:prop>
</D:propfind>
XML;
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $xml);
        $this->exec();

        if ($this->info['http_code'] != '207') return FALSE; // Error!

        $v=self::xml_decode($this->ansver);
        return $v["response"]["propstat"]["prop"];
    }


    /** опубликовать или скрыть файл или папку из общего доступа
     * @param string $url путь на яндекс-диске.
     * @param bool $hide - true - скрыть
     * @return string|bool -  ссылка на файл или папку
     */
    function pub($url,$hide=false){
        $this->start($url, 'PROPPATCH');
        if($hide)
            $xml=<<<XML
<propertyupdate xmlns="DAV:">
  <remove>
    <prop>
      <public_url xmlns="urn:yandex:disk:meta" />
    </prop>
  </remove>
</propertyupdate>
XML;
        else
        $xml=<<<XML
<propertyupdate xmlns="DAV:">
  <set>
    <prop>
      <public_url xmlns="urn:yandex:disk:meta">true</public_url>
    </prop>
  </set>
</propertyupdate>
XML;

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $xml);
        $this->exec();

        if ($this->info['http_code'] != '207') return FALSE; // Error!

        $v=self::xml_decode($this->ansver);
        return ($hide ? ($v["response"]["propstat"]["prop"]["public_url"]=='') : $v["response"]["propstat"]["prop"]["public_url"] );
    }


    /** ѕроверить опубликован файл или папка дл€ общего доступа
     * @param string $url путь на яндекс-диске.
     * @return string|bool -  ссылка на файл/папку если опубликован или пуста€ строка
     */
    function is_pub($url){
        $this->start($url, 'PROPFIND');
        $this->headers[] = 'Depth: 0';
            $xml=<<<XML
<propfind xmlns="DAV:">
  <prop>
    <public_url xmlns="urn:yandex:disk:meta"/>
  </prop>
</propfind>
XML;
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $xml);
        $this->exec();

        if ($this->info['http_code'] != '207') return FALSE; // Error!

        $v=self::xml_decode($this->ansver);
        return $v["response"]["propstat"]["prop"]["public_url"];
    }

    /** раскодировать XML в массив
     * @param $xml
     * @return array|null
     */
    static function xml_decode($xml){
        $dom = new DOMDocument('1.0', 'utf-8');
        @$dom->loadXML($xml);
        if(!$dom)return null;//die('‘айл XML испорчен!');

//echo "\n<br><br>1:".htmlspecialchars(var_export($dom,!0),false,'windows-1251');

        $root=$dom->documentElement;
//echo "\n<br><br>2:"; var_dump($root);
        $nodelist=$root->childNodes;    //список узлов 1-го уровн€

//echo "\n<br><br>".htmlspecialchars(var_export($nodelist,!0),false,'windows-1251');


        return self::dom2array($nodelist); // этот вариант вернет в виде массива
    }

    static function dom2array($nodelist){
        $resArr=array();
        $res=array();
        foreach ($nodelist as $child){
//echo "\n<br><br>3:"; var_dump($child);
            if ($child->nodeType==XML_ELEMENT_NODE){
                if($child->firstChild){
                    $t=self::dom2array($child->childNodes);
                }else{
                    $t=$child->nodeValue;
                }
		if(isset($res[$child->localName])){ // если несколько узлов с одинаковым именем, делаю массив
		    $resArr[]=$res;
		    $res=array();
		}
		$res[$child->localName]=$t;
            }elseif ($child->nodeType==XML_ATTRIBUTE_NODE){
                $res['@'.$child->nodeName]=$child->nodeValue;
                echo "<br>@".$child->nodeName.'='.htmlspecialchars($child->nodeValue,false,'windows-1251');
            }elseif ($child->nodeType==XML_TEXT_NODE){
                $res=$child->nodeValue;
            }else{ // сюда никогда не должна попасть!
		echo "\n<br><br>child->nodeType=".$child->nodeType;
	    }
        }
	if($resArr){
		if($res)$resArr[]=$res;
		$res=$resArr;
	}
        return $res;
    }

}
