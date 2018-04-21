<?php
namespace core\lib;
/**
 * Created by PhpStorm.
 * User: xiebh
 * Date: 2018/4/15
 * Time: 22:56
 */
class Db
{
    protected static $_dbh = null;
    protected $_dbType = 'mysql';
    protected $_pconnect = true;//是否使用长连接;
    protected $_host = 'localhost';
    protected $_port = '3306';
    protected $_user = 'root';
    protected $_pass = 'root';
    protected $_dbName = null;
    protected $_sql = false;// 一条sql语句
//    protected $_tbName='';
    protected $_where = '';
    protected $_order = '';
    protected $_limit = '';
    protected $_field = '*';
    protected $_clear = 0;//状态，0表示查询田间干净，1表示查询条件污染
    protected $_trans = 0;//事务指令数

    /**   初始化Db类
     * Db constructor.
     * @param $conf
     */
    public function __construct($conf)
    {
        class_exists('PDO') or die('PDO:class not exits.');
        $this->_host = $conf['host'];
        $this->_user = $conf['user'];
        $this->_pass = $conf['pass'];
        $this->_dbName = $conf['dbname'];
        if (is_null(self::$_dbh)) {
            $this->_connect();
        }

    }

    /**
     * 数据库连接方法
     */
    public function _connect()
    {
        $dsn = $this->_dbType . ':host=' . $this->_host . ';port=' . $this->_port . ';dbname=' . $this->_dbName;
        $options = $this->_pconnect ? array(PDO::ATTR_PERSISTENT => true) : array();
        try {
            $dbh = new PDO($dsn, $this->_user, $this->_pass, $options);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//错误报告设置为 抛出exception异常
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);//试着使用本地预处理语句 防sql注入
        } catch (PDOException $e) {
            die('connect fail' . $e->getMessage());
        }
        $dbh->exec('SET NAMES utf8');
        self::$_dbh = $dbh;
    }

    /**字段和表明添加 `
     * @param $value
     * @return string
     */
    protected function _addChar($value)
    {
        if ('*' == $value || false !== strpos($value, '(') || false !== strpos($value, '.') || false !== strpos($value, '`')) {
        } elseif (false == strpos($value, '`')) {
            $value = '`' . trim($value) . '`';
        }
        return $value;
    }


    /**获取数据表的所有字段信息
     * @param $tbName
     * @return array
     */
    public function _tbFields($tbName)
    {
        $sql = "select column_name from information_schema.columns where table_name='" . $tbName . "' and " . "table_schema='" . $this->_dbName . "'";
        $stmt = self::$_dbh->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ret = [];
        foreach ($result as $k => $v) {
            $ret[$v['column_name']] = 1;
        }
        return $ret;
    }

    /**
     * 过滤并格式化数据表字段
     */
    public function _dataFormmat($tbname, $data)
    {
        if (!is_array($data)) return [];
        $table_column = $this->_tbFields($tbname);
        $ret = [];
        foreach ($data as $k => $val) {
            if (!is_scalar($val)) continue;
            if (array_key_exists($k, $table_column)) {
                $k = $this->_addChar($k);
                if (is_int($val)) $val = intval($val);
                if (is_float($val)) $val = floatval($val);
                if (is_string($val)) $val = '"' . addslashes($val) . '"';
            }
            $ret[$k] = $val;
        }
        return $ret;
    }

    /**执行查询语句
     * @param $sql
     * @return mixed
     */
    protected function _doQuery($sql)
    {
        $this->_sql = $sql;
        $stmt = self::$_dbh->prepare($this->_sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**执行插入 删除 更新 语句
     * @param $sql
     * @return mixed
     */
    protected function _doExec($sql)
    {
        $this->_sql = $sql;
        $result = self::$_dbh->exec($sql);
        return $result;
    }

    //获取最近一次的sql语句
    public function getLastSql()
    {
        return $this->_sql;
    }


    /**新增数据
     * @param $tbName 数据表名称
     * @param $data //提交过来的数据
     * @return mixed|void
     */
    public function insert($tbName, $data)
    {
        $data = $this->_dataFormmat($tbName, $data);
        if (!$data) return;
        $sql = 'insert into ' . $tbName . '(' . implode(',', array_keys($data)) . ')values(' . implode(',', array_values($data)) . ')';
        return $this->_doExec($sql);
    }

    /**删除方法
     * @param $tbName 数据表名称
     * @return bool|mixed
     */
    public function delete($tbName)
    {
        //防止全表删除
        if (!trim($this->_where)) return false;
        $sql = 'delete from ' . $tbName . $this->_where;
        $this->_clear = 1;
        $this->_clear();
        return $this->_doExec($sql);
    }

    /**查询语句
     * @param string $tbName 数据表名称
     * @return mixed
     */
    public function select($tbName = "")
    {
        $sql = 'select ' . trim($this->_field) . ' from ' . $tbName . $this->_where . ' ' . $this->_order . ' ' . $this->_limit;
        $this->_clear = 1;
        $this->_clear();
        $res = $this->_doQuery($sql);
        return $res;
    }

    /**  数据表的更新操作
     * @param $tbName要更新的数据表名
     * @param $data 更新的数据
     * @return bool|mixed
     */
    public function update($tbName, $data)
    {
        if (!trim($this->_where)) return false;
        $data = $this->_dataFormmat($tbName, $data);
        if (!$data) return false;
        $upArr = [];
        foreach ($data as $k => $v) {
            $upArr[] = $k . "=" . $v;
        }
        $str = implode(',', $upArr);
        $sql = 'update ' . $tbName . ' set ' . trim($str) . ' ' . $this->_where;
        $rows = $this->_doExec($sql);
        return $rows;
    }


    /**指定where条件
     * @param $option
     * @return $this
     */
    public function where($option)
    {
        if ($this->_clear > 0) $this->_clear();
        if (is_array($option) && empty($option)) return $this;
        if (is_string($option) && empty(trim($option))) return $this;
        $this->_where = ' where ';
        if (is_string($option)) $this->_where .= $option;
        if (is_array($option)) {
            foreach ($option as $k => $v) {
                if (!is_array($v)) {
                    $logic = 'and';
                    $condition = '(' . $this->_addChar($k) . '="' . $v . '")';
                }
                if (is_array($v)) {
                    $logic = $v[0];
                    $condition = $k . ' ' . $logic . '(' . implode(',', $v[1]) . ')';
                }
                $this->_where .= isset($mark) ? $logic . $condition : $condition;
                $mark = 1;
            }
        }
//        var_dump($this->_where);exit;
        return $this;
    }


    /** 排序操作
     * @param $option要排序的字段 支持数组和字符串模式 ['field1'=>desc,'field2'=>.....]  ；filed1 desc ,field2 ....
     * @return $this
     */
    public function order($option)
    {
        if ($this->_clear > 0) $this->_clear();
        if (is_array($option) && empty($option)) return $this;
        if (is_string($option) && empty(trim($option))) return $this;
        $this->_order = 'order by ';
        if (is_string($option)) {
            $this->_order .= $option;
        }
        if (is_array($option)) {
            foreach ($option as $k => $v) {
                $order = $this->_addChar($k) . ' ' . $v;
                $this->_order .= isset($mark) ? ' , ' . $order : $order;
                $mark = 1;
            }
        }
        return $this;
    }

    /**设置查询行数及页数
     * @param $page 当pagesize不为null的时候 page为页数 否则为行数
     * @param null $pagesize
     */
    public function limit($page, $pagesize = null)
    {
        if ($this->_clear > 0) $this->_clear();
        if ($pagesize === null) {
            $this->_limit = ' limit ' . $page;
        } else {
            $pageval = (intval($page) - 1) * $pagesize;
            $this->_limit = ' limit ' . $pageval . ' , ' . $pagesize;
        }
        return $this;
    }

    /**设置查询字段
     * @param $field 设置要查询的字段，支持数组和字符串
     * @return $this
     */
    public function field($field)
    {
        if ($this->_clear > 0) $this->_clear();
        if (is_string($field)) {
            $arr = explode(',', $field);
            $nfield = array_map(array($this, '_addChar'), $arr);//为数组arr中的每一个字都进行一次 _addChar() 函数的运算
            $this->_field = implode(',', $nfield);
            return $this;
        }

    }

    /*
     * 清楚标记的方法
     */
    protected function _clear()
    {
        $this->_where = "";
        $this->_limit = "";
        $this->_order = "";
        $this->_field = "*";
        $this->_clear = 0;
    }


    /**
     * 开启事务处理
     */
    public function startTrans(){
        if($this->_trans==0) self::$_dbh->beginTransaction();
        $this->_trans++;
        return;
    }

    /**事务回滚
     * @return bool
     */
    public function rollBack(){
        $result=true;
        if($this->_trans>0){
            $result=self::$_dbh->rollback();
            $this->_trans=0;
        }
        return $result;
    }

    //事务处理的提交
    public function commit(){
        $result=true;
        if($this->_trans>0){
            $result=self::$_dbh->commit();
            $this->_trans=0;
        }
        return $result;
    }


}