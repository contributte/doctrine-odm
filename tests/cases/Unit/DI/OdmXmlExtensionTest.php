<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmExtension;
use Nettrine\ODM\DI\OdmXmlExtension;
use Tests\Toolkit\TestCase;

final class OdmXmlExtensionTest extends TestCase
{

	public function testExtension(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.xml', new OdmXmlExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => TEMP_PATH,
					'appDir' => __DIR__,
				],
			]);
		}, self::class . __METHOD__);

		/** @var Container $container */
		$container = new $class();

		$this->assertInstanceOf(XmlDriver::class, $container->getService('odm.xml.xmlDriver'));
	}

}
