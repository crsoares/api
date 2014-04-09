<?php

namespace Users\Validator;

use Zend\Validator\AbstractValidator;

class Url extends AbstractValidator
{
	const INVALID = 'urlInvalid';

	protected $messageTemplates = array(
		self::INVALID => 'Url inválido dado'
	);

	public function isValid($value)
	{
		if(!is_string($value)) {
			$this->error(self::INVALID);
			return false;
		}

		$this->setValue($value);
		if(!filter_var($value, FILTER_VALIDATE_URL)) {
			$this->error(self::INVALID);
			return false;
		}

		return true;
	}
}