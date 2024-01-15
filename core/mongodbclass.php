<?php     
/*** Mongodb类** examples:     
* $mongo =HMongodb::getInstance("127.0.0.1:11223");   
* $mongo->selectDb("test_db");   
* 创建索引   
* $mongo->ensureIndex("test_table", array("id"=>1), array('unique'=>true));   
* 获取表的记录   
* $mongo->count("test_table");   
* 插入记录   
* $mongo->insert("test_table", array("id"=>2, "title"=>"asdqw"));   
* 更新记录   
* $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"));   
* 更新记录-存在时更新，不存在时添加-相当于set   
* $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"),array("upsert"=>1));   
* 查找记录   
* $mongo->find("c", array("title"=>"asdqw"), array("start"=>2,"limit"=>2,"sort"=>array("id"=>1)))   
* 查找一条记录   
* $mongo->findOne("$mongo->findOne("ttt", array("id"=>1))", array("id"=>1));   
* 删除记录   
* $mongo->remove("ttt", array("title"=>"bbb"));   
* 仅删除一条记录   
* $mongo->remove("ttt", array("title"=>"bbb"), array("justOne"=>1));   
* 获取Mongo操作的错误信息   
* $mongo->getError();   
*/     
     
class Mongodbclass {     
     
    //Mongodb连接     
    static private $mongo;     
    static private $curr_db_name;     
    static private $curr_table_name;     
    static private $error;     
    static private $obj;
     
    /**   
    * 构造函数   
    * 支持传入多个mongo_server(1.一个出问题时连接其它的server 2.自动将查询均匀分发到不同server)   
    *   
    * 参数：   
    * $mongo_server:数组或字符串-array("127.0.0.1:1111", "127.0.0.1:2222")-"127.0.0.1:1111"   
    * $connect:初始化mongo对象时是否连接，默认连接   
    * $auto_balance:是否自动做负载均衡，默认是   
    *   
    * 返回值：   
    * 成功：mongo object   
    * 失败：false   
    */     
    function __construct($mongo_server, $connect=true, $auto_balance=true)     
    { 
        $arrConfig = Config::get($mongo_server);
        $mongo_server=$arrConfig["host"];
        $dbname=$arrConfig["dbname"];
        if (is_array($mongo_server))     
        {     
            $mongo_server_num = count($mongo_server);     
            if ($mongo_server_num > 1 && $auto_balance)     
            {     
                $prior_server_num = rand(1, $mongo_server_num);     
                $rand_keys = array_rand($mongo_server,$mongo_server_num);     
                $mongo_server_str = $mongo_server[$prior_server_num-1];     
                foreach ($rand_keys as $key)     
                {     
                    if ($key != $prior_server_num - 1)     
                    {     
                        $mongo_server_str .= ',' . $mongo_server[$key];     
                    }     
                }     
            }     
            else     
            {     
                $mongo_server_str = implode(',', $mongo_server);     
            }                  
            
        }     
        else     
        {     
            $mongo_server_str = $mongo_server;     
        }     
        try 
        {    
            self::$mongo = new Mongo($mongo_server_str, array('connect'=>$connect));
            $this->selectDb($dbname);
        }     
        catch (MongoConnectionException $e)     
        {     
             self::$error = $e->getMessage();     
            return false;     
        }     
    }     
     
    static function getInstance($mongo_server="local.mongdb", $flag=array())     
    {     
        if(self::$obj)
        {
            return self::$obj;
        }
        static $mongodb_arr;     
        if (empty($flag['tag']))     
        {     
            $flag['tag'] = 'default';          
            
        }     
        if (isset($flag['force']) && $flag['force'] == true)     
        {     
            self::$obj = new Mongodbclass($mongo_server);     
            if (empty($mongodb_arr[$flag['tag']]))     
            {     
                $mongodb_arr[$flag['tag']] = self::$obj;     
            }     
            return self::$obj;     
        }     
        else if (isset($mongodb_arr[$flag['tag']]) && is_resource($mongodb_arr[$flag['tag']]))     
        {     
            return $mongodb_arr[$flag['tag']];     
        }     
        else     
        {     
            self::$obj = new Mongodbclass($mongo_server);    
            $mongodb_arr[$flag['tag']] = self::$obj;     
            return self::$obj;                  
            
        }          
        
    }     
     
    /**   
    * 连接mongodb server   
    *   
    * 参数：无   
    *   
    * 返回值：   
    * 成功：true   
    * 失败：false   
    */     
    function connect()     
    {     
        try {     
            self::$mongo->connect();     
            return true;     
        }     
        catch (MongoConnectionException $e)     
        {     
            self::$error = $e->getMessage();     
            return false;     
        }     
    }     
     
    /**   
    * select db   
    *   
    * 参数：$dbname   
    *   
    * 返回值：无   
    */     
    function selectDb($dbname)     
    {     
        self::$curr_db_name = $dbname;     
    }     
     
    /**   
    * 创建索引：如索引已存在，则返回。   
    *   
    * 参数：   
    * $table_name:表名   
    * $index:索引-array("id"=>1)-在id字段建立升序索引   
    * $index_param:其它条件-是否唯一索引等   
    *   
    * 返回值：   
    * 成功：true   
    * 失败：false   
    */     
    function ensureIndex($table_name, $index, $index_param=array())     
    {     
        $dbname = self::$curr_db_name;     
        $index_param['safe'] = 1;     
        try {     
            self::$mongo->$dbname->$table_name->ensureIndex($index, $index_param);     
            return true;     
        }     
        catch (MongoCursorException $e)     
        {     
            self::$error = $e->getMessage();     
            return false;     
        }     
    }     
     
    /**   
    * 插入记录   
    *   
    * 参数：   
    * $table_name:表名   
    * $record:记录   
    *   
    * 返回值：   
    * 成功：true   
    * 失败：false   
    */     
    function insert($table_name, $record)
    {    
        $dbname = self::$curr_db_name;   
        try 
        {     
            self::$mongo->$dbname->$table_name->insert($record, array('safe'=>true));     
            return true;     
        }     
        catch (MongoCursorException $e)     
        {     
            self::$error = $e->getMessage();     
            return false;     
        }     
    }     
     
    /**   
    * 查询表的记录数   
    *   
    * 参数：   
    * $table_name:表名   
    *   
    * 返回值：表的记录数   
    */     
    function count($table_name)     
    {     
        $dbname = self::$curr_db_name;     
        return self::$mongo->$dbname->$table_name->count();     
    }     
     
    /**   
    * 更新记录   
    *   
    * 参数：   
    * $table_name:表名   
    * $condition:更新条件   
    * $newdata:新的数据记录   
    * $options:更新选择-upsert/multiple   
    *   
    * 返回值：   
    * 成功：true   
    * 失败：false   
    */     
    function update($table_name, $condition, $newdata, $options=array())     
    {     
        $dbname = self::$curr_db_name;     
        $options['safe'] = 1;     
        if (!isset($options['multiple']))     
        {     
            $options['multiple'] = 0;          }     
        try {     
            self::$mongo->$dbname->$table_name->update($condition, $newdata, $options);     
            return true;     
        }     
        catch (MongoCursorException $e)     
        {     
            self::$error = $e->getMessage();     
            return false;     
        }          }     
     
    /**   
    * 删除记录   
    *   
    * 参数：   
    * $table_name:表名   
    * $condition:删除条件   
    * $options:删除选择-justOne   
    *   
    * 返回值：   
    * 成功：true   
    * 失败：false   
    */     
    function remove($table_name, $condition, $options=array())     
    {     
        $dbname = self::$curr_db_name;     
        $options['safe'] = 1;     
        try {     
            self::$mongo->$dbname->$table_name->remove($condition, $options);     
            return true;     
        }     
        catch (MongoCursorException $e)     
        {     
            self::$error = $e->getMessage();     
            return false;     
        }          }     
     
    /**   
    * 查找记录   
    *   
    * 参数：   
    * $table_name:表名   
    * $query_condition:字段查找条件   
    * $result_condition:查询结果限制条件-limit/sort等   
    * $fields:获取字段   
    *   
    * 返回值：   
    * 成功：记录集   
    * 失败：false   
    */     
    function find($table_name, $query_condition, $result_condition=array(), $fields=array())     
    {     
        $dbname = self::$curr_db_name;     
        $cursor = self::$mongo->$dbname->$table_name->find($query_condition, $fields);     
        if (!empty($result_condition['start']))     
        {     
            $cursor->skip($result_condition['start']);     
        }     
        if (!empty($result_condition['limit']))     
        {     
            $cursor->limit($result_condition['limit']);     
        }     
        if (!empty($result_condition['sort']))     
        {     
            $cursor->sort($result_condition['sort']);     
        }     
        $result = array();     
        try {     
            while ($cursor->hasNext())     
            {     
                $result[] = $cursor->getNext();     
            }     
        }     
        catch (MongoConnectionException $e)     
        {     
            self::$error = $e->getMessage();     
            return false;     
        }     
        catch (MongoCursorTimeoutException $e)     
        {     
            self::$error = $e->getMessage();     
            return false;     
        }     
        return $result;     
    }     
     
    /**   
    * 查找一条记录   
    *   
    * 参数：   
    * $table_name:表名   
    * $condition:查找条件   
    * $fields:获取字段   
    *   
    * 返回值：   
    * 成功：一条记录   
    * 失败：false   
    */     
    function findOne($table_name, $condition, $fields=array())     
    {     
        $dbname = self::$curr_db_name;     
        return self::$mongo->$dbname->$table_name->findOne($condition, $fields);     
    }
    
    /**
     * mongodb复杂查询
     * 
     */
    //function command($tableName,$map,$reduce,$out){
      //  $dbname = self::$curr_db_name;     
        //return self::$mongo->$dbname->command(array('mapreduce'=>$tableName,'map'=> $map,'reduce'=> $reduce,'out' => $out));  
    //}
     
    /**   
    * 获取当前错误信息   
    *   
    * 参数：无   
    *   
    * 返回值：当前错误信息   
    */     
    function getError()     
    {     
        return self::$error;     
    }
    
    
    /*
     *  $mongodb=  Mongodbclass::getInstance();
        $map="function() {
		emit(
		this.touid,
		{count:this.totalprice}
		);
            }";
        $reduce="function(key, values) {
	
	var total=0;
	values.forEach(function(val) 
	{
		total   += val.count;
	});
	return total;
        }";
        $result=$mongodb->command(array("mapreduce"=>"consumelog","map"=>$map,"reduce"=>$reduce,"out"=>"temp","query"=>array("touid"=>array('$gt'=>10000023))),array("_id"=>-1),1,1);
     */
    
    
    
    function command($arr,$sort=array(),$limit=100,$skip=0)
    {
        $result=array();
        if(is_array($arr) && count($arr)>0)
        {
             foreach ($arr as $key=>$value)
            {
                 if($key=="map" || $key=="reduce")
                {
                    $arr[$key]=new MongoCode($value);    
                }
            }
            
            if(empty($arr["mapreduce"]))
            {
                return $result;
            }
            if(empty($arr["out"]))
            {
                $arr["out"]="temp";
            }
            $dbname=self::$curr_db_name;
            $temp =self::$mongo->$dbname->command($arr);
            $coll=self::$mongo->$dbname->selectCollection($arr["out"]);
            $result=$coll->find();
            if(!empty($sort))
            {
                $result=$result->sort($sort);
            }
            
            if(!empty($skip))
            {
                $result=$result->skip($skip);
            }
            $result=$result->limit($limit);
            $result = iterator_to_array($result,true); 
            
            $coll->drop(); 
        
        }
        return $result;
       
    }
}     
     
?>  