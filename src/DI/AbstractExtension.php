<?php declare(strict_types = 1);

namespace Nettrine\ODM\DI;

use Doctrine\ODM\MongoDB\Configuration;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
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

	/**
	 * @param string|mixed[]|Statement $config
	 */
	protected function getDefinitionFromConfig(string|array|Statement $config, string $name): Definition|string
	{
		if (is_string($config) && str_starts_with($config, '@')) {
			return $config;
		}

		if (is_string($config)) {
			$config = new Statement($config);
		} elseif (is_array($config)) {
			/** @var string $factory */
			$factory = $config[0] ?? $config['factory'];
			/** @var mixed[] $arguments */
			$arguments = $config['arguments'] ?? [];
			$config = new Statement($factory, $arguments);
		}

		$def = $this->getContainerBuilder()->addDefinition($name);
		$def->setFactory($config->getEntity() ?? $config, $config->arguments);

		return $def;
	}

}
