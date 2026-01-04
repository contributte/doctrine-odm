<?php declare(strict_types = 1);

namespace Nettrine\ODM\DI;

use Doctrine\Common\Cache\Cache;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
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
			Expect::array(),
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

	/**
	 * @param string|mixed[]|Statement|null $config
	 */
	private function loadSpecificDriver(string|array|Statement|null $config, string $prefix): Definition|string
	{
		if ($config !== null && $config !== []) { // Nette converts explicit null to an empty array
			$driverName = $this->prefix($prefix);
			$driverDef = $this->getHelper()->getDefinitionFromConfig($config, $driverName);

			// If service is extension specific, then disable autowiring
			if ($driverDef instanceof Definition && $driverDef->getName() === $driverName) {
				$driverDef->setAutowired(false);
			}

			return $driverDef;
		}

		return $this->loadDefaultDriver();
	}

	private function loadDefaultDriver(): Definition|string
	{
		$config = $this->config;

		if ($this->defaultDriverDef !== null) {
			return $this->defaultDriverDef;
		}

		if ($config->defaultDriver === null || $config->defaultDriver === []) { // Nette converts explicit null to an empty array
			return $this->defaultDriverDef = '@' . Cache::class;
		}

		$defaultDriverName = $this->prefix('defaultCache');
		$this->defaultDriverDef = $defaultDriverDef = $this->getHelper()->getDefinitionFromConfig(
			$config->defaultDriver,
			$defaultDriverName
		);

		// If service is extension specific, then disable autowiring
		if ($defaultDriverDef instanceof Definition && $defaultDriverDef->getName() === $defaultDriverName) {
			$defaultDriverDef->setAutowired(false);
		}

		return $defaultDriverDef;
	}

}
