<?php

namespace Wall\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractRestfulController
{
	protected $usersTable;

	protected $userStatusesTable;
	
	protected $userImagesTable;

	public function get($username)
	{
		$userTable = $this->getUsersTable();
		$userStatusesTable = $this->getUserStatusesTable();
		$userImagesTable = $this->getUserImagesTable();

		$userData = $usersTable->getByUsername($username);
		$userStatuses = $userStatusesTable->getByUserId(
			$userData->id,
		)->toArray();
		$userImages = $userImagesTable->getByUser($userData->id)->toArray();

		$wallData = $userData->getArrayCopy();
		$wallData['feed'] = array_merge($userStatuses, $userImages);

		usort($wallData['feed'], function($a, $d){
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

	public function create($data)
	{
		if(array_key_exists('status', $data) && !empty($data['status'])) {
			$result = $this->createStatus($data);
		}

		if(array_key_exists('image', $data) && !empty($data['image'])) {
			$result = $this->createImage($data);
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

	public function createImage($data)
	{
		$userImagesTable = $this->getUserImagesTable();
		$filters = $userImagesTable->getInputFilter();
		$filters->setData($data);

		if($filters->isValid()) {
			$filename = sprintf(
				'public/images/%s.png',
				sha1(uniqid(time(), true))
			);
			$content = base64_decode($data['image']);
			$image = imagecreatefromstring($content);
			
			if(imagepng($image, $filename) === true) {
				$result = new JsonModel(array(
					'result' => $userImagesTable->create(
						$data['user_id'],
						basename($filename)
					)
				));
			} else {
				$result = new JsonModel(array(
					'result' => false,
					'errors' => 'Erro ao armazenar a imagem',
				));
			}
			imagedestroy($image);
		} else {
			$result = new JsonModel(array(
				'result' => false,
				'errors' => $filters->getMessages(),
			));
		}
	}

	public function getUserImagesTable() 
	{
		if(!$this->userImagesTable) {
			$sm = $this->getServiceLocator();
			$this->userImagesTable = $sm->get('Users\Model\UserImagesTable');
		}
		return $this->userImagesTable;
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
