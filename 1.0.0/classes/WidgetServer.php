<?php
require_once ('ELSTR_JsonServer.php');
require_once ('..\..\..\..\..\..\Temp\ELSTR_Application.php');
require_once ('..\..\..\zf\1.8.2\Zend\Session.php');
require_once ('ArticleWidgetServer.php');

/**
 * @author egli
 * @version 1.0
 * @created 19-Okt-2009 17:14:59
 */
class ELSTR_WidgetServer extends ExamleWidgetServer
{

	var $m_ELSTR_JsonServer;
	var $m_ELSTR_EnterpriseApplication;
	var $m_Zend_Session;

	/**
	 * Load all Applications into Applications Array
	 */
	function ELSTR_WidgetServer()
	{
	}

}
?>