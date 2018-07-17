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

    //�������ݿ� ????????
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
                throw new CDbException("���ݿ�����ʧ��");
            }
            else
            {
                echo "���ݿ����ӳɹ�";  
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

//ѡ�����ݿ�
    public function selectDB($dbName)
    {


    }

//�������ݿ�
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
            CErrorLog::errorLogFile("���ݿ�����ʧ��");
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
*���ƣ�querySQL
*���ߣ�NIUX
*���ܣ�ִ���з��ص����ݿ��ѯ
*��Σ�$querySQL ��ѯsql, $rows dataset
*����ʱ�䣺2018-03-31
***********************************************************/
    public function querySQL($querySQL, &$rows)
    {
        $num = 0;
        try
        {
            var_dump($querySQL);
            if($this->m_conn==null)
            {               
                throw new CDbException("���ݿ�δ����",EXCODE_DB_NOT_CONNECTED);             
            }
            
            $query = sqlsrv_query($this->m_conn, $querySQL);
            CDbException::TrackDbErrors();
            if(!$query):
                throw new CDbException("��ѯ�������� SQL:$querySQL");
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
            //�����ͨѶ��·�ж�������Ҫ�������ݿ�
            if($ex->getCode() == 10054):
                $this->reconnDB();
            endif;
            if($ex->getCode() == 1000)://���ݿ�δ����
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

//ִ���޷��ص����ݿ��ѯ
    public function execSQL($querySQL)
    {
        try
        {
            if($this->m_conn==null)
            {
                throw new Exception("���ݿ�δ����");
            }
            var_dump($querySQL);
            //var_dump($querySQL, $Param);
            $ret = sqlsrv_query($this->m_conn, $querySQL); 
            CDbException::TrackDbErrors();
            if(!$ret):
                throw new CDbException("���ִ��ʧ��SQL:$querySQL");
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
//�Ͽ����ݿ�����??
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
                throw new CDbException("���ݿ�δ����");
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