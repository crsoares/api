<?php

namespace Users\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Sql\Expression;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Users\Validator\Url;

class UserLinksTable extends AbstractTableGateway implements AdapterAwareInterface
{
	protected $table = "user_links";

	public function setDbAdapter(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->initialize();
	}

	public function getByUserId($userId)
	{
		$select = $this->sql()->select()->where(array('user_id' => $userId))->order('created_at DESC');
		return $this->selectWith($select);
	}



	public function create($userId, $url, $title)
	{
		return $this->insert(array(
			'user_id' => $userId,
			'url' => $url,
			'title' => $title,
			'created_at' => new Expression('NOW()'),
			'updated_at' => null
		));
	}

	public function getInputFilter()
	{
		$inputFilter = new InputFilter();
		$factory = new InputFactory();

		$inputFilter->add($factory->createInput(array(
			'name' => 'user_id',
			'required' => true,
			'filters' => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
				array('name' => 'Int')
			),
			'validators' => array(
				array('name' => 'NotEmpty'),
				array('name' => 'Digits'),
				array(
					'name' => 'Zend\Validator\Db\RecordExists',
					'options' => array(
						'table' => 'users',
						'field' => 'id',
						'adapter' => $this->adapter
					)
				)
			)
		)));

		$inputFilter->add($factory->createInput(array(
			'name' => 'url',
			'required' => true,
			'filters' => array(
				array('name' => 'StripTags'),
				array('name' => 'StringTrim')
			),
			'validators' => array(
				array('name' => 'NotEmpty'),
				array(
					'name' => 'StringLength',
					'options' => array(
						'max' => 2048
					)
				),
				array('name' => '\Users\Validator\Url');
			)
		)));

		return $inputFilter;
	}
}