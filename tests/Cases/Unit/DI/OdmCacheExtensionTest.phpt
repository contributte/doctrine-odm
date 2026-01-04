<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\VoidCache;
use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\DI\ServiceCreationException;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmAnnotationsExtension;
use Nettrine\ODM\DI\OdmCacheExtension;
use Nettrine\ODM\DI\OdmExtension;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

// Test AutowiredCacheDrivers
Toolkit::test(static function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function ($compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
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

	Assert::type(PhpFileCache::class, $dm->getConfiguration()->getMetadataCacheImpl());
});

// Test ProvidedCacheDrivers
Toolkit::test(static function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function ($compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
			$compiler->addExtension('odm.cache', new OdmCacheExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
					'appDir' => __DIR__,
				],
				'odm.cache' => [
					'defaultDriver' => ArrayCache::class,
					'metadataCache' => VoidCache::class,
				],
			]);
		})
		->build();

	/** @var DocumentManager $dm */
	$dm = $container->getByType(DocumentManager::class);

	Assert::type(VoidCache::class, $dm->getConfiguration()->getMetadataCacheImpl());
});

// Test NoCacheDriver
Toolkit::test(static function (): void {
	Assert::exception(
		static function (): void {
			ContainerBuilder::of()
				->withCompiler(static function ($compiler): void {
					$compiler->addExtension('annotations', new AnnotationsExtension());
					$compiler->addExtension('mongodb', new MongoDBExtension());
					$compiler->addExtension('odm', new OdmExtension());
					$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
					$compiler->addExtension('odm.cache', new OdmCacheExtension());
					$compiler->addConfig([
						'parameters' => [
							'tempDir' => Environment::getTestDir(),
							'appDir' => __DIR__,
						],
						'annotations' => [
							'cache' => VoidCache::class,
						],
					]);
				})
				->build();
		},
		ServiceCreationException::class,
		'#Service \'(mongodb|odm).configuration\' .+#'
	);
});
