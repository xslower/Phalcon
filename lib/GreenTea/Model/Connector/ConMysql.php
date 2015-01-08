<?php
/**
 * @name ConMysql.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-9-9
 * @version 0.01
 */

namespace GreenTea\Model\Connector;


use GreenTea\Model\Connector;

class ConMysql extends Connector{
    /**
     * @param array $options = array(
        'host' => $config->db->host,
        'port' => $config->db->port,
        'username' => $config->db->username,
        'password' => $config->db->password,
        'dbname' => $config->db->defaultDb
        );
     * @return \Phalcon\Db\Adapter\Pdo\Mysql
     */
    protected function _connect(Array $options){
        return new \PhalconEx\Db\Adapter\Mysql($options);
    }

} 