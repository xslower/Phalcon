<?php

/**
 * @name Correlate.php
 * @desc 
 * @author 宋文峰(songwf3@lenovo.com)
 * @date 2014/12/22 0022
 * @version 0.01
 */

namespace GreenTea\Model\DataBase;

use Exception;
use GreenTea\Model\DataBase;
use GreenTea\Model\Sql\Factory;
use GreenTea\Utility\XArray;

class Correlate extends DataBase {
    /**
     * 关联类型
     * 对于多对多关系，如果把关联表的数据合并到关系表中时(例如：tag只有name有效，所以无需独立的表)，
     * 则因为关系表成为实体，且拥有唯一主键，而成为‘一对多’关系
     */
    const HAS_MANY = 'HAS_MANY';          //One to Many 
    const BELONGS_TO = 'BELONGS_TO';      //Many to One, Include One to One
    const MANY2MANY = 'MANY_TO_MANY';     //Many to Many
    //用于设置的键名
    const RELATE_TYPE = 'RELATE_TYPE';    //关联类型
    const SELF_FN_KEY = 'SELF_FN_KEY';    //本表与关联表关联的键，[(1vN/NvN)主键，(Nv1)外键]
    const RELATE_FN_KEY = 'RELATE_FN_KEY';//关联表与本表关联的键，[(Nv1/NvN)主键，(1vN)外键]
    //用于多对多关系的关系表(中间那个只有主键的表)的设置
    const M2M_TABLE = 'M2M_TABLE';        //关系表的model名
    const M2M_BASE_PK = 'M2M_BASE_PK';    //在关系表中本表的字段名
    const M2M_RELATE_PK = 'M2M_RELATE_PK';//关联表在关系表中的字段名

    /**
     * 在fields中设置查询关联表的字段，如果不设则获取所有字段
     *  $fields[self::RELATE]['foreign_key'] = ['user_id', 'field2', 'field3'];
     * 在condition中设置查询关联表达条件，如果不设则传空
     *  $condition[self::RELATE]['foreign_key'] = ['user_id' => 5, 'name' => 'hahaha'];
     * 在append中设置是否开启关联表查询，以及启用哪个关联表查询
     *  $append[self::RELATE] = self::RELATE_ON;    //临时开启关联查询，无视$this->_relate_on
     *  $append[self::RELATE] = self::RELATE_OFF;   //临时关闭关联查询，无视$this->_relate_on
     *  $append[self::RELATE] = 'foreign_key1';     //只开启foreign_key1定义的关联查询
     */
    const RELATE = 'RELATE';
    const RELATE_ON = 'RELATE_ON';
    const RELATE_OFF = 'RELATE_OFF';

    /**
     * 关联关系定义，用于自动关联表查询。示例：PS:本表的Model是Student
     * 'Class' => [ //key是关联表的Model名，
     *      self::RELATE_TYPE => self::MANY2MANY,    //关联类型
     *      self::SELF_FN_KEY => 'student_id',       //本表与关联表关联的键，这里是主键
     *      self::RELATE_TABLE_PK => 'class_id',     //关联表的主键
     *      self::M2M_TABLE => 'ClassAndStudent',    //关系表的model
     *      self::M2M_BASE_PK => 'student_id',       //在关系表中本表的字段名
     *      self::M2M_RELATE_PK => 'class_id',       //关联表在关系表中的字段名
     * ];
     * @var array
     * @alert 如果一个表有多个外键，则关联类型必须为belongs to，否则无法进行关联表数据组合。
     */
    protected $_relationship;

    /**
     * @var bool 是否开启关联查询
     */
    protected $_relate_on = false;

    public function __construct() {
        parent::__construct();

        $this->_correlate_default = $this->_relate_on;
    }
    
    protected function _relateOn(){
        if (!$this->_relationship || !is_array($this->_relationship)) {
            return false;
        }
        return $this->_relate_on;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(Array $fields){
        $id = parent::insert($fields);
        if(!$this->_relateOn()){
            return $id;
        }
        //循环根据关联关系定义进行关联插入
        foreach ($this->_relationship as $relate_model => $config) {
            if(!isset($fields[$relate_model])){
                continue;
            } //如果用户加入了关联表数据，则继续
            $relate_list = $fields[$relate_model];
            if(!XArray::isTwoDimension($relate_list)){ //如果是一维数组，则包裹成2维数组方便统一处理
                $relate_list = [$relate_list];
            }
            $rel_model = $this->_getModel($relate_model);
            switch ($config[self::RELATE_TYPE]){
                case self::BELONGS_TO:  //多对一关系不允许关联插入，忽略之
                    break;
                case self::HAS_MANY:    //一对多关系一般也不会关联插入，只有post和tag这种多对多退化的一对多关系才会
                    foreach ($relate_list as $rel_fields) {
                        $rel_fields[$config[self::RELATE_FN_KEY]] = $id;
                        $rid = $rel_model->insertOrUpdate($rel_fields);
                    }
                    break;
                case self::MANY2MANY:   //多对多关系一般也只是关联插入关系表，而不插入关联表
                    $m2m_model = $this->_getModel($config[self::M2M_TABLE]);
                    foreach ($relate_list as $rel_fields) {
                        $rid = $rel_model->insertOrUpdate($rel_fields); //通过I or U省去了判断是否存在的操作
                        $m2m_fields[$config[self::M2M_BASE_PK]] = $id;
                        $m2m_fields[$config[self::M2M_RELATE_PK]] = $rid;
                        $m2m_model->insert($m2m_fields);
                    }
                    break;
            }
        }
        return $id;
    }
    
    /**
     * {@inheritdoc}
     * 
     */
    public function update(array $fields, array $condition) {
        $ret = parent::update($fields, $condition);
        if(!$this->_relateOn()){
            return $ret;
        }
        $pk = $this->_getPkFields();
        if(count($pk) > 1){ //不支持复合主键的关联修改
            $this->_logger->warning('Does not support Multi PK with collrelate Update! Table[' . $this->getTableName() . ']');
            return $ret;
        }
        //只有修改主键才需要关联修改
        $old_pkv = $this->_getPkValue($condition);
        $new_pkv = $this->_getPkValue($fields);
        if(!$old_pkv || !$new_pkv){
            return $ret;
        }
        //循环根据关联关系定义进行关联更新
        foreach ($this->_relationship as $relate_model => $config) {
            switch ($config[self::RELATE_TYPE]){
                case self::BELONGS_TO:  //多对一关系不允许关联修改，忽略之
                    break;
                case self::HAS_MANY:    //1vN只有修改主键时允许关联修改
                    $rel_model = $this->_getModel($relate_model);
                    $rel_fields[$config[self::RELATE_FN_KEY]] = $new_pkv;
                    $rel_condition[$config[self::RELATE_FN_KEY]] = $old_pkv;
                    $rel_model->update($rel_fields, $rel_condition);
                    break;
                case self::MANY2MANY:   //1)修改主键则更新关系表
                    //2)修改两实体的关系则请直接操作关系表，这里无法简单的定义对关系表的操作。
                    $m2m_model = $this->_getModel($config[self::M2M_TABLE]);
                    $m2m_fields[$config[self::M2M_BASE_PK]] = $new_pkv;
                    $m2m_condition[$config[self::M2M_BASE_PK]] = $old_pkv;
                    $m2m_model->update($m2m_fields, $m2m_condition);
                    break;
            }
        }
        return $ret;
    }
    
    /**
     * {@inheritdoc}
     * 虽然支持批量删除的关联删除不太复杂 (就是先查询下删除了哪些id，然后通过id列表删除即可)，
     * 但是考虑到删除操作的危险性，还是不支持了。
     */
    public function delete(array $condition) {
        $ret = parent::delete($condition);
        if(!$this->_relateOn()){
            return $ret;
        }
        $pk = $this->_getPkFields();
        if(count($pk) > 1){ //不支持复合主键的关联删除
            $this->_logger->warning('Does not support Multi PK with collrelate Delete! Table[' . $this->getTableName() . ']');
            return $ret;
        }
        //只有主键删除才能关联删除
        $pkv = $this->_getPkValue($condition);
        if(!$pkv){
            return $ret;
        }
        //循环根据关联关系定义进行关联更新
        foreach ($this->_relationship as $relate_model => $config) {
            switch ($config[self::RELATE_TYPE]){
                case self::BELONGS_TO:  //多对一关系不允许关联删除，忽略之
                    break;
                case self::HAS_MANY:    //1vN
                    $rel_model = $this->_getModel($relate_model);
                    $rel_condition[$config[self::RELATE_FN_KEY]] = $pkv;
                    $rel_model->delete($rel_condition);
                    break;
                case self::MANY2MANY:   //多对多关系则删除关系表的数据
                    $m2m_model = $this->_getModel($config[self::M2M_TABLE]);
                    $m2m_condition[$config[self::M2M_BASE_PK]] = $pkv;
                    $m2m_model->delete($m2m_condition);
                    break;
            }
        }
        return $ret;
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function getList(Array $condition = [], Array $append = [], Array $fields = []) {
        $list = parent::getList($condition, $append, $fields);
        if (!$this->_relationship || !is_array($this->_relationship)) {
            return $list;
        }
        $fk_setting = $this->_relationship;
        $relate = XArray::fetchItem($append, self::RELATE, '');
        $relate_on = $this->_relate_on;
        switch ($relate) {
            case self::RELATE_ON:
                $relate_on = true;
                break;
            case self::RELATE_OFF:
                $relate_on = false;
                break;
            case '':    //没有设置关联查询，则保持默认
                break;
            default:
                $relate_on = true;
                if (!isset($fk_setting[$relate])) { //指定的关联查询的外金没有定义
                    $this->_logger->warning('Have not Define such a Foreign Key [' . $relate . ']!');
                    return $list;
                }
                $fk_setting = [];   //去掉其它没有指定的定义
                $fk_setting[$relate] = $this->_relationship[$relate];
        }
        if ($relate_on) {
            $list = $this->_getRrelate($list, $condition, $fields, $fk_setting);
        }
        return $list;
    }

    /**
     * 关联查询，把所有外键均转化成关联表对应的记录
     * @param array $list   查询结果列表
     * @param array $condition  主要是给HAS_MANY和MANY2MANY用的，用于对其多条数据的筛选
     * @return array
     * @throws Exception
     */
    protected function _getRrelate(Array $list, Array $condition, Array $fields, Array $relationship) {
        $relate_condition = XArray::fetchItem($condition, self::RELATE, []);
        $relate_fields = XArray::fetchItem($fields, self::RELATE, []);
        foreach ($relationship as $relate_model => $config) {
            $current_condition = XArray::fetchItem($relate_condition, $relate_model, []);
            $current_fields = XArray::fetchItem($relate_fields, $relate_model, []);
            $self_fnkey = $config[self::SELF_FN_KEY];
            $relate_fnkey = $config[self::RELATE_FN_KEY];
            switch ($config[self::RELATE_TYPE]) {
                case self::BELONGS_TO:
                    $one_list = $this->_getRelateData($list, $self_fnkey, $relate_model, $relate_fnkey, $current_condition, $current_fields);
                    $this->_assembleRelateData($list, $self_fnkey, $one_list, $relate_fnkey, $relate_model);
                    break;
                case self::HAS_MANY:
                    $many_list = $this->_getRelateData($list, $self_fnkey, $relate_model, $relate_fnkey, $current_condition, $current_fields);
                    $this->_assembleRelateData($list, $self_fnkey, $many_list, $relate_fnkey, $relate_model);
                    break;
                case self::MANY2MANY:
                    $m_self_key = $config[self::M2M_BASE_PK];
                    $m_relate_key = $config[self::M2M_RELATE_PK];
                    $m_list = $this->_getRelateData($list, $self_fnkey, $config[self::M2M_TABLE], $m_self_key, [], []);
                    $relate_list = $this->_getRelateData($m_list, $m_relate_key, $relate_model, $relate_fnkey, $current_condition, $current_fields);
                    //把关系表跟关联表merge到一起
                    $this->_assembleRelateData($m_list, $m_relate_key, $relate_list, $relate_fnkey, '');
                    $this->_assembleRelateData($list, $self_fnkey, $m_list, $m_self_key, $relate_model);
                    break;
                default:
                    throw new Exception('Wrong Relate Type! [' . $config[self::RELATE_TYPE] . ']');
            }
        }
        return $list;
    }

    /**
     * 以基准列表的相关字段作为条件(加上其它条件)查询关联表
     * @param array $base_list      基准列表
     * @param string $base_fnkey    本表与关联表关联的键，主键或外键
     * @param string $relate_model  关联表model名
     * @param string $relate_fnkey  关联表与本表关联的键，主键或外键
     * @param array $condition  查询条件
     * @param array $fields     
     * @return array
     */
    protected function _getRelateData(Array $base_list, $base_fnkey, $relate_model, $relate_fnkey, Array $condition, Array $fields) {
        $key_list = [];
        foreach ($base_list as $row) {
            $key_list[$row[$base_fnkey]] = $row[$base_fnkey];
        }
        $condition[$relate_fnkey][Factory::IN] = $key_list;
        $relate_mdoel = $this->_getModel($relate_model);
        $append[self::RELATE] = self::RELATE_OFF;
        $data = $relate_mdoel->getList($condition, $append, $fields);
        return $data;
    }

    /**
     * 把基准表跟关联表通过外键关联起来
     * @param array $base_list
     * @param string $base_fnkey    本表与关联表关联的键，主键或外键
     * @param array $relate_list
     * @param string $relate_fnkey  关联表与本表关联的键，主键或外键
     * @param string $relate_model  关联表的Model名。   如果为空则使用array_merge
     */
    protected function _assembleRelateData(Array &$base_list, $base_fnkey, Array $relate_list, $relate_fnkey, $relate_model) {
        $indexed_list = XArray::listToTree($relate_list, [$relate_fnkey]);
        foreach ($base_list as &$row) {
            if($relate_model){
                $row[$relate_model] = $indexed_list[$row[$base_fnkey]];
            }else{
                $row = array_merge($row, $indexed_list[$row[$base_fnkey]]);
            }
        }
    }

    /**
     * @param string $model_name
     * @return Correlate
     */
    protected function _getModel($model_name) {
        return new $model_name;
    }

}
