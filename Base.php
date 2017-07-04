<?php
/**
 * Created by PhpStorm.
 * User: lushaohui
 * Date: 2017/6/28
 * Time: 22:09
 */

namespace lushaohui\model;
use PDOException;
use PDO;

//数据库操作类，在本类中实现对数据库的增删改查
class Base{
    //声明一个静态的$pdo 属性，并给其赋默认值为空;目的是为了在本类中全局调用
    private static $pdo = NULL;
    //声明一个静态的$table属性;目的是为了在本类中全局调用
    private $table;
    private $where = '';


    public function __construct($config,$table) {
        //1.调用数据库连接方法 2.在操作数据库时,必须连接后才再操作,所以在构造方法中执行
        $this->connect($config);
        //1.为表名属性赋值 2.数据库操作必须操作具体的表,所以需要有表名
        $this->table = $table;
    }

    /**
     * 链接数据库
     * @param $config
     */
    private function connect($config){
        //判断属性$pdo是否存在，如果属性$pdo已经链接过数据库了，不需要重复链接了；这样可以大大额提高效率
        if(!is_null(self::$pdo)) return;
        try{
            //Dsn代表数据源，包括数据库类型，主机地址，数 据库名
            $dsn = "mysql:host=" . $config['db_host'] . ";dbname=" . $config['db_name'];
            $user = $config['db_user'];
            $password = $config['db_password'];
            //执行pdo
            $pdo = new PDO($dsn,$user,$password);
            //设置错误类型；默认是显示错误的，为了提高效率将错误类型设置为异常错误就可以被catch捕捉到进而在页面输出
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            //设置字符集；
            $pdo->query("SET NAMES " . $config['db_charset']);
            //将$pdo存到静态属性中
            self::$pdo = $pdo;
        //捕捉错误
        }catch (PDOException $e){
            //shuchucuowu
            exit($e->getMessage());
        }
    }

    public function where($where){
        //lianjie数据库时的where条件
        $this->where = " WHERE {$where}";
        //将对象返出去，方便链式调用
        return $this;
    }

    /**
     * 获取全部数据
     */
    public function get(){
        //按条件查询数据库
        $sql = "SELECT * FROM {$this->table} {$this->where}";
        //调用q方法
        return $this->q($sql);
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function count($field='*'){
        $sql = "SELECT count({$field}) as c FROM {$this->table} {$this->where}";
        $data = $this->q($sql);
        return $data[0]['c'];
    }

    /**
     * 方法用来获得主键字段
     * @param $pri主键
     *
     */
    public function find($pri){
        //如果是Artcile::find(3)那么$priFild是aid=4 如果是Category::find(3)那么$priFild是cid=3
        $priFild=$this->getPri();
        //获得主键id；经$this->where方法之后$this->where的值是WHERE aid=$pri
        $this->where("{$priFild}={$pri}");
        //发送Sql
        $sql="SELECT * FROM {$this->table} {$this->where}";
       // p($sql);
        //调用q方法，执行查询操作，并将值返给$data
        $data=$this->q($sql);
        // p($data);
        //把原来的二维数组变为一维数组
        $data=current($data);
        //p($data);
        //将转化后的一维数组赋值给属性
        $this->data=$data;
        //将对象返回；目的是为了链式调用
        return $this;
    }

    public function findArray($pri){
        $obj = $this->find($pri);
        return $obj->data;
    }


    public function toArray(){
        return $this->data;
    }
    /**
     * 获得表的主键
     */
    public function getPri(){
        //查看表的结构
        $desc = $this->q("DESC {$this->table}");
        //打印desc看结果调试
        //p($desc);
        $priField = '';
        foreach ($desc as $v){
            if($v['Key'] == 'PRI'){
                $priField = $v['Field'];
                break;
            }
        }
        return $priField;
    }
    //q方法执行pdo查询操作，返回有结果集的对象
    public function q($sql){
        try{
            //执行pdo查新操作；query返回的是有结果集的操作
            $result = self::$pdo->query($sql);
            $data = $result->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        }catch (PDOException $e){
            exit($e->getMessage());
        }

    }
    //e方法同样执行pdo查询操作，返回的是有结果集的对象
    public function e($sql){
        return   self::$pdo->exec($sql);

    }


}