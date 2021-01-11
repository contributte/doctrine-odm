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
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../../../bootstrap.php';


/**
 * @testCase
 */
final class OdmAnnotationsExtensionTest extends TestCase
{

	public function testOk(): void
	{
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(
			static function (Compiler $compiler): void {
				$compiler->addExtension('annotations', new AnnotationsExtension());
				$compiler->addExtension('cache', new CacheExtension());
				$compiler->addExtension('mongodb', new MongoDBExtension());
				$compiler->addExtension('odm', new OdmExtension());
				$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
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

		Assert::type(AnnotationDriver::class, $container->getService('odm.annotations.annotationDriver'));
	}

	public function testNoReader(): void
	{
		Assert::exception(
			function (): void {
				$loader = new ContainerLoader(TEMP_DIR, true);
				$class = $loader->load(
					static function (Compiler $compiler): void {
						$compiler->addExtension('cache', new CacheExtension());
						$compiler->addExtension('mongodb', new MongoDBExtension());
						$compiler->addExtension('odm', new OdmExtension());
						$compiler->addExtension('odm.annotations', new OdmAnnotationsExtension());
						$compiler->addConfig(
							[
								'parameters' => [
									'tempDir' => TEMP_DIR,
									'appDir' => __DIR__,
								],
							]
						);
					},
					[getmypid(), 2]
				);

				new $class();
			},
			MissingServiceException::class,
			"#Service of type 'Doctrine\\\Common\\\Annotations\\\Reader' not found\.#"
		);
	}

}


(new OdmAnnotationsExtensionTest())->run();
