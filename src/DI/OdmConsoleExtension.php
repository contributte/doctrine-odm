<?php declare(strict_types = 1);

namespace Nettrine\ODM\DI;

use Doctrine\ODM\MongoDB\Tools\Console\Command\ClearCache\MetadataCommand;
use Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateHydratorsCommand;
use Doctrine\ODM\MongoDB\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ODM\MongoDB\Tools\Console\Command\QueryCommand;
use Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\CreateCommand;
use Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\DropCommand;
use Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\UpdateCommand;
use Doctrine\ODM\MongoDB\Tools\Console\Helper\DocumentManagerHelper;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\DI\ServiceCreationException;
use Symfony\Component\Console\Application;

final class OdmConsoleExtension extends AbstractExtension
{

	/** @var bool */
	private $cliMode;

	public function __construct(?bool $cliMode = null)
	{
		$this->cliMode = $cliMode ?? PHP_SAPI === 'cli';
	}


	public function loadConfiguration(): void
	{
		// Validates needed extension
		$this->validate();

		if (!class_exists(Application::class)) {
			throw new ServiceCreationException(sprintf('Missing %s service', Application::class));
		}

		// Skip if it's not CLI mode
		if (!$this->cliMode) {
			return;
		}

		$builder = $this->getContainerBuilder();

		// Helpers
		$builder->addDefinition($this->prefix('documentManagerHelper'))
			->setType(DocumentManagerHelper::class)
			->setAutowired(false);

		// Commands
		$builder->addDefinition($this->prefix('schemaToolCreateCommand'))
			->setType(CreateCommand::class)
			->addTag('console.command', 'odm:schema:create')
			->setAutowired(false);

		$builder->addDefinition($this->prefix('schemaToolUpdateCommand'))
			->setType(UpdateCommand::class)
			->addTag('console.command', 'odm:schema:update')
			->setAutowired(false);

		$builder->addDefinition($this->prefix('schemaToolDropCommand'))
			->setType(DropCommand::class)
			->addTag('console.command', 'odm:schema:drop')
			->setAutowired(false);

		$builder->addDefinition($this->prefix('generateHydratorsCommand'))
			->setType(GenerateHydratorsCommand::class)
			->addTag('console.command', 'odm:generate:hydrators')
			->setAutowired(false);

		$builder->addDefinition($this->prefix('generateProxiesCommand'))
			->setType(GenerateProxiesCommand::class)
			->addTag('console.command', 'odm:generate:proxies')
			->setAutowired(false);

		$builder->addDefinition($this->prefix('queryCommand'))
			->setType(QueryCommand::class)
			->addTag('console.command', 'odm:query')
			->setAutowired(false);

		$builder->addDefinition($this->prefix('metadataCommand'))
			->setType(MetadataCommand::class)
			->addTag('console.command', 'odm:clear-cache:metadata')
			->setAutowired(false);
	}


	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		// Skip if it's not CLI mode
		if (!$this->cliMode) {
			return;
		}

		$builder = $this->getContainerBuilder();

		// Lookup for Symfony Console Application
		/** @var ServiceDefinition $applicationDef */
		$applicationDef = $builder->getDefinitionByType(Application::class);

		// Register helpers
		$documentManagerHelper = $this->prefix('@documentManagerHelper');
		$applicationDef->addSetup(new Statement('$service->getHelperSet()->set(?,?)', [$documentManagerHelper, 'dm']));
	}

}
