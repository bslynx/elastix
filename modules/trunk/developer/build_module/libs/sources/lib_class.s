class paloSanto{NAME_CLASS} {
    var $_DB;
    var $errMsg;

    function paloSanto{NAME_CLASS}(&$pDB)
    {
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

    /*HERE YOUR FUNCTIONS*/

    function ObtainNum{NAME_CLASS}()
    {
        //Here your implementation
        $query   = "SELECT COUNT(*) FROM ";
        /*
        $result=$this->_DB->getFirstRowQuery($query);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return 0;
        }
        return $result;
        */

        /*THIS LINE SHOULD BE ERASE*/ return array(0 => 0); /*THIS LINE SHOULD BE ERASE*/
    }

    function Obtain{NAME_CLASS}($limit, $offset, $field_pattern)
    {
        //Here your implementation
        $query   = "SELECT * FROM ";
        /*
        $result=$this->_DB->fetchTable($query, true);
        if($result==FALSE)
        {
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
        */

        /*THIS LINE SHOULD BE ERASE*/ return array(); /*THIS LINE SHOULD BE ERASE*/
    }
}
