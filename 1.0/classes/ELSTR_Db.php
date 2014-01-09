<?php

/**
* Class to create ELSTR specific DB
*
* @author Marco Egli, Felix Nyffenegger
* @version 1.0
* @created 19-Okt-2009 17:14:59
*/
class ELSTR_Db {
    var $m_dbAdapter;

    function __construct($dbAdapter)
    {
        $this->m_dbAdapter = $dbAdapter;
    }

    /**
    * Override: Insert
    *
    * @param mixed $table The table to insert data into.
    * @param array $bind Column-value pairs.
    * @param string $userId
    * @return array
    */
    public function insert($table, $bind, $userId = '')
    {
        $insertDefaultValues = $this->_getInsertDefaultData($table, $userId);
        $bind = array_merge($bind, $insertDefaultValues);
        $affectedRows = $this->m_dbAdapter->insert($table, $bind);
        return array('count' => $affectedRows, '_id' => $insertDefaultValues['_id']);
    }

	/**
	 * Override: update
	 *
	 * @param mixed $table The table to insert data into.
	 * @param array $bind Column-value pairs.
	 * @param mixes $where update where clause
	 * @param string $userId
	 * @return integer $affectedRows Number of affected rows
	 */
	public function update($table, $bind, $where, $userId = '')
	{
		$updateDefaultValues = $this->_getUpdateDefaultData($userId);
		$bind = array_merge($bind, $updateDefaultValues);
		$affectedRows = $this->m_dbAdapter->update($table, $bind, $where);
		return $affectedRows;
	}


	/**
	 * Override: delete
	 *
	 * @param mixed $table The table to insert data into.
	 * @param mixes $where update where clause
	 * @return integer $affectedRows Number of affected rows
	 */
	public function delete($table, $where)
	{
		$affectedRows = $this->m_dbAdapter->delete($table, $where);
		return $affectedRows;
	}


    /**
    * Prepares and executes an SQL statement with bound data.
    *
    * @param mixed $sql The SQL statement with placeholders.
    *                        May be a string or Zend_Db_Select.
    * @param mixed $bind An array of data to bind to the placeholders.
    * @return Zend_Db_Statement_Interface
    */
    public function query($sql, $bind = array())
    {
        return $this->m_dbAdapter->query($sql, $bind);
    }

	public function select(){
		return $this->m_dbAdapter->select();
	}

    public function beginTransaction(){
        return $this->m_dbAdapter->beginTransaction();
    }

    public function commit(){
        return $this->m_dbAdapter->commit();
    }

    public function rollBack(){
        return $this->m_dbAdapter->rollBack();
    }

    private function _getInsertDefaultData($table, $userId)
    {
        $creaUser = $userId;
        $updaUser = $userId;
        $result[0] = 1;
        while (count($result) > 0) {
            $Id = md5(uniqid(rand(), true));
            $select = $this->m_dbAdapter->select()->from ($table)->where("_id = '$Id'");
            $stmt = $this->m_dbAdapter->query($select);
            $result = $stmt->fetchAll();
        }
        $creaDate = Zend_Date::now()->toString('YYYY-MM-dd HH:mm:ss');
        $updaDate = $creaDate;
        return array ('_id' => $Id,
            '_insertDate' => $creaDate,
            '_insertUser' => $creaUser,
            '_updateDate' => $updaDate,
            '_updateUser' => $updaUser);
    }


	private function _getUpdateDefaultData($userId)
	{
		$updaUser = $userId;
		$updaDate = Zend_Date::now()->toString('YYYY-MM-dd HH:mm:ss');
		return array ('_updateDate' => $updaDate,
		    '_updateUser' => $updaUser);
	}

}

?>