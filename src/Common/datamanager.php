<?php
//require_once ("../mutithread/safelock.php");

/**********************************************************
*名称：CDataArrayManager 队列管理器
*作者：NIUX
*功能：
1、对队列进行入队出队
2、对队列进行安全访问控制

数组示例
$data_list = {'key1'=>｛'key1'=>'4',//
								'key2'=>'sdfsdfdsfsdfdsfdsf',//
                                'key3'=>'sdfdsfdsfdsfdsf',//
				'key2'=>｛'key1'=>'5',
								'key2'=>'sdfsdfdsfsdfdsfdsf',
                                'key3'=>'sdfdsfdsfdsfdsf',
				'key3'=>｛'key1'=>'5',
				                'key2'=>'sdfsdfdsfsdfdsfdsf',
                                'key3'=>'sdfdsfdsfdsfdsf',};
*创建时间：2018-04-10
***********************************************************/
class CDataArrayManager
{
    protected $data_list;//数据队列  

    public function __construct()
    {
        $this->data_list = array();
    }
/**********************************************************
*名称：pushArrayData
*作者：NIUX
*功能：将key-value节点加入到链表
*入参：$key,  $value 数据键值对
*返回值：无
*创建时间：2018-04-10
***********************************************************/		
    public function pushArrayData($key,  $value)
    {
        //加锁
        try
        {
            //var_dump($value);
            //$lock = new LockSystem(LockSystem::LOCK_TYPE_FILE);
            //$lock->getLock("lockfile", 8);
            //xdebug_start_trace();
            //var_dump($this->data_list);
            //var_dump("before push:", $value);
            $this->data_list[$key] = $value;
            //var_dump("after push:", $this->data_list);
            //xdebug_stop_trace();
            //$lock->releaseLock($key);			
        }
        catch(Exception $e)
        {
            //$lock->releaseLock($key);
            print_r("error message: ".$e->getMessage());
        }

    }

/**********************************************************
*名称：bulkPushArrayData
*作者：NIUX
*功能：整块地将数组加入到链表
*入参：$bulkData 整块数据
*返回值：无
*创建时间：2018-05-10
***********************************************************/		
    public function bulkPushArrayData(Array &$bulkData)
    {
        //加锁
        try
        {
            array_merge($this->data_list, $bulkData);		
        }
        catch(Exception $e)
        {
            print_r("error message: ".$e->getMessage());
        }
    }    
/**********************************************************
*名称：shiftArrayData
*作者：NIUX
*功能：从数据队列中取出第一个元素
*入参：&$key, &$result 取出的数据键值对
*返回值：true 队列有数据 false 队列无数据
*创建时间：2018-04-10
***********************************************************/		
    public function shiftArrayData(&$key, &$value)
    {
        $isEmpty = false;
        try
        {
            //$lock = new LockSystem(LockSystem::LOCK_TYPE_FILE);
            //$lock->getLock("lockfile", 8);
            $ret = count($this->data_list) ==0 ? false: true;
            if($ret):
                //取出第一个元素的值
                $head = each($this->data_list);
                $key = $head['key'];  
                $value = $head['value'];
                //var_dump("before shift:", $this->data_list);
                //$value = array_shift($this->data_list); 
                //$value = array_splice($this->data_list, 0, 1); 
                unset($this->data_list[$key]);
               // var_dump("after shift:", $this->data_list);
                //$lock->releaseLock($key);
            endif;
        }
        catch(Exception $e)
        {
            //$lock->releaseLock($key);
            print_r("error message:".$e->getMessage());
        }

        return $ret;
    }
    /**********************************************************
*名称：getArraySize()
*作者：NIUX
*功能：获取数据队列包含的元素个数
*入参：无
*返回值：数据队列的元素个数
*创建时间：2018-04-10
***********************************************************/		
    public function getArraySize()
    {
        return count($this->data_list);
    }
}


/***********************************
$mg  = new CDataArrayManager();
$key;$value;
$mg->pushArrayData('1111', array('key'=>'k1111', 'value'=>'v1111')); 
$mg->pushArrayData('2222', array('key'=>'k2222', 'value'=>'v2222'));
$mg->pushArrayData('3333', array('key'=>'k3333', 'value'=>'v3333'));
$mg->pushArrayData('4444', array('key'=>'k4444', 'value'=>'v4444'));
$mg->pushArrayData('5555', array('key'=>'k5555', 'value'=>'v5555'));

$mg->ShiftArrayData($key, $value);
$mg->ShiftArrayData($key, $value);
$mg->ShiftArrayData($key, $value);
$mg->ShiftArrayData($key, $value);
$mg->ShiftArrayData($key, $value);

**************************************/