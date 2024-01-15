<?php
/**
 * 控制器基类
 *
 * chenwei
 *
 */
class Controller
{
	//request的控制器名称
	//public $controller_name = '';
	
	//request的控制器方法
	//public $method_name		= '';
	
	//request的get参数数组
	//public $uri_hash  		= array();	
	//public $uri_array 		= array();
	 
	public function __construct()
	{
		//$this->controller_name = Registry::get('controller_name');
		//$this->method_name = Registry::get('method_name');
		//$this->uri_hash = Registry::get('uri_hash');
		//$this->uri_array = Registry::get('uri_array');
	}
	
	/**
	 * 加载视图模板
	 * 
	 * @param string $template_file 模板文件
	 * @param array $value_array 传到视图的数据
	 * @param string $layout 是否使用层，及层的名字
	 * @param bool $output 是否输出，不输出则返回模板解析后的 html
	 */
	public function load_view($template_file, $value_array=array(), $layout='default', $output=true)
	{
		$view = new View();
//
//		$value_array['imgServer'] = IMGSERVER;
//                $value_array['version'] = VERSION;
		//解析模板
		return $view->render($template_file, $value_array, $layout, $output);
	}
	
        
        /**
         *获取json格式
         * @param type $code
         * @param type $info
         * @param type $msg 
         */
	protected function outputJson($code=0,$info=array(),$msg="",$params=array())
        {
            $action=  $this->getParam("action", "");
		$rs=array(
                        'action'=>$action,
			'code'=>$code,
			'info'=>$info,
			'errormsg'=>$msg,
                        'params'=>$params
		);
                
//                echo FastJSON::encode($rs);
		echo json_encode($rs);
		exit;
	}

	protected function getParam($key,$default="")
	{
		$result=$_REQUEST[$key];
		if($result==null)
		{
            $json_data = file_get_contents('php://input');
            if(!empty($json_data))
            {
                $data =json_decode($json_data,true);
                if(isset($data[$key]))
                {
                    $result =$data[$key];
                }
            }

            if($result ==null)
            {
                $result=$default;
            }

		}

		return $result;
	}


    /**
     * 检查用户是否登陆状态
     */
    protected function CheckLogin()
    {
        $token = $this->getParam("CT_token");
        if(empty($token))
        {
            $token=$_COOKIE["CT_token"];
            if(empty($token))
            {
                $token=$_SERVER["HTTP_CT_token"];
            }
        }
        
		$arrToken =explode("_",$token);
                
         
                
		if(!empty($arrToken) && is_array($arrToken) && count($arrToken)==2)
		{
			$userid=$arrToken[0];
			$authkey=$arrToken[1];
            $arrUserInfo= Userinfo_Model::getUserInfoByid($userid);

               
			$authtruekey = Userinfo_Model::MakeAuthKey($userid, $arrUserInfo["openId"]);
                       
			 if (strcmp($token, $authtruekey) == 0)
			{
				return $arrUserInfo;
			}

		}

		header("location:/?action=login");
    }
    
    
        /**
     * 检查用户是否登陆状态
     */
    protected function getLoginInfo()
    {
        $token = $this->getParam("CT_token");
        if(empty($token))
        {
            $token=$_COOKIE["CT_token"];
            if(empty($token))
            {
                $token=$_SERVER["HTTP_CT_token"];
            }
        }
        $arrToken =explode("_",$token);
        if(!empty($arrToken) && is_array($arrToken) && count($arrToken)==2)
        {
                $userid=$arrToken[0];
                $authkey=$arrToken[1];

                $arrUserInfo= Userinfo_Model::getUserInfoByid($userid);


                $authtruekey = Userinfo_Model::MakeAuthKey($userid, $arrUserInfo["openId"]);
                if (strcmp($token, $authtruekey) == 0)
                {
                        return $arrUserInfo;
                }
                else
                {
                    return array();
//				echo json_encode(array("code"=>404,"info"=>"用户未登录"));
                }
        }
        return array();
    }
    
    
    /**
     * 获取客户端版本号 111
     * @param string $type num 是获取出整型数字
     * @return string|int
     */
    protected function _getClientVersion($type = 'num') 
    {

        $version =$this->getParam("SY_version","");
        
        if(empty($version))
        {
            $version="0.0.0";
        }
        
        if($type=='num')
        {
            $arrversion=explode('.', $version);
            if(!$arrversion[2])
            {
                $arrversion[2]=0;
            }
            $version=$arrversion[0]*100+$arrversion[1]*10+$arrversion[2];
            return $version;
        }
        else
        {
            return $version;
        }
    }
    
    /**
     *获取系统类型 
     */
    protected function getSystemType($num="")
    {
        $result=0;
        $userAgent=strtolower($_SERVER["HTTP_USER_AGENT"]);
        if(strpos($userAgent, "iphone") > 0 || strpos($userAgent, "ipad")>0)
        {
            $systemType = 'iphone';
            $result=1;
//            Zend_Registry::set("systemtype", "iphone");
        } 
        else 
        {
            $systemType = 'android';
//            Zend_Registry::set("systemtype", "android");
        }
        
        if($num=="int")
        {
            return $result;
        }
        
        return $systemType;
    }
    
    
    /**
     *获取客户端agent信息
     * @return string 
     */
    protected function getUserAgent()
    {
       $result=$_SERVER['HTTP_USER_AGENT'];
       if(empty($result))
        {
           $result="";
        }
        
        return $result;
    }
    
    
    
    
      /**
     * 检查用户是否登陆状态
     */
    protected function getWeiXinToken($appid,$secret)
    {
        $code=  $this->getParam("code", "");
        if(empty($code))
        {
           $this->outputJson(1,[],"缺少参数code");
        }

        $weixinurl="https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$code&grant_type=authorization_code";
//        $weixinurl="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
        
        $userinfo=Communication_Model::b_posttoserver(array(), $weixinurl);
        return $userinfo;
        
    }
    
    
    
        /**
     * 检查用户是否登陆状态
     */
    protected function getWeiXinOpenid($appid,$secret)
    {
        $openid="";
        $userinfo=  $this->getWeiXinToken($appid, $secret);
        $userinfo=  json_decode($userinfo, true);
     
        if(!empty($userinfo) && !empty($userinfo["openid"]))
        {
            $openid=$userinfo["openid"];
        }
        return $openid;
        
    }
    
    
    

}
?>