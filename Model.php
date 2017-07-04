<?php
/**
 * Created by PhpStorm.
 * User: lushaohui
 * Date: 2017/6/28
 * Time: 21:15
 */

namespace lushaohui\model;


class Model
{
    //声明一个静态属性；方便在静态方法中被调用
    private static $config;
    public function __call($name, $arguments)
    {
        //静态调用parseAction方法；当在外面实例化调用一个未定义的方法时会自动执行此方法 并将其返回
        return self::parseAction($name,$arguments);
    }
    public static function __callStatic($name, $arguments)
    {
        //静态调用parseAction方法；当在外面静态调用一个未定义的方法时会自动执行此方法 并将其返回
        return self::parseAction($name,$arguments);
    }
    private static function parseAction($name, $arguments){
        //获得静态调用的类名，并赋值给$table;目的是当fan出去的时候被外面静态调用的方法接到
        $table = get_called_class();
        //index.php?s=home/entry/arc
        //p($table);exit;
        //p出来的结果为system\model\Article
        //由上面p出来的结果可以看出获得的类名是带有命名空间的，所以我们需要对其进行截取，将类名单独取出来
        $table = strtolower(ltrim(strrchr($table,'\\'),'\\'));
        //实例化base类，调用base类中相应的方法，并将其返出去让外面静态调用的时候被接到；
        return call_user_func_array([new Base(self::$config,$table),$name],$arguments);
    }

    /**
     * @param mixed $config
     */
    public static function setConfig($config)
    {
        //把传进来的配置项赋值给给本类的静态属性；数据库配置项在加载时候会执行本方法；这样我们就获得了数据库的配置项,以后在调用Base类时会把本配置项再传给Base类.
        self::$config = $config;
    }
}