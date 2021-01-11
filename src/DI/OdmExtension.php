<?php declare(strict_types = 1);

namespace Nettrine\ODM\DI;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Client;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\DI\Helpers;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nettrine\ODM\Exception\Logical\InvalidArgumentException;
use stdClass;

/**
 * @property-read stdClass $config
 */
final class OdmExtension extends AbstractExtension
{

	public function getConfigSchema(): Schema
	{
		$parameters = $this->getContainerBuilder()->parameters;
		$proxyDir = isset($parameters['tempDir']) ? $parameters['tempDir'] . '/cache/doctrine.odm.proxy' : null;
		$hydratorDir = isset($parameters['tempDir']) ? $parameters['tempDir'] . '/cache/doctrine.odm.hydrator' : null;

		return Expect::structure(
			[
				'configurationClass' => Expect::string(Configuration::class),
				'configuration' => Expect::structure(
					[
						'autoGenerateProxyClasses' => Expect::anyOf(
							Expect::int(),
							Expect::bool(),
							Expect::type(Statement::class)
						)->default(Configuration::AUTOGENERATE_FILE_NOT_EXISTS),
						'defaultDB' => Expect::string('example'),
						'hydratorDir' => Expect::string($hydratorDir)->nullable(),
						'hydratorNamespace' => Expect::string('Nettrine\ODM\Hydrator')->nullable(),
						'proxyDir' => Expect::string($proxyDir)->nullable(),
						'proxyNamespace' => Expect::string('Nettrine\ODM\Proxy')->nullable(),
						'metadataDriverImpl' => Expect::string(),
						'classMetadataFactoryName' => Expect::string(),
						'repositoryFactory' => Expect::string(),
					]
				),
				'types' => Expect::array(),
			]
		);
	}

	public function loadConfiguration(): void
	{
		$this->loadDoctrineConfiguration();
		$this->loadEntityManagerConfiguration();
	}

	public function loadDoctrineConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$globalConfig = $this->config;
		$config = $globalConfig->configuration;

		// @validate configuration class is subclass of origin one
		$configurationClass = $globalConfig->configurationClass;
		assert(is_string($configurationClass));
		if (!is_a($configurationClass, Configuration::class, true)) {
			throw new InvalidArgumentException(
				'Configuration class must be subclass of ' . Configuration::class . ', ' . $configurationClass . ' given.'
			);
		}

		$configuration = $builder->addDefinition($this->prefix('configuration'))
			->setType($configurationClass);

		if (is_bool($config->autoGenerateProxyClasses)) {
			$configuration->addSetup(
				'setAutoGenerateProxyClasses',
				[
					$config->autoGenerateProxyClasses === true ? Configuration::AUTOGENERATE_FILE_NOT_EXISTS : Configuration::AUTOGENERATE_NEVER,
				]
			);
		} elseif (is_int($config->autoGenerateProxyClasses)) {
			$configuration->addSetup('setAutoGenerateProxyClasses', [$config->autoGenerateProxyClasses]);
		}

		if ($config->defaultDB !== null) {
			$configuration->addSetup('setDefaultDB', [$config->defaultDB]);
		}

		if ($config->hydratorDir !== null) {
			$configuration->addSetup('setHydratorDir', [Helpers::expand($config->hydratorDir, $builder->parameters)]);
		}

		if ($config->hydratorNamespace !== null) {
			$configuration->addSetup('setHydratorNamespace', [$config->hydratorNamespace]);
		}

		if ($config->proxyDir !== null) {
			$configuration->addSetup('setProxyDir', [Helpers::expand($config->proxyDir, $builder->parameters)]);
		}

		if ($config->proxyNamespace !== null) {
			$configuration->addSetup('setProxyNamespace', [$config->proxyNamespace]);
		}

		if ($config->metadataDriverImpl !== null) {
			$configuration->addSetup('setMetadataDriverImpl', [$config->metadataDriverImpl]);
		}

		if ($config->classMetadataFactoryName !== null) {
			$configuration->addSetup('setClassMetadataFactoryName', [$config->classMetadataFactoryName]);
		}

		if ($config->repositoryFactory !== null) {
			$configuration->addSetup('setRepositoryFactory', [$config->repositoryFactory]);
		}

		/** @var ServiceDefinition $client */
		$client = $builder->getDefinitionByType(Client::class);
		$client->getFactory()->arguments[2] += ['typeMap' => DocumentManager::CLIENT_TYPEMAP];

		$types = $this->config->types;
		foreach ($types as $name => $class) {
			$configuration
				->addSetup(
					'\Doctrine\ODM\MongoDB\Types\Type::registerType(?, ?)',
					[$name, $class]
				);
		}
	}

	public function loadEntityManagerConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		// Document Manager
		$builder->addDefinition($this->prefix('documentManager'))
			->setType(DocumentManager::class)
			->setFactory(
				DocumentManager::class . '::create',
				[
						$builder->getDefinitionByType(Client::class),
						$this->prefix('@configuration'),
					]
			);
	}

}
