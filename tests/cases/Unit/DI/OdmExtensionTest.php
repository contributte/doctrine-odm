<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Client;
use MongoDB\Driver\Manager;
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
use Tester\Assert;
use Tester\TestCase;
use Tests\Fixtures\DummyConfiguration;

require_once __DIR__ . '/../../../bootstrap.php';


/**
 * @testCase
 */
final class OdmExtensionTest extends TestCase
{

	public function testMongo(): void
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

		$client = $container->getByType(Client::class);

		Assert::type(Client::class, $client);
		Assert::type(Manager::class, $client->getManager());
	}

	public function testDocumentManager(): void
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

		Assert::type(DocumentManager::class, $container->getByType(DocumentManager::class));
	}

	public function testOwnImplementations(): void
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
				$compiler->addConfig(
					[
						'odm' => [
							'configurationClass' => DummyConfiguration::class,
						],
					]
				);
			},
			[getmypid(), 2]
		);

		/** @var Container $container */
		$container = new $class();

		Assert::type(DummyConfiguration::class, $container->getByType(DummyConfiguration::class));
	}

	public function testConfigurationException(): void
	{
		Assert::exception(
			function (): void {
				$loader = new ContainerLoader(TEMP_DIR, true);
				$loader->load(
					static function (Compiler $compiler): void {
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
						$compiler->addConfig(
							[
								'odm' => [
									'configurationClass' => stdClass::class,
								],
							]
						);
					},
					[getmypid(), 3]
				);
			},
			InvalidArgumentException::class,
			'Configuration class must be subclass of Doctrine\ODM\MongoDB\Configuration, stdClass given.'
		);
	}

}


(new OdmExtensionTest())->run();
