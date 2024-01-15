<?php

/**
 * 
 *
 */
class Main_Frontend_Controller extends Controller
{

    private $model;

    public function __construct()
    {
        parent::__construct();

    }


    public function actionlist()
    {
//        $userinfo=["user_type"=>1];
        $page =$this->getParam("pageIndex",1);
        $size =$this->getParam("size",10);
        $FacilityType =$this->getParam("FacilityType","");
        $list =Mobilefoodfacilitypermit_Model::getList($FacilityType,$page,$size);
        $count =Mobilefoodfacilitypermit_Model::getCount();
        $result =[];
        $result["list"] =$list;
        $result["count"] =$count;


        $this->outputJson(0,$result);

    }


    public function actioninfo()
    {
//        $userinfo=["user_type"=>1];
        $locationid =$this->getParam("locationid",0);
        $info = Mobilefoodfacilitypermit_Model::getInfoByid($locationid);


        $this->outputJson(0,$info);

    }
	

}

?>
