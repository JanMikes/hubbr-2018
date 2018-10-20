<?php declare (strict_types=1);

namespace Entrydo\Tests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Nette\DI\Container;
use Entrydo\Tests\Utils\DoctrineConnectionMock;

trait DatabaseSetup
{
	use CompiledContainer {
		createContainer as parentCreateContainer;
	}


	protected function createContainer(): Container
	{
		ini_set('memory_limit', '1024m');

		$container = $this->parentCreateContainer();

		$db = $container->getByType(Connection::class);
		if (!$db instanceof DoctrineConnectionMock) {
			throw new \LogicException('Connection service should be instance of ConnectionMock');
		}

		$db->onConnect[] = function () use ($container) {
			/** @var EntityManager $em */
			$em = $container->getByType(EntityManager::class);

			$this->runMigrations($em);
			$this->loadFixtures($em);
		};

		return $container;
	}


	private function runMigrations(EntityManager $entityManager): void
	{
		$metadata = $entityManager->getMetadataFactory()->getAllMetadata();

		$schemaTool = new SchemaTool($entityManager);
		$schemaTool->createSchema($metadata);
	}


	private function loadFixtures(EntityManager $entityManager): void
	{
		$loader = new Loader();
		$loader->loadFromDirectory(__DIR__ . '/../fixtures');

		$fixtures = $loader->getFixtures();
		$executor = new ORMExecutor($entityManager);

		$executor->execute($fixtures, TRUE);
	}
}