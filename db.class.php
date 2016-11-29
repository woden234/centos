<?php

class db {

    private static $Connect_hosts = array(); //默认没有连接

    //实现数据库不同服务器自动连接 默认连接$host=0 如果操作其他服务器数据库，加上对应主机参数即可

    private static function connect($host = 0) {//默认连接主机为第一个

        $hosts = array(
            1 => array('host' => '121.40.209.182', 'user' => 'hdzuoye', 'passwd' => 'nQ5eB78QUnF2296K', 'dbname' => 'hdzuoye'),
            0 => array('host' => 'localhost', 'user' => 'root', 'passwd' => '4892993abc1234', 'dbname' => 'woden')
        );
        if (!isset($hosts[$host]))
            die('host not setting');
        $link = mysqli_connect($hosts[$host]['host'], $hosts[$host]['user'], $hosts[$host]['passwd'], $hosts[$host]['dbname'])
                or die('Could not connect: ' . mysqli_error($link));
        mysqli_set_charset($link, "utf8");
        return $link;
    }

    public static function escape($value, $host = 0) {//防注入 $value支持数组
        if (!isset(self::$Connect_hosts[$host]))
            self::$Connect_hosts[$host] = self::connect($host);
        if (is_array($value)) {
            foreach ($value as $k => $v)
                $value[$k] = self::escape($v, $host);
        } else
            $value = mysqli_real_escape_string(self::$Connect_hosts[$host], $value);
        return $value;
    }

    public static function insert_id($host = 0) {
        return mysqli_insert_id(self::$Connect_hosts[$host]);
    }

    public static function query($sql, $host = 0) {
        if (!isset(self::$Connect_hosts[$host]))
            self::$Connect_hosts[$host] = self::connect($host);

        if (defined("debug")) {//调试模式
            $t1 = microtime(1);
            $result = mysqli_query(self::$Connect_hosts[$host], $sql);
            $tout = microtime(1) - $t1;
            global $M_debug;
            $M_debug[] = $sql . " -- use time:" . $tout . "\r\n\r\n";
        } else
            $result = mysqli_query(self::$Connect_hosts[$host], $sql);
        $errno = mysqli_errno(self::$Connect_hosts[$host]);
        if ($errno == 0)
            return $result;
        die($sql . "<br>" . mysqli_error(self::$Connect_hosts[$host]) . " errno[$errno]");
        return false;
    }

    public static function select($sql, $host = 0) {
        $result = self::query($sql, $host);
        $retval = array();
        if ($result == NULL)
            return $retval;
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $retval[] = $row;
        }
        return $retval;
    }

    public static function insert($table, $array, $host = 0) {
        $ziduan = "`" . implode("`,`", array_keys($array)) . "`";
        $values = "'" . implode("','", self::escape($array)) . "'";
        return self::query("INSERT INTO $table($ziduan) values($values)", $host);
    }

}

?>
