<?php
	require_once ('ELSTR_EnterpriseApplication_Abstract.php');
	require_once ('Zend/Session.php');
	
	/**
	 * This is the abstract class every WidgetServer must implement.
	 * Note: $acl and $user are optional, but must be set if one of the applications needs ACL control.
	 * 
	 * These methods must be implemented:
	 * _initApplications($acl, $user) : Tell the WidgetServer which applications to use with $this->registerApplication()
	 * 
	 * @author Felix Nyffenegger
	 * @version 1.0
	 * @created 19-Okt-2009 17:41:37
	 */
	abstract class ELSTR_WidgetServer_Abstract
	{
		protected $m_applications;
		
		function __construct($acl = null, $user = null) {
			$this->m_applications = array();
			$this->_initApplications($acl, $user);
		}
	
		/**
		 * The implementation class must implement this method in order
		 * to add all the applications needed to the $m_applications array
		 * [OPTIPON] This could later be replaced by pure configuration
		 */
		abstract protected function _initApplications($acl, $user);
		
		/**
		 * Register an application for this WidgetServer
		 * Carefull: yet, only one instance of an application can be registered at a time
		 * 
		 * @param $application ELSTR_EnterpriseApplication_Abstract
		 * @return void 
		 */
		protected function registerApplication($application) {
			$this->m_applications[get_class($application)] = $application;
		}
		
		/**
		 * Get a registered servcie
		 * 
		 * @param $name String
		 * @return ELSTR_Service_Abstract
		 */
		protected function getApplication($name) {
			if (array_key_exists($name, $this->m_applications)) {
	            return $this->m_applications[$name];
	        }
	        return false;
		}
		
		/**
		 * Remove a service from the application
		 * 
		 * @param $service ELSTR_Service_Abstract
		 * @return void
		 */
		protected function unregisterApplication($application) {
			unset($this->m_applications[get_class($application)]);
		}
	}
?>