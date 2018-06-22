<?php
//require_once ("../mutithread/safelock.php");

/**********************************************************
*���ƣ�CDataArrayManager ���й�����
*���ߣ�NIUX
*���ܣ�
1���Զ��н�����ӳ���
2���Զ��н��а�ȫ���ʿ���

����ʾ��
$data_list = {'key1'=>��'key1'=>'4',//
								'key2'=>'sdfsdfdsfsdfdsfdsf',//
                                'key3'=>'sdfdsfdsfdsfdsf',//
				'key2'=>��'key1'=>'5',
								'key2'=>'sdfsdfdsfsdfdsfdsf',
                                'key3'=>'sdfdsfdsfdsfdsf',
				'key3'=>��'key1'=>'5',
				                'key2'=>'sdfsdfdsfsdfdsfdsf',
                                'key3'=>'sdfdsfdsfdsfdsf',};
*����ʱ�䣺2018-04-10
***********************************************************/
class CDataArrayManager
{
    protected $data_list;//���ݶ���  

    public function __construct()
    {
        $this->data_list = array();
    }
/**********************************************************
*���ƣ�pushArrayData
*���ߣ�NIUX
*���ܣ���key-value�ڵ���뵽����
*��Σ�$key,  $value ���ݼ�ֵ��
*����ֵ����
*����ʱ�䣺2018-04-10
***********************************************************/		
    public function pushArrayData($key,  $value)
    {
        //����
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
*���ƣ�bulkPushArrayData
*���ߣ�NIUX
*���ܣ�����ؽ�������뵽����
*��Σ�$bulkData ��������
*����ֵ����
*����ʱ�䣺2018-05-10
***********************************************************/		
    public function bulkPushArrayData(Array &$bulkData)
    {
        //����
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
*���ƣ�shiftArrayData
*���ߣ�NIUX
*���ܣ������ݶ�����ȡ����һ��Ԫ��
*��Σ�&$key, &$result ȡ�������ݼ�ֵ��
*����ֵ��true ���������� false ����������
*����ʱ�䣺2018-04-10
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
                //ȡ����һ��Ԫ�ص�ֵ
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
*���ƣ�getArraySize()
*���ߣ�NIUX
*���ܣ���ȡ���ݶ��а�����Ԫ�ظ���
*��Σ���
*����ֵ�����ݶ��е�Ԫ�ظ���
*����ʱ�䣺2018-04-10
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