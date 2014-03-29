<?php

namespace Wall\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractRestfulController
{
	protected $usersTable;

	public function get($username) 
	{
		$usersTable = $this->getUsersTable();
		$userData = $usersTable->getByUsername($username);

		if($userData !== false) {
			return new JsonModel($userData->getArrayCopy());
		} else {
			throw new \Exception('Usuário não encontrado', 404);
		}
	}

	public function getList() 
	{
		$this->methodNotAllowed();
	}

	public function create($data)
	{
		$this->methodNotAllowed();
	}

	public function update($id, $data)
	{
		$this->methodNotAllowed();
	}

	public function delete($id)
	{
		$this->methodNotAllowed();
	}

	protected function methodNotAllowed()
	{
		$this->response->setStatusCode(
			\Zend\Http\PhpEnvironment\Response::STATUS_CODE_405
		);
	}

	protected function getUsersTable()
	{
		if(!$this->usersTable) {
			$sm = $this->getServiceLocator();
			$this->usersTable = $sm->get('Users\Model\UsersTable');
		}
		return $this->usersTable;
	}
}