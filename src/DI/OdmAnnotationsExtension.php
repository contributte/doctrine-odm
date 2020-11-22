<?php declare(strict_types = 1);

namespace Nettrine\ODM\DI;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
final class OdmAnnotationsExtension extends AbstractExtension
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

	/**
	 * Register services
	 */
	public function loadConfiguration(): void
	{
		// Validates needed extension
		$this->validate();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$builder->addDefinition($this->prefix('annotationDriver'))
			->setType(MappingDriver::class)
			->setFactory(AnnotationDriver::class, [$builder->getDefinitionByType(Reader::class), $config->paths])
			->addSetup('addExcludePaths', [$config->excludePaths]);

		$configurationDef = $this->getConfigurationDef();
		$configurationDef->addSetup('setMetadataDriverImpl', [$this->prefix('@annotationDriver')]);
	}

}
