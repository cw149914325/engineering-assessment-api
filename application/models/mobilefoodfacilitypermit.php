<?php

/**
 * 用户信息Model
 * @Table Userinfo_*
 */
class Mobilefoodfacilitypermit_Model extends Model
{

    private static $object;

    public static function getinstance()
    {
        if (!self::$object)
            self::$object = new Mobilefoodfacilitypermit_Model();
        return self::$object;
    }









    /**
     *获取单个用户信息
     * @param type $userid
     * @return type 
     */
    static public function getInfoByid($locationid,$clear=true)
    {
        $result=array();
//        $strKeyUserInfo = "getUserInfoByid" . $id;
//        $temp= Memcachedclass::get($strKeyUserInfo);
//        if ($clear || empty($temp))
//        {
            $dbconnect = Database::load();
            $sql = "select * from Mobile_Food_Facility_Permit where locationid=:locationid limit 1";
            if ($dbconnect->query($sql, array(":locationid" => $locationid)) && $dbconnect->rowcount() > 0)
            {
                $result=$dbconnect->fetch_row();
//                Memcachedclass::set($strKeyUserInfo, $result);
                
            }
  
//        }
//        else
//        {
//            $result=$temp;
//        }
            
        return $result;
        
    }
    
    
    
    
    
    
     /**
    * 添加订单信息
    */
    static public function addUserInfo($data)
    {
        $result=0;
        if(empty($data))
        {
                return false;
        }
        $dbconnect = Database::load();
        $id = $dbconnect->insertGetLastID("Mobile_Food_Facility_Permit", $data);

        if($id && $dbconnect->rowcount() > 0)
        {
                $result=$id;
        }

        return $result;
    }
    
   
    
        
          /**
        *获取商品列表
        * @param type $clear
        * @return type 
        */
        static public function getList($FacilityType="",$page=1,$size=10)
        {
            $page+=0;
            $size+=0;
            $result=array();
            if($page<1)
            {
                $page =1;
            }
            $offset=($page-1)*$size;
            $dbconnect = Database::load();
            $params=[];
            $sql = "select * from Mobile_Food_Facility_Permit  ";
            if(!empty($FacilityType))
            {
                $sql .=" where FacilityType =:FacilityType ";
                $params[":FacilityType"]=$FacilityType;
            }
            $sql .= " order by locationid desc limit $offset,$size ";
            if ($dbconnect->query($sql, $params) && $dbconnect->rowcount() > 0)
            {
                $result = $dbconnect->fetch_all();
            }

            return $result;
        }



    static public function getCount()
    {
        $result =0;
        $dbconnect = Database::load();
        $sql = "select count(1) as count from Mobile_Food_Facility_Permit  limit 1 ";
        if ($dbconnect->query($sql, array()) && $dbconnect->rowcount() > 0)
        {
            $temp = $dbconnect->fetch_row();
            if(!empty($temp))
            {
                $result =$temp["count"];
            }
        }

        return $result;
    }





    /**
     * 更新用户信息
     */
    static public function updateInfo($id, $arr)
    {
        $dbconnect = Database::load();
        $query = array();
        $set = array();
        foreach ($arr as $key => $val)
        {
            $query[":" . $key] = $val;
            $set[$key] = $key . "= :" . $key;
        }
       $exe=$dbconnect->update("Mobile_Food_Facility_Permit", implode(",", $set), "id = '{$id}'", $query);
        if ($exe)
        {
//             self::getInfoByid($id,true);

            return true;
        }
        else
        {
            return false;
        }
    }
    
    
    
    
     /**
    * 删除用户
    */
    static public function delInfo($locationid)
    {
        $result=0;
        $dbconnect = Database::load();
        $sql="delete from Mobile_Food_Facility_Permit where locationid=:locationid limit 1";

        if($dbconnect->query($sql, array(":locationid"=>$locationid)) && $dbconnect->rowcount() > 0)
        {
            $result=true;
        }

        return $result;
    }


    
    
   

}

?>
