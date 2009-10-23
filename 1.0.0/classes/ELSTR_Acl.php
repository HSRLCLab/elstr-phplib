<?php

/**
 * This class extendes the Zend_Acl by a db loader to reviec ACL data from ELSTR DB
 * 
 * @author Felix Nyffenegger
 * @version 1.0
 * @created 21-Okt-2009 14:27:39
 */

class ELSTR_Acl extends Zend_Acl
{

	function loadFromDB()
	{
		//TODO: Here, load the ACL from Database
		
		// [DEBUG] For now, just add some dummy data			
		$this->addRole(new Zend_Acl_Role('role_guest'))
		    ->addRole(new Zend_Acl_Role('role_member'), 'role_guest')
		    ->addRole(new Zend_Acl_Role('role_admin'));
		
		//create admin and guest user			
		$this->addRole(new Zend_Acl_Role('guest'), 'role_guest');
		$this->addRole(new Zend_Acl_Role('member'), 'role_member');
		$this->addRole(new Zend_Acl_Role('admin'), 'role_admin');
		
		//add ressources
		$this->add(new Zend_Acl_Resource('EXAMPLE_Application_YAHOO'));
		$this->add(new Zend_Acl_Resource('EXAMPLE_Service_YQL'));
		$this->add(new Zend_Acl_Resource('EXAMPLE_Service_YQL_pizzaService'));
				
		//set rights
		$this->deny('role_guest', 'EXAMPLE_Application_YAHOO');
		$this->allow('role_member', 'EXAMPLE_Application_YAHOO');
		$this->allow('role_member', 'EXAMPLE_Service_YQL');
		$this->deny('role_member', 'EXAMPLE_Service_YQL_pizzaService');
		$this->allow('role_admin');			
	}

}
?>