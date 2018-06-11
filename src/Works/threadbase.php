<?php
/**********************************************************
*名称：CBaseWork 父线程类
*作者：NIUX
*功能：
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
*名称：exitThread 
*作者：NIUX
*功能：通知线程退出
***********************************************************/	
    public function exitThread()
    {
        $this->isExit = true;
        $this->Notify();
    }	
        
        
/**********************************************************
*名称：start 
*作者：NIUX
*功能：启动
***********************************************************/	
    public function start()
    {
        return true;
    }	
        
}
	
