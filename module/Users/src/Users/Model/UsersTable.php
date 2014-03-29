<?php

namespace Users\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\AdapterAwareInterface;

class UsersTable extends AbstractTableGateway implements AdapterAwareInterface
{
	protected $table = "users";

	public function setDbAdapter(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->initialize();
	}

	public function getByUsername($username)
	{
		$rowset = $this->select(array('username' => $username));

		return $rowset->current();
	}
}