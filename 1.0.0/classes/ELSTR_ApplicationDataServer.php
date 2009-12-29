<?php

require_once ('ELSTR_JsonServer.php');

/**
* Application Data Server
*
* @version $Id$
* @copyright 2009
*/

class ELSTR_ApplicationDataServer {
    private $m_application;

    function __construct($application)
    {
        $this->m_application = $application;
    }

    /**
    * Create a JSON Server and handle itselfs
    *
    * @return void
    */
    public function handle()
    {
        $server = new ELSTR_JsonServer();
        $server->setClass($this);
        $server->handle();
    }

    /**
    * Get the preview for an item or document or project
    *
    * @param string $appNam Name of the application
    * @return array
    */
    public function load($appName)
    {
        $configPublic = $this->m_application->getOption("public");

        if (isset($configPublic[$appName])) {
            $appConfigPublic = array_merge($configPublic['shared'], $configPublic[$appName]);
        } else {
            $appConfigPublic = $configPublic['shared'];
        }

        $result['config'] = $appConfigPublic;

    	$result['user']['username'] = $this->m_application->getBootstrap()->getResource('user')->getUsername();
    	$result['user']['isAuth'] = $this->m_application->getBootstrap()->getResource('auth')->hasIdentity();
		$result['user']['isAdmin'] = $this->m_application->getBootstrap()->getResource('acl')->inheritsRole($result['user']['username'],'role_admin',false);

    	$result['language']['current'] = $this->m_application->getBootstrap()->getResource("language")->getTranslation()->getLocale();
    	$result['language']['modules'] = $this->m_application->getBootstrap()->getResource("language")->getRegisteredModules();
    	$result['language']['translations'] = $this->m_application->getBootstrap()->getResource("language")->getTranslation()->getMessages();

    	return $result;
    }
}

?>