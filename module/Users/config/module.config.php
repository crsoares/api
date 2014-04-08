<?php

namespace Users;

return array(
	'di' => array(
		'services' => array(
			'Users\Model\UsersTable' => 'Users\Model\UsersTable',
			'Users\Model\UserStatusesTable' => 'Users\Model\UserStatusesTable',
			'Users\Model\UserImagesTable' => 'Users\Model\UserImagesTable'
		)
	)
);