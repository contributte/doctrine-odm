<?php declare(strict_types = 1);

namespace Nettrine\ODM\DI;

use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
final class OdmAttributesExtension extends AbstractExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure(
			[
				'paths' => Expect::listOf('string'),
				'excludePaths' => Expect::listOf('string'),
			]
		);
	}

	public function loadConfiguration(): void
	{
		// Validates needed extension
		$this->validate();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$builder->addDefinition($this->prefix('attributeDriver'))
			->setType(MappingDriver::class)
			->setFactory(AttributeDriver::class, [$config->paths])
			->addSetup('addExcludePaths', [$config->excludePaths]);

		$configurationDef = $this->getConfigurationDef();
		$configurationDef->addSetup('setMetadataDriverImpl', [$this->prefix('@attributeDriver')]);
	}

}
