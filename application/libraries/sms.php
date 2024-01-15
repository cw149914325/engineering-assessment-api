<?php
class Sms_Library
{
        private static $obj;
        private static $client;
        function __construct() 
        {
           
	}
        
        static function getInstance()
        {  
            if(!self::$obj)
            {
                self::$obj = new Sms_Library();   
            }
            return self::$obj;
        }

        
/**
 *给指定手机号码发送信息
 * @param type $mobileno
 * @param type $msg 
 */
public static function sendmsg($mobileno,$msg)
 {
    $msg=$msg." 【上海大饭】";
     $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,"https://sms-api.luosimao.com/v1/send.json");
    curl_setopt($ch,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
    curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,30);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,true);
    curl_setopt($ch,CURLOPT_SSLVERSION,3);
    curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
    curl_setopt($ch,CURLOPT_USERPWD,'api:key-47601130292c037a0c64044c2ba48b13');
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,array('mobile' => $mobileno,'message' => $msg));
    $res=curl_exec($ch);
    curl_close( $ch );
    return $res;
 }




    public static function sendcodemsg($mobile,$code)
    {
        $host = "http://dingxin.market.alicloudapi.com";
        $path = "/dx/sendSms";
        $method = "POST";
        $appcode = "84b7207f95a04f31b0decd86f02c9b49";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "mobile=$mobile&param=code:".$code."&tpl_id=TP1804037";
        $bodys = "";
        $url = $host . $path . "?" . $querys;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $res=curl_exec($curl);
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == '200') {
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $header = substr($res, 0, $headerSize);
            $bodys = substr($res, $headerSize);
        }
//        var_dump($bodys);
        curl_close( $curl );

        return $bodys;
    }

       
}


