<?php
//header("Content-type: text/html; charset=gb2312");
//namespace Home\DBConn;
//use Exception;

require_once ("DbException.php");
require_once ("ErrorLog.php");

class CDbConn
{
    private $m_conn;
    private $m_username;
    private $passwd;
    private $m_serverName;
    private $dbname;
    public function __construct()
    {
        $this->m_serverName=null;
        $this->m_username=null;
        $this->passwd =null;
        $this->m_conn = null;
    }
        
    public function _destruct()
    {
        var_dump($this->m_conn);
    }
	
    public function initParam($servername, $user, $password, $dbname)
    {
        $this->m_serverName = $servername;//"172.30.0.35\LIS";
        $this->m_username = $user;//"sa";
        $this->passwd = $password;//"P@ssw0rd";
        $this->dbname = $dbname;//"MIIS6.0";
        return true;
    }

    //连接数据库 ????????
    public function connDB()
    {
        try
        {
            if(null== $this->m_conn)
            {
            //var_dump($this->m_serverName);
                $connectionInfo = array("UID"=>$this->m_username, "PWD"=>$this->passwd, "Database"=>$this->dbname);
                $this->m_conn = sqlsrv_connect( $this->m_serverName, $connectionInfo);
                //var_dump($this->m_conn);
                CDbException::TrackDbErrors();
            }

            if( $this->m_conn == false)
            {
                throw new CDbException("数据库连接失败");
            }
            else
            {
                echo "数据库连接成功";  
            }
        }
        catch(CDbException $ex)
        {
            print_r($ex->toString());           
            CErrorLog::errorLogFile($ex->toString());
        }
        catch(Exception $e)
        {
            print_r("Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage());
        }

        return $this->m_conn == null? false: true;
    }

//选择数据库
    public function selectDB($dbName)
    {


    }

//重连数据库
    public function reconnDB()
    {
        try
        {
            if($this->m_conn != null)
            {
                sqlsrv_close($this->m_conn);
                CDbException::TrackDbErrors();
            }	
            $connectionInfo = array("UID"=>$this->m_username, "PWD"=>$this->passwd, "Database"=>$this->dbname);
            $this->m_conn = sqlsrv_connect( $this->m_serverName, $connectionInfo);	
            CDbException::TrackDbErrors();
        }
        catch(CDbException $ex)
        {
            print_r($ex->toString());
            CErrorLog::errorLogFile("数据库重连失败");
            CErrorLog::errorLogFile($ex->toString());
        }
        catch(Exception $e)
        {
            print_r("Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage());
            return false;
        }

        return true;
    }
/**********************************************************
*名称：querySQL
*作者：NIUX
*功能：执行有返回的数据库查询
*入参：$querySQL 查询sql, $rows dataset
*创建时间：2018-03-31
***********************************************************/
    public function querySQL($querySQL, &$rows)
    {
        $num = 0;
        try
        {
            var_dump($querySQL);
            if($this->m_conn==null)
            {               
                throw new CDbException("数据库未连接",EXCODE_DB_NOT_CONNECTED);             
            }
            
            $query = sqlsrv_query($this->m_conn, $querySQL);
            CDbException::TrackDbErrors();
            if(!$query):
                throw new CDbException("查询发生错误。 SQL:$querySQL");
            endif;            
            while($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC))
            {
                $rows[$num] = $row;
                //print_r($row);
                $num++;
            }	
        }
        catch(CDbException $ex)
        {
            print_r($ex->toString());
            CErrorLog::errorLogFile($querySQL);
            CErrorLog::errorLogFile($ex->toString());
            //如果是通讯链路中断引起，需要重连数据库
            if($ex->getCode() == 10054):
                $this->reconnDB();
            endif;
            if($ex->getCode() == 1000)://数据库未连接
                $this->connDB();
            endif;              
        }
        catch(Exception $e)
        {
            //var_dump($querySQL);
            //var_dump($rows);
            //print_r("Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage());
            CErrorLog::errorLogFile("Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage());
            return 0;
        }

        return $num;
    }

//执行无返回的数据库查询
    public function execSQL($querySQL)
    {
        try
        {
            if($this->m_conn==null)
            {
                throw new Exception("数据库未连接");
            }
            var_dump($querySQL);
            //var_dump($querySQL, $Param);
            $ret = sqlsrv_query($this->m_conn, $querySQL); 
            CDbException::TrackDbErrors();
            if(!$ret):
                throw new CDbException("语句执行失败SQL:$querySQL");
            endif;
        }
        catch(CDbException $ex)
        {
            print_r($ex->toString());
            CErrorLog::errorLogFile($querySQL);
            CErrorLog::errorLogFile($ex->toString());
        }
        catch(Exception $e)
        {
            print_r("Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage());
            return true;
        }

        return true;
    }
//断开数据库连接??
    public function disConn()
    {	
        try
        {
            if($this->m_conn != null)
            {
                sqlsrv_close($this->m_conn);
                CDbException::TrackDbErrors();
                $this->m_conn = null;			
            }	
            else
            {
                throw new CDbException("数据库未连接");
            }
        }
        catch(CDbException $ex)
        {
            print_r($ex->toString());
            CErrorLog::errorLogFile($ex->toString());
        }
        catch(Exception $e)
        {
            print_r("Error: [File]".$e->getFile()."[Line]".$e->getLine()."[Message]". $e->getMessage());
        }

        return true;
    }
}