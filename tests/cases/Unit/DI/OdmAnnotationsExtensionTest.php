<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\MissingServiceException;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmAnnotationsExtension;
use Nettrine\ODM\DI\OdmExtension;
use Tests\Toolkit\TestCase;

final class OdmAnnotationsExtensionTest extends TestCase
{

	public function testOk(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(
			static function (Compiler $compiler): void {
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
			},
			self::class . __METHOD__
		);

		/** @var Container $container */
		$container = new $class();

		$this->assertInstanceOf(AnnotationDriver::class, $container->getService('odm.annotations.annotationDriver'));
	}

	public function testNoReader(): void
	{
		$this->expectException(MissingServiceException::class);
		$this->expectExceptionMessageMatches("#Service of type 'Doctrine\\\Common\\\Annotations\\\Reader' not found\.#");

		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
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
	}

}
