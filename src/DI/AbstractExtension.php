<?php declare(strict_types = 1);

namespace Nettrine\ODM\DI;

use Doctrine\ODM\MongoDB\Configuration;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nettrine\ODM\DI\Helpers\SmartStatement;
use Nettrine\ODM\Exception\Logical\InvalidStateException;
use stdClass;

/**
 * @property-read stdClass $config
 */
abstract class AbstractExtension extends CompilerExtension
{

	public function validate(): void
	{
		if ($this->compiler->getExtensions(OdmExtension::class) === []) {
			throw new InvalidStateException(
				sprintf('You should register %s before %s.', OdmExtension::class, static::class)
			);
		}
	}

	protected function getConfigurationDef(): ServiceDefinition
	{
		/** @var ServiceDefinition $def */
		$def = $this->getContainerBuilder()->getDefinitionByType(Configuration::class);

		return $def;
	}

	protected function getDefinitionFromConfig(string|Statement $config, string $name): Definition|string
	{
		// Reference to existing service
		if (is_string($config) && str_starts_with($config, '@')) {
			return $config;
		}

		// Class name or Statement
		$statement = SmartStatement::from($config);

		$def = $this->getContainerBuilder()->addDefinition($name);
		$def->setFactory($statement);

		return $def;
	}

}
