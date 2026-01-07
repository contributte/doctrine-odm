<?php declare(strict_types = 1);

namespace Nettrine\ODM\DI\Helpers;

use Nette\DI\Definitions\Statement;
use Nettrine\ODM\Exception\Logical\InvalidArgumentException;

final class SmartStatement
{

	public static function from(mixed $service): Statement
	{
		if (is_string($service)) {
			return new Statement($service);
		}

		if ($service instanceof Statement) {
			return $service;
		}

		throw new InvalidArgumentException('Unsupported type of service');
	}

}
