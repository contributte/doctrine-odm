<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmAttributesExtension;
use Nettrine\ODM\DI\OdmExtension;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

// Test Ok
Toolkit::test(static function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(static function ($compiler): void {
			$compiler->addExtension('cache', new CacheExtension());
			$compiler->addExtension('mongodb', new MongoDBExtension());
			$compiler->addExtension('odm', new OdmExtension());
			$compiler->addExtension('odm.attributes', new OdmAttributesExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => Environment::getTestDir(),
					'appDir' => __DIR__,
				],
			]);
		})
		->build();

	Assert::type(AttributeDriver::class, $container->getService('odm.attributes.attributeDriver'));
});
