<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\Common\Cache\Cache;
use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\DI\ServiceCreationException;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmAttributesExtension;
use Nettrine\ODM\DI\OdmCacheExtension;
use Nettrine\ODM\DI\OdmExtension;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

// Test AutowiredCacheDrivers
Toolkit::test(static function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function ($compiler): void {
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.attributes', new OdmAttributesExtension());
			$compiler->addExtension('odm.cache', new OdmCacheExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
					'appDir' => __DIR__,
				],
			]);
		})
		->build();

	/** @var DocumentManager $dm */
	$dm = $container->getByType(DocumentManager::class);

	Assert::type(Cache::class, $dm->getConfiguration()->getMetadataCacheImpl());
});

// Test ProvidedCacheDrivers
Toolkit::test(static function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function ($compiler): void {
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.attributes', new OdmAttributesExtension());
			$compiler->addExtension('odm.cache', new OdmCacheExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
					'appDir' => __DIR__,
				],
				'odm.cache' => [
					'defaultDriver' => ArrayAdapter::class,
					'metadataCache' => NullAdapter::class,
				],
			]);
		})
		->build();

	/** @var DocumentManager $dm */
	$dm = $container->getByType(DocumentManager::class);

	Assert::type(NullAdapter::class, $dm->getConfiguration()->getMetadataCacheImpl());
});

// Test NoCacheDriver
Toolkit::test(static function (): void {
	Assert::exception(
		static function (): void {
			ContainerBuilder::of()
				->withCompiler(static function ($compiler): void {
					$compiler->addExtension('mongodb', new MongoDBExtension());
					$compiler->addExtension('odm', new OdmExtension());
					$compiler->addExtension('odm.attributes', new OdmAttributesExtension());
					$compiler->addExtension('odm.cache', new OdmCacheExtension());
					$compiler->addConfig([
						'parameters' => [
							'tempDir' => Environment::getTestDir(),
							'appDir' => __DIR__,
						],
					]);
				})
				->build();
		},
		ServiceCreationException::class,
		'#Service \'(mongodb|odm).configuration\' .+#'
	);
});
