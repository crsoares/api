<?php

namespace Common;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;

class Module implements ConfigProviderInterface,
AutoloaderProviderInterface, BootstrapListenerInterface
{
	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				)
			)
		);
	}

	public function onBootstrap(EventInterface $e)
	{
		$app = $e->getTarget();
		$services = $app->getServiceManager();
		$events = $app->getEventManager();
		$events->attach(
			$services->get('Common\Listeners\ApiErrorListener')
		);
	}
}