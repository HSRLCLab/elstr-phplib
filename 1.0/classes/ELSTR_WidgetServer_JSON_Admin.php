<?php
require_once ('ELSTR_WidgetServer_JSON_Abstract.php');

/**
* This is the WidgetServer for administrating the elstr application
* All public actions in this class will be available for post requests
*
* @author Marco Egli
*/
class ELSTR_WidgetServer_JSON_Admin extends ELSTR_WidgetServer_JSON_Abstract {
    /**
    * Get roles from db
    *
    * @return array
    */
    public function getRoleList()
    {
        $db = $this->m_application->getBootstrap()->getResource('db')['elstr'];
		if (is_array($db)) $db = $db['elstr'];
        // Select all roles from db
        $select = $db->select();
        $select->from(array('r' => 'Role'),
        			  array('r.name','r._id'));
    	$select->order('r.name');
        $stmt = $db->query($select);
        $resultRoles = $stmt->fetchAll();

		for ($n = 0; $n < count($resultRoles); $n++) {

			$select = $db->select();

			$select->from(array('rr' => 'RoleRole'),array('rr._id1','rr._id2'));
			$select->from(array('r' => 'Role'),array('r.name','r._id'));

			$select->where('rr._id2 = ? AND rr._id1 = r._id', $resultRoles[$n]["_id"]);

			$stmt = $db->query($select);
			$resultParentRoles = $stmt->fetchAll();

			if (count($resultParentRoles)>0){
				$resultRoles[$n]["parent"] = $resultParentRoles[0]["name"];
			}
		}

        return $resultRoles;
    }

    /**
    * Get resource data and access rights for each role
    *
    * @return array
    */
    public function getResourceDataTable()
    {
        $db = $this->m_application->getBootstrap()->getResource('db')['elstr'];
		if (is_array($db)) $db = $db['elstr'];
        $acl = $this->m_application->getBootstrap()->getResource('acl');

        // Select all roles from db
        $select = $db->select();
        $select->from(array('r' => 'Role'),
        			  array('r.name'));
        $stmt = $db->query($select);
        $resultRoles = $stmt->fetchAll();

        // Select all resources from db
        $select = $db->select();
        $select->from(array('r' => 'Resource'),
                      array('r.type','r.name','r.isCore'));
        $select->order(array('r.isCore DESC','r.type', 'r.name'));
        $stmt = $db->query($select);
        $resultResources = $stmt->fetchAll();

        for ($i = 0; $i < count($resultResources); $i++) {
            // Get the right for every role
            for ($n = 0; $n < count($resultRoles); $n++) {
                $resourceName = $resultResources[$i]['name'];
                $roleName = $resultRoles[$n]['name'];

                $isAllowed = $acl->isAllowed($roleName, $resourceName);
                if ($isAllowed) {
                    $resultResources[$i][$roleName] = "allow";
                } else {
                    $resultResources[$i][$roleName] = "deny";
                }

                $resultResources[$i][$roleName."_original"] = $this->getCurrentRole($resourceName, $roleName);

            }
        }

        return $resultResources;
    }


	function getCurrentRole($resourceName, $roleName){

		$db = $this->m_application->getBootstrap()->getResource('db')['elstr'];
		if (is_array($db)) $db = $db['elstr'];

		// Select the resourceId from db
		$select = $db->select();
		$select->from(array('r' => 'Resource'),
					  array('r.name','r._id'));

		$select->where('r.name = ?', $resourceName);
		$stmt = $db->query($select);
		$resultResources = $stmt->fetchAll();
		$resourceId = $resultResources[0]['_id'];

		// Select the roleId from db
		$select = $db->select();
		$select->from(array('r' => 'Role'),
					  array('r.name','r._id'));
		$select->where('r.name = ?', $roleName);
		$stmt = $db->query($select);
		$resultRoles = $stmt->fetchAll();
		$roleId = $resultRoles[0]['_id'];

		// Select the roleId from db
		$select = $db->select();
		$select->from(array('rr' => 'RoleResource'),
					  array('rr._id1','rr._id2','rr.access'));
		$select->where('rr._id1 = ?', $roleId);
		$select->where('rr._id2 = ?', $resourceId);

		// $select->where(array ("rr._id1 = '$roleId' rr._id2 = '$resourceId'"));

		$stmt = $db->query($select);
		$result = $stmt->fetchAll();

		if ($result && count($result)>0){
			return $result[0]["access"];
		}else{
			return null;
		}


	}

    /**
    * Get the preview for an item or document or project
    *
    * @param string $resourceName
    * @param string $roleName
    * @param string $accessRight
    * @return array
    */
    public function updateAccessRight($resourceName, $roleName, $accessRight)
    {
        $result = array('action' => 'success',
            'newValue' => $accessRight);

        $db = $this->m_application->getBootstrap()->getResource('db')['elstr'];
		if (is_array($db)) $db = $db['elstr'];
        $user = $this->m_application->getBootstrap()->getResource('user');

        // Select the resourceId from db
        $select = $db->select();
		$select->from(array('r' => 'Resource'),
					  array('r.name','r._id'));

        $select->where('r.name = ?', $resourceName);
        $stmt = $db->query($select);
        $resultResources = $stmt->fetchAll();
        $resourceId = $resultResources[0]['_id'];
        
        // Select the roleId from db
        $select = $db->select();
		$select->from(array('r' => 'Role'),
					  array('r.name','r._id'));
        $select->where('r.name = ?', $roleName);
        $stmt = $db->query($select);
        $resultRoles = $stmt->fetchAll();
        $roleId = $resultRoles[0]['_id'];
        
        // Select the roleId from db
        $select = $db->select();
		$select->from(array('rr' => 'RoleResource'),
					  array('rr._id1','rr._id2','rr.isCore','rr.access'));
        $select->where('rr._id1 = ?', $roleId);
        $select->where('rr._id2 = ?', $resourceId);
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            if ($result[0]['isCore']) {

                // Core values are not allowed to update
                $result['newValue'] = $result[0]['access'];
                $result['action'] = "failure";
                throw new ELSTR_Exception('1009',1009,null,$this);

            } else {                
                if ($accessRight == "inherit"){
                	// Delete existing 
			        $deleteTableData = array ("RoleResource._id1 = '$roleId'", "RoleResource._id2 = '$resourceId'");
		            $result = $db->delete('RoleResource', $deleteTableData);
                } else {
					// Update existing
                	$updateTableData = array ('access' => $accessRight);
	                $whereCondition = array ("RoleResource._id1 = '$roleId'", "RoleResource._id2 = '$resourceId'");
	                $result = $db->update('RoleResource', $updateTableData, $whereCondition, $user->getUsername());                
                }
            }
        } else {
            // Insert new
            $insertTableData = array ('access' => $accessRight,
                '_id1' => $roleId,
                '_id2' => $resourceId);
            $result = $db->insert('RoleResource', $insertTableData, $user->getUsername());
        }

        return $result;
    }

    /**
    * Get the preview for an item or document or project
    *
    * @param string $mode
    * @param string $resourceName
    * @param string $type
    * @return array
    */
    public function updateResource($mode, $resourceName, $type)
    {
        $result = array('action' => 'success');

        $db = $this->m_application->getBootstrap()->getResource('db')['elstr'];
		if (is_array($db)) $db = $db['elstr'];
        $user = $this->m_application->getBootstrap()->getResource('user');

        if ($mode == 'add') {

            $insertTableData = array ('name' => $resourceName, 'type' => $type);
            $result = $db->insert('Resource', $insertTableData, $user->getUsername());

        }else if ($mode == 'delete') {

            $select = $db->select();
            $select->from('Resource');
            $select->where('Resource.name = ?', $resourceName);
            $stmt = $db->query($select);
            $resultResources = $stmt->fetchAll();
            for ($i = 0; $i < count($resultResources); $i++) {
                if ($resultResources[$i]['isCore']) {
                    // Core values are not allowed to update
                    $result['action'] = "failure";
                    throw new ELSTR_Exception('1009',1009,null,$this);
                } else {
                    $resourceId = $resultResources[$i]['_id'];
                    $db->delete("RoleResource", "RoleResource._id2 = '$resourceId'");
                    $db->delete("Resource", "Resource._id = '$resourceId'");
                }
            }

        }else{

        	$result['action'] = "failure";
        	throw new ELSTR_Exception('Unknown mode provided');

        }
        return $result;
    }

    /**
    * Get the preview for an item or document or project
    *
    * @param string $mode
    * @param string $roleName
    * @return array
    */
    public function updateRole($mode, $roleName)
    {
        $result = array('action' => 'success');

        $db = $this->m_application->getBootstrap()->getResource('db')['elstr'];
		if (is_array($db)) $db = $db['elstr'];
        $user = $this->m_application->getBootstrap()->getResource('user');

        if ($mode == 'add') {

            $insertTableData = array ('name' => $roleName);
            $result = $db->insert('Role', $insertTableData, $user->getUsername());
            $roleAddedId = $result['_id'];
            // Add newly added role to role anonymous
            // Select the roleId from db
            $select = $db->select();
            $select->from('Role');
            $select->where('Role.name = ?', 'role_anonymous');
            $stmt = $db->query($select);
            $resultRoles = $stmt->fetchAll();
            $roleAnonymousId = $resultRoles[0]['_id'];

            $insertTableData = array ('_id1' => $roleAnonymousId,
                '_id2' => $roleAddedId);
            $result = $db->insert('RoleRole', $insertTableData, $user->getUsername());

        }else if ($mode == 'delete') {

            $select = $db->select();
            $select->from('Role');
            $select->where('Role.name = ?', $roleName);
            $stmt = $db->query($select);
            $resultRoles = $stmt->fetchAll();
            for ($i = 0; $i < count($resultRoles); $i++) {
                if ($resultRoles[$i]['isCore']) {
                    // Core values are not allowed to update
                    $result['action'] = "failure";
                    throw new ELSTR_Exception('1009',1009,null,$this);
                } else {
                    $roleId = $resultRoles[$i]['_id'];
                    $db->delete("RoleResource", "RoleResource._id1 = '$roleId'");
                    $db->delete("RoleRole", "RoleRole._id1 = '$roleId'");
                    $db->delete("RoleRole", "RoleRole._id2 = '$roleId'");
                    $db->delete("Role", "Role._id = '$roleId'");
                }
            }

        }else{

			$result['action'] = "failure";
			throw new ELSTR_Exception('Unknown mode provided');

	 	}
        return $result;
    }

    /**
    * This method must be implemented to initialize the applications
    *
    * @return
    */
	protected function _initEnterpriseApplications()
	{
		// No enterprise application
	}


}

?>