<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\ODM\MongoDB\DocumentManager;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmAnnotationsExtension;
use Nettrine\ODM\DI\OdmExtension;
use Nettrine\ODM\Exception\Logical\InvalidArgumentException;
use stdClass;
use Tests\Fixtures\DummyConfiguration;
use Tests\Toolkit\TestCase;

final class OdmExtensionTest extends TestCase
{

	public function testDocumentManager(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => TEMP_PATH,
					'appDir' => __DIR__,
				],
			]);
		}, self::class . __METHOD__);

		/** @var Container $container */
		$container = new $class();
		$this->assertInstanceOf(DocumentManager::class, $container->getByType(DocumentManager::class));
	}

	public function testOwnImplementations(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('annotations', new AnnotationsExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => TEMP_PATH,
					'appDir' => __DIR__,
				],
			]);
			$compiler->addConfig([
				'odm' => [
					'configurationClass' => DummyConfiguration::class,
				],
			]);
		}, self::class . __METHOD__);

		/** @var Container $container */
		$container = new $class();
		$this->assertInstanceOf(DummyConfiguration::class, $container->getByType(DummyConfiguration::class));
	}

	public function testConfigurationException(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Configuration class must be subclass of Doctrine\ODM\MongoDB\Configuration, stdClass given.');

		$loader = new ContainerLoader(TEMP_PATH, true);
		$loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => TEMP_PATH,
					'appDir' => __DIR__,
				],
			]);
			$compiler->addConfig([
				'odm' => [
					'configurationClass' => stdClass::class,
				],
			]);
		}, self::class . __METHOD__);
	}

}
