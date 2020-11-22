<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\Common\Cache\VoidCache;
use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\ServiceCreationException;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmAnnotationsExtension;
use Nettrine\ODM\DI\OdmCacheExtension;
use Nettrine\ODM\DI\OdmExtension;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';


/**
 * @testCase
 */
final class OdmCacheExtensionTest extends TestCase
{

	public function testAutowiredCacheDrivers(): void
	{
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(
			static function (Compiler $compiler): void {
				$compiler->addExtension('annotations', new AnnotationsExtension());
				$compiler->addExtension('cache', new CacheExtension());
				$compiler->addExtension('mongodb', new MongoDBExtension());
				$compiler->addExtension('odm', new OdmExtension());
				$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
				$compiler->addExtension('odm.cache', new OdmCacheExtension());
				$compiler->addConfig(
					[
						'parameters' => [
							'tempDir' => TEMP_DIR,
							'appDir' => __DIR__,
						],
					]
				);
			},
			[getmypid(), 1]
		);

		/** @var Container $container */
		$container = new $class();

		/** @var DocumentManager $dm */
		$dm = $container->getByType(DocumentManager::class);

		Assert::type(PhpFileCache::class, $dm->getConfiguration()->getMetadataCacheImpl());
	}

	public function testProvidedCacheDrivers(): void
	{
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(
			static function (Compiler $compiler): void {
				$compiler->addExtension('annotations', new AnnotationsExtension());
				$compiler->addExtension('cache', new CacheExtension());
				$compiler->addExtension('mongodb', new MongoDBExtension());
				$compiler->addExtension('odm', new OdmExtension());
				$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
				$compiler->addExtension('odm.cache', new OdmCacheExtension());
				$compiler->addConfig(
					[
						'odm.cache' => [
							'defaultDriver' => ArrayCache::class,
							'metadataCache' => VoidCache::class,
						],
						'parameters' => [
							'tempDir' => TEMP_DIR,
							'appDir' => __DIR__,
						],
					]
				);
			},
			[getmypid(), 2]
		);

		/** @var Container $container */
		$container = new $class();

		/** @var DocumentManager $dm */
		$dm = $container->getByType(DocumentManager::class);

		Assert::type(VoidCache::class, $dm->getConfiguration()->getMetadataCacheImpl());
	}

	public function testNoCacheDriver(): void
	{
		Assert::exception(
			function (): void {
				$loader = new ContainerLoader(TEMP_DIR, true);
				$class = $loader->load(
					static function (Compiler $compiler): void {
						$compiler->addExtension('annotations', new AnnotationsExtension());
						$compiler->addExtension('mongodb', new MongoDBExtension());
						$compiler->addExtension('odm', new OdmExtension());
						$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
						$compiler->addExtension('odm.cache', new OdmCacheExtension());
						$compiler->addConfig(
							[
								'annotations' => [
									'cache' => VoidCache::class,
								],
								'parameters' => [
									'tempDir' => TEMP_DIR,
									'appDir' => __DIR__,
								],
							]
						);
					},
					[getmypid(), 3]
				);

				new $class();
			},
			ServiceCreationException::class,
			'#Service \'(mongodb|odm).configuration\' .+#'
		);
	}

}


(new OdmCacheExtensionTest())->run();
