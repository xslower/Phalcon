<?php
/**
 * @name BaseController.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 14-8-26
 * @version 0.01
 */

class BaseController extends \Library\Base\Controller {

    /**
     * @param $key
     * @return \GreenTea\Model\NoSQL
     */
    protected function _getCacheInstance(&$key){
        //$tkey = str_replace(['-'], '_', $key);
        $factors = explode('-', $key);
        //$nosql = new \GreenTea\Model\NoSql();
        $nosql = $this->di->getShared(\GreenTea\DI\Services::SERVICE_DB_CACHE);
        switch(count($factors)){
            case 1:
                break;
            case 2:
                $nosql->setTableName($factors[0]);
                $key = $factors[1];
                break;
            case 3:
                $nosql->setDbName($factors[0]);
                $nosql->setTableName($factors[1]);
                $key = $factors[2];
                break;
            default:
                $nosql->setDbName(array_shift($factors));
                $nosql->setTableName(array_shift($factors));
                $key = implode('-', $factors);
        }
        return $nosql;
    }
    
    protected function _getNoCacheCommonInstance($tb_name, $db_name = '') {
    		$cm = new Common($tb_name, $db_name);
    		$cm->setCacheStrategy(GreenTea\Model\CachableBase::CACHE_NONE);
    		return $cm;
    }

}