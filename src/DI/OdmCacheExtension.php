<?php declare(strict_types = 1);

namespace Nettrine\ODM\DI;

use Doctrine\Common\Cache\Cache;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nettrine\ODM\DI\Helpers\SmartStatement;
use stdClass;

/**
 * @property-read stdClass $config
 */
final class OdmCacheExtension extends AbstractExtension
{

	private Definition|string|null $defaultDriverDef = null;

	public function getConfigSchema(): Schema
	{
		return Expect::structure(
			[
				'defaultDriver' => $this->getServiceSchema(),
				'metadataCache' => $this->getServiceSchema(),
			]
		);
	}

	public function loadConfiguration(): void
	{
		// Validates needed extension
		$this->validate();

		$this->loadMetadataCacheConfiguration();
	}

	private function getServiceSchema(): Schema
	{
		return Expect::anyOf(
			Expect::string(),
			Expect::type(Statement::class)
		)->nullable();
	}

	private function loadMetadataCacheConfiguration(): void
	{
		$config = $this->config;
		$configurationDef = $this->getConfigurationDef();

		$configurationDef->addSetup(
			'setMetadataCacheImpl',
			[
				$this->loadSpecificDriver($config->metadataCache, 'metadataCache'),
			]
		);
	}

	private function loadSpecificDriver(string|Statement|null $config, string $prefix): Definition|string
	{
		if ($config !== null) {
			$builder = $this->getContainerBuilder();
			$driverDef = $builder->addDefinition($this->prefix($prefix));
			$driverDef->setFactory(SmartStatement::from($config));
			$driverDef->setAutowired(false);

			return $driverDef;
		}

		return $this->loadDefaultDriver();
	}

	private function loadDefaultDriver(): Definition|string
	{
		if ($this->defaultDriverDef !== null) {
			return $this->defaultDriverDef;
		}

		$config = $this->config;

		if ($config->defaultDriver === null) {
			return $this->defaultDriverDef = '@' . Cache::class;
		}

		$builder = $this->getContainerBuilder();
		$defaultDriverDef = $builder->addDefinition($this->prefix('defaultCache'));
		$defaultDriverDef->setFactory(SmartStatement::from($config->defaultDriver));
		$defaultDriverDef->setAutowired(false);

		return $this->defaultDriverDef = $defaultDriverDef;
	}

}
