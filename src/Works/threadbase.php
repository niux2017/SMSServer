<?php
/**********************************************************
*���ƣ�CBaseWork ���߳���
*���ߣ�NIUX
*���ܣ�
***********************************************************/
//namespace Home\Controller;
//use Home\Controller\Thread;
class CBaseWork 
{
    private $isExit;
	
    public function __construct() {
        $this->isExit = false;
    }


/**********************************************************
*���ƣ�exitThread 
*���ߣ�NIUX
*���ܣ�֪ͨ�߳��˳�
***********************************************************/	
    public function exitThread()
    {
        $this->isExit = true;
        $this->Notify();
    }	
        
        
/**********************************************************
*���ƣ�start 
*���ߣ�NIUX
*���ܣ�����
***********************************************************/	
    public function start()
    {
        return true;
    }	
        
}
	
