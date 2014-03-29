<?php

namespace Wall;

return array(
	'router' => array(
		'routes' => array(
			'wall' => array(
				'type' => 'Zend\Mvc\Router\Http\Segment',
				'options' => array(
					'route' => '/api/wall/[/:id]',
					'constraints' => array(
						'id' => '\w+'
					),
					'defaults' => array(
						'controller' => 'Wall\Controller\Index',
					)
				)
			)
		)
	),
	'controllers' => array(
		'invokables' => array(
			'Wall\Controller\Index' => 'Wall\Controller\IndexController',
		)
	)
);