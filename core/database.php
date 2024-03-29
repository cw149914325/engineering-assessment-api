<?php
/**
 * 数据库类
 *
 */
class Database {
    private static $dbh;
    public static $counter=0;
    private static $statement;
    public static $all_query;
    private static $strdbset="";
    private static $arrdbset=array();

    /**
     *
     * @param string $dbsetting 选择数据库配置文件里的指定的配置
     * @return object Database实例
     */
    static public function load($dbsetting='local.database') {
    	
        static $obj;
        if(array_key_exists($dbsetting, self::$arrdbset)&& self::$arrdbset[$dbsetting]&& self::$arrdbset[$dbsetting]["obj"])
        {
            $obj=self::$arrdbset[$dbsetting]["obj"];
        }
        else
        {
            $obj=new Database($dbsetting);
            self::$arrdbset[$dbsetting]["obj"]=$obj;
        }
        self::$strdbset=$dbsetting;
   
//        if(self::$strdbset==$dbsetting)
//        {
//            if(!$obj)
//                $obj = new Database($dbsetting);
//        }
//        else
//        {
//            if(self::$dbh)
//            {
//                self::$dbh = null;
//            }
//            self::$strdbset=$dbsetting;
//            $obj = new Database($dbsetting);
//        }
        return $obj;
    }

    private function __construct($dbsetting) {
        $this->connect($dbsetting);
    }

    /**
     *
     * @param array $dbconfig 数据库连接参数
     * @return object $this
     */
    private function connect($dbsetting) {
//        if(self::$dbh)
//            return $this;
//        $dbconfig = Config::get($dbsetting);
//        $dsn = "mysql:dbname={$dbconfig['dbname']};host={$dbconfig['host']}";
//        $dbh = new PDO($dsn, $dbconfig['user'], $dbconfig['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
//        self::$dbh = $dbh;
        if(array_key_exists($dbsetting, self::$arrdbset)&& self::$arrdbset[$dbsetting]["dbh"])
            return $this;
        $dbconfig = Config::get($dbsetting);
        $dsn = "mysql:dbname={$dbconfig['dbname']};host={$dbconfig['host']}";
        $dbh = new PDO($dsn, $dbconfig['user'], $dbconfig['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES 'utf8';"));
        self::$arrdbset[$dbsetting]["dbh"] = $dbh;
    }

    /**
     * sql查询
     *
     * @param string $sql
     * @param array $prepare_array
     * @return bool $this
     */
    public function query($sql, $prepare_array=array()) {
        return $this->_query($sql, $prepare_array);
    }


    /**
     * 条件查询
     *
     * @param string $table 表名
     * @param string $select select字段
     * @param string $where where 语句
     * @param array $prepare
     * @param string $condition 附加条件, order by , limit 等
     * @return bool on execute result
     */
    public function select($table, $select='*', $where = '', $prepare = array(), $condition='') {
        $where = $where?("WHERE {$where}"):'';
        $sql = " SELECT {$select} FROM {$table} $where {$condition}";
        return $this->_query($sql, $prepare);
    }

    /**
     * 删除数据
     *
     *
     * @param string $table 表名
     * @param string $where where 语句，不能为空，避免删除全表数据，
     * @param array $prepare
     * @return none
     */
    public function delete($table, $where = '', $prepare = array()) {
        if (!$where)
            return false;
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->_query($sql, $prepare);
    }

    /**
     * 插入数据库
     *
     * @param string $table
     * @param array $arr
     * @return bool on execute result
     */
    public function insert($table, $array=array()) {
        $sql = " INSERT INTO {$table} ";
        $fields = array_keys($array);
        $values = array_values($array);
        $condition = array_fill(1, count($fields), '?');
        $sql .= "(`".implode('`,`', $fields)."`) VALUES (".implode(',', $condition).")";
        return $this->_query($sql, $values);
        //return self::$dbh->lastInsertId();
    }
    
    public function insertGetLastID($table, $array=array()) {
        $sql = " INSERT INTO {$table} ";
        $fields = array_keys($array);
        $values = array_values($array);
        $condition = array_fill(1, count($fields), '?');
        $sql .= "(`".implode('`,`', $fields)."`) VALUES (".implode(',', $condition).")";
        $this->_query($sql, $values);   
        return self::$arrdbset[self::$strdbset]["dbh"]->lastInsertId();
    }


    public function batInsert($table,$array=array())
    {
        if(empty($array))
        {
            return false;
        }
        $sql = " INSERT INTO {$table} ";
        $fields = array_keys($array[0]);
        $sql .="(`".implode('`,`', $fields)."`) VALUES ";
        $count =count($array);
        $values =[];
        foreach($array as $k=>$v)
        {
            $condition = array_fill(1, count($v), '?');
            if($k<$count-1)
            {
                $sql .="(".implode(',', $condition)."),";
            }
            else
            {
                $sql .="(".implode(',', $condition).")";
            }

            $values =array_merge($values,array_values($v));

        }

        return $this->_query($sql, $values);
    }

    /**
     * 更新操作
     *
     * @param string $table 表名
     * @param array $array 更新的数据，键 值对
     * @param string $condition 条件
     * @return bool false on execute fail or rowcount on success;
     */
    //public function update($table, $array=array(), $condition=null)
    public function update($table, $set = '', $where = '', $prepare = array()) {
        if(!$where)
            return false;
        $sql = " UPDATE {$table} SET {$set} WHERE {$where}";
        return $this->_query($sql, $prepare);
    }

    /**
     * 取得多行记录集
     *
     * @return array 结果集
     */
    public function fetch_all() {
//          
//        return self::$statement->fetchAll(PDO::FETCH_NUM);
        return self::$statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 取得单行记录
     *
     * @return array
     */
    public function fetch_row() {
        return self::$statement->fetch(PDO::FETCH_ASSOC);
    }

    public function rowcount() {
        return self::$statement->rowCount();
    }

    /**
     * 查询数据表,所有的数据表的查询操作，最终都到这里处理
     *
     * @param string $sql
     * @param array $prepare
     * @return $this
     */
    private function _query($sql, $prepare=array()) 
    {
        $statement = self::$arrdbset[self::$strdbset]["dbh"]->prepare($sql);
        if(!$statement->execute($prepare)) {
            print_r($statement->errorInfo());
            log_message($statement->errorInfo());
            log_message($sql);
            return false;
        }
        self::$statement = $statement;
        /*self::$counter += 1;
		if(false !== ($pos=strpos($sql, '?')))
		{
			$v = array_shift($prepare);
			$sql = str_replace('?', "'{$v}'", $sql);
		}
		self::$all_query[] = $sql;*/
        return true;
    }

    public function beginTransaction() {
        self::$arrdbset[self::$strdbset]["dbh"]->beginTransaction();
    }

    public function commitTransaction() {
        self::$arrdbset[self::$strdbset]["dbh"]->commit();
    }

    public function rollBackTransaction() {
        self::$arrdbset[self::$strdbset]["dbh"]->rollBack();
    }

    static public function profiler() {
        return array(
                'counter'	=> self::$counter
                ,'all_query'	=> self::$all_query
        );
    }
}
?>