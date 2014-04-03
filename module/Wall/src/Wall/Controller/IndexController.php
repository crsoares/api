<?php

namespace Wall\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractRestfulController
{
	protected $usersTable;

	protected $userStatusesTable;

	public function get($username) 
	{
		$usersTable = $this->getUsersTable();
		$userStatusesTable = $this->getUserStatusesTable();

		$userData = $usersTable->getByUsername($username);
		$userStatuses = $userStatusesTable->getByUserId($userData->id)->toArray();

		$wallData = $userData->getArrayCopy();
		$wallData['feed'] = $userStatuses;

		usort($wallData['feed'], function($a, $b){
			$timestampA = strtotime($a['created_at']);
			$timestampB = strtotime($b['created_at']);

			if($timestampA == $timestampB) {
				return 0;
			}

			return ($timestampA > $timestampB) ? -1 : 1;
		});

		if($userData !== false) {
			return new JsonModel($wallData);
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
		$userStatusesTable = $this->getUserStatusesTable();

		$filters = $userStatusesTable->getInputFilter();
		$filters->setData($data);

		if($filters->isValid()) {
			$data = $filters->getValues();

			$result = new JsonModel(array(
				'result' => $userStatusesTable->create(
					$data['user_id'], $data['status']
				)
			));
		} else {
			$result = new JsonModel(array(
				'result' => false,
				'errors' => $filters->getMessages()
			));
		}

		return $result;
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

	protected function getUserStatusesTable()
	{
		if(!$this->userStatusesTable) {
			$sm = $this->getServiceLocator();
			$this->userStatusesTable = $sm->get('Users\Model\UserStatusesTable');
		}

		return $this->userStatusesTable;
	}
}