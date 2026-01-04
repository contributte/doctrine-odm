<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmExtension;
use Nettrine\ODM\DI\OdmXmlExtension;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

// Test Extension
Toolkit::test(static function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function ($compiler): void {
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.xml', new OdmXmlExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
					'appDir' => __DIR__,
				],
			]);
		})
		->build();

	Assert::type(XmlDriver::class, $container->getService('odm.xml.xmlDriver'));
});
