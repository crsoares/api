<?php

namespace Wall\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use Zend\Filter\FilterChain;
use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewLines;
use Zend\Dom\Query;

class IndexController extends AbstractRestfulController
{
	protected $usersTable;

	protected $userStatusesTable;
	
	protected $userImagesTable;

	protected $userLinksTable;

	public function get($username)
	{
		$usersTable = $this->getUsersTable();
		$userStatusesTable = $this->getUserStatusesTable();
		$userImagesTable = $this->getUserImagesTable();
		$userLinksTable = $this->getUserLinksTable();

		$userData = $usersTable->getByUsername($username);
		$userStatuses = $userStatusesTable->getByUserId($userData->id)->toArray();
		$userImages = $userImagesTable->getByUserId($userData->id)->toArray();
		$userLinks = $userLinksTable->getByUserId($userData->id)->toArray();

		$wallData = $userData->getArrayCopy();
		$wallData['feed'] = array_merge($userStatuses, $userImages, $userLinks);

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

	}

	public function create($data)
	{
		if(array_key_exists('status', $data) && !empty($data['status'])) {
			$result = $this->createStatus($data);
		}

		if(array_key_exists('image', $data) && !empty($data['image'])) {
			$result = $this->createImage($data);
		}

		if(array_key_exists('url', $data) && !empty($data['url'])) {
			$result = $this->createLink($data);
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

	protected function createImage($data)
	{
		$userImagesTable = $this->getUserImagesTable();
		$filters = $userImagesTable->getInputFilter();
		$filters->setData($data);

		if($filters->isValid()) {
			$filename = sprintf('public/images/%s.png',sha1(uniqid(time(), true)));
			$content = base64_decode($data['image']);
			$image = imagecreatefromstring($content);
			
			if(imagepng($image, $filename) === true) {
				$result = new JsonModel(array(
					'result' => $userImagesTable->create($data['user_id'],basename($filename))
				));
			} else {
				$result = new JsonModel(array(
					'result' => false,
					'errors' => 'Erro ao armazenar a imagem'
				));
			}
			imagedestroy($image);
		} else {
			$result = new JsonModel(array(
				'result' => false,
				'errors' => $filters->getMessages()
			));
		}
		return $result;
	} 

	protected function createStatus($data)
	{
		$userStatusesTable = $this->getUserStatusesTable();
		
		$filtes = $userStatusesTable->getInputFilter();
		$filtes->setData($data);

		if($filtes->isValid()) {
			$data = $filtes->getValues();
			
			$result = new JsonModel(array(
				'result' => $userStatusesTable->create($data['user_id'], $data['status']),
			));
		} else {
			$result = new JsonModel(array(
				'result' => false,
				'errors' => $filters->getMessages(),
			));
		}
		return $result;
	}

	protected function createLink($data)
	{
		$userLinksTable = $this->getUserLinksTable();

		$filters = $userLinksTable->getInputFilter();
		$filters->setData($data);

		if($filters->isValid()) {
			$data = $filters->getValues();

			$client = new Client($data['url']);
			$client->setEncType(Client::ENC_URLENCODED);
			$client->setMethod(\Zend\Http\Request::METHOD_GET);
			$response = $client->send();

			if($response->isSuccess()) {
				$html = $response->getBody();
				$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

				$dom = new Query($html);
				$title = $dom->execute('title')->current()->nodeValue;

				if(!empty($title)) {
					$filterChain = new FilterChain();
					$filterChain->attach(new StripTags());
					$filterChain->attach(new StringTrim());
					$filterChain->attach(new StripNewLines());

					$title = $filterChain->filter($title);
				} else {
					$title = null;
				}

				return new JsonModel(array(
					'result' => $userLinksTable->create($data['user_id'], $data['url'], $title)
				));
			}
		}

		return new JsonModel(array(
			'result' => false,
			'errors' => $filters->getMessages()
		));
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

	protected function getUserLinksTable()
	{
		if(!$this->userLinksTable()) {
			$sm = $this->getServiceLocator();
			$this->userLinksTable = $sm->get('Users\Model\UserLinksTable');
		}
		return $this->userLinksTable;
	}
}
