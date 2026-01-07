<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Nette\DI\MissingServiceException;
use Nettrine\Annotations\DI\AnnotationsExtension;
use Nettrine\Cache\DI\CacheExtension;
use Nettrine\MongoDB\DI\MongoDBExtension;
use Nettrine\ODM\DI\OdmAnnotationsExtension;
use Nettrine\ODM\DI\OdmExtension;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

// Test Ok
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

	Assert::type(AnnotationDriver::class, $container->getService('odm.annotations.annotationDriver'));
});

// Test NoReader
Toolkit::test(static function (): void {
	Assert::exception(
		static function (): void {
			ContainerBuilder::of()
				->withCompiler(static function ($compiler): void {
					$compiler->addExtension('cache', new CacheExtension());
					$compiler->addExtension('mongodb', new MongoDBExtension());
					$compiler->addExtension('odm', new OdmExtension());
					$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
					$compiler->addConfig([
						'parameters' => [
							'tempDir' => Environment::getTestDir(),
							'appDir' => __DIR__,
						],
					]);
				})
				->build();
		},
		MissingServiceException::class,
		'#Service of type Doctrine.Common.Annotations.Reader not found#'
	);
});
