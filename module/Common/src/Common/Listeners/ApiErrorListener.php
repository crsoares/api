<?php

namespace Common\Listeners;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;

class ApiErrorListener extends AbstractListenerAggregate
{
	public function attach(EventManagerInterface $events)
	{
		$this->listeners[] = $events->attach(
								MvcEvent::EVENT_RENDER,
								'ApiErrorListener::onRender',
								1000
							 );
	}

	public static function onRender(MvcEvent $e)
	{
		if($e->getResponse()->isOk()) {
			return;
		}

		$httpCode = $e->getResponse()->getStatusCode();
		$sm = $e->getApplication()->getServiceManager();
		$viewModel = $e->getResult();
		$exception = $viewModel->getVariable('exception');

		$model = new JsonModel(array(
			'errorCode' => $exception->getCode() ?: $httpCode,
			'errorMsg' => $exception->getMessage(),
		));
		$model->setTerminal(true);

		$e->setResult($model);
		$e->setViewModel($model);
		$e->getResponse()->setStatusCode($httpCode);
	}
}