<?php

namespace Users\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\Sql\Expression;
use Zend\InputFilte\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class UserImagesTable extends AbstractTableGateway implements AdapterAwareInterface
{
	protected $table = "user_images";

	public function setDbAdapter(Adapter $adapter)
	{
		$this->adapter = $adapter;
		$this->initialize();
	}

	public function getByUserId($userId)
	{
		$select = $this->sql->select()->where(array('user_id' => $userId))->order('created_at DESC');
		return $this->selectWith($select);
	}

	public function getByFilename($filename)
	{
		$rowset = $this->select(array('filename' => $filename));
		return $rowset->current();
	}

	public function getById($id)
	{
		$rowset = $this->select(array('id' => $id));
		return $rowset->current();
	}

	public function create($userId, $filename)
	{
		return $this->insert(array(
			'user_id' => $userId,
			'filename' => $filename,
			'created_at' => new Expression('NOW()'),
			'updated_at' => null,
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
				array('name' => 'Int'),
			),
			'validators' => array(
				array('name' => 'NotEmpty'),
				array('name' => 'Digits'),
				array(
					'name' => 'Zend\Validator\Db\RecordExists',
					'options' => array(
						'table' => 'users',
						'field' => 'id',
						'adapter' => $this->adapter,
					)
				)
			)
		)));

		return $inputFilter;
	}
}