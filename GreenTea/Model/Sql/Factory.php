<?php
/**
 * @name Factory.php
 * @desc 暂不支持预处理，在一次请求中，prepare后多次执行肯定是效率高些，
 *  但是在多次请求中，后面的prepare能否复用前面的prepare，以及最终提高性能还有待验证。
 * @author 宋文峰(songwenfeng@baidu.com)
 * @date 13-10-29
 * @version 0.02
 */
namespace GreenTea\Model\Sql;

class Factory extends \GreenTea\Factory{
    const INSERT = 'insert';
    const DELETE = 'delete';
    const UPDATE = 'update';
    const SELECT = 'select';
    const INSERT_OR_UPDATE = 'insert_or_update';

    const LAST_INSERT_ID= 'last_insert_id';

    //condition
    const BETWEEN       = 'BETWEEN';
    const IN            = 'IN';
    const LIKE          = 'LIKE';
    //append
    const ORDER_BY      = 'ORDER BY';
    const LIMIT         = 'LIMIT';
    const GROUP_BY      = 'GROUP BY';
    //返回的列表使用哪个field作为key重新组织列表
    const RETURN_KEY    = 'return_key';

    //using for insert on duplicate key update
    const INSERT_FIELDS = 'insert_fields';
    const UPDATE_FIELDS = 'update_fields';

    //using for fields assemble
    const SELF_ADD  = 'SELF_ADD';
    const SELF_SUB  = 'SELF_SUB';

    //using for condition assemble
    const _OR_      = 'OR';
    const _AND_     = 'AND';

    protected static $_instance;

    protected function __construct(){

    }

    public static function getInstance(){
        if(!self::$_instance instanceof self){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function getClassname($target){
        return 'GreenTea\Model\Sql\Statement\\' . $target;
    }


}