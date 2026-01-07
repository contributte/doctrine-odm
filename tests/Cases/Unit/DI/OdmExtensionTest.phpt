<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Client;
use MongoDB\Driver\Manager;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmAnnotationsExtension;
use Nettrine\ODM\DI\OdmExtension;
use Nettrine\ODM\Exception\Logical\InvalidArgumentException;
use stdClass;
use Tester\Assert;
use Tests\Fixtures\DummyConfiguration;

require_once __DIR__ . '/../../../bootstrap.php';

// Test Mongo
Toolkit::test(static function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function ($compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
					'appDir' => __DIR__,
				],
				'annotations' => [
					'cache' => '@cache.adapter',
				],
			]);
		})
		->build();

	$client = $container->getByType(Client::class);

	Assert::type(Client::class, $client);
	Assert::type(Manager::class, $client->getManager());
});

// Test DocumentManager
Toolkit::test(static function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function ($compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
					'appDir' => __DIR__,
				],
				'annotations' => [
					'cache' => '@cache.adapter',
				],
			]);
		})
		->build();

	Assert::type(DocumentManager::class, $container->getByType(DocumentManager::class));
});

// Test OwnImplementations
Toolkit::test(static function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function ($compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
					'appDir' => __DIR__,
				],
				'annotations' => [
					'cache' => '@cache.adapter',
				],
				'odm' => [
					'configurationClass' => DummyConfiguration::class,
				],
			]);
		})
		->build();

	Assert::type(DummyConfiguration::class, $container->getByType(DummyConfiguration::class));
});

// Test ConfigurationException
Toolkit::test(static function (): void {
	Assert::exception(
		static function (): void {
			ContainerBuilder::of()
				->withCompiler(static function ($compiler): void {
					$compiler->addExtension('mongodb', new MongoDBExtension());
					$compiler->addExtension('odm', new OdmExtension());
					$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
					$compiler->addConfig([
						'parameters' => [
							'tempDir' => Environment::getTestDir(),
							'appDir' => __DIR__,
						],
						'odm' => [
							'configurationClass' => stdClass::class,
						],
					]);
				})
				->build();
		},
		InvalidArgumentException::class,
		'Configuration class must be subclass of Doctrine\ODM\MongoDB\Configuration, stdClass given.'
	);
});
