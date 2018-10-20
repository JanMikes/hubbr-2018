<?php declare (strict_types=1);

namespace Entrydo\Tests;


use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Nette\DI\Container;
use Entrydo\Tests\Utils\DoctrineConnectionMock;
use Symplify\DoctrineMigrations\Configuration\Configuration;

trait MysqlDatabaseSetup
{
	use CompiledContainer {
		createContainer as parentCreateContainer;
	}


	/**
	 * @var string|null
	 */
	protected $databaseName;


	protected function createContainer(): Container
	{
		$this->extraConfigs[] = __DIR__ . '/mysql.neon';

		ini_set('memory_limit', '1024m');
		set_time_limit(60);

		$container = $this->parentCreateContainer();

		// /** @var DoctrineConnectionMock $db */
		$db = $container->getByType(Connection::class);
		if ( ! $db instanceof DoctrineConnectionMock) {
			throw new \LogicException('Connection service should be instance of ConnectionMock');
		}

		$db->onConnect[] = function (Connection $db) use ($container) {
			if ($this->databaseName !== null) {
				return;
			}

			/** @var EntityManager $em */
			$em = $container->getByType(EntityManager::class);

			$this->setupDatabase($em);
		};

		return $container;
	}


	private function setupDatabase(EntityManager $entityManager): void
	{
		$this->databaseName = 'db_tests_' . getmypid();

		$connection = $entityManager->getConnection();

		$this->dropDatabase($connection);
		$this->createDatabase($connection);
		$this->runMigrations($entityManager);
		$this->loadFixtures($entityManager);

		register_shutdown_function(function () use ($connection) {
			$this->dropDatabase($connection);
		});
	}


	private function createDatabase(Connection $db): void
	{
		$db->exec("CREATE DATABASE {$this->databaseName}");
		$this->connectToDatabase($db, $this->databaseName);
	}


	private function dropDatabase(Connection $db): void
	{
		if (!$db->getDriver() instanceof Driver) {
			return;
		}

		$db->exec("DROP DATABASE IF EXISTS {$this->databaseName}");
	}


	private function connectToDatabase(Connection $db, $databaseName): void
	{
		$db->close();
		$db->__construct(
			['dbname' => $databaseName] + $db->getParams(),
			new Driver(),
			$db->getConfiguration(),
			$db->getEventManager()
		);
		$db->connect();
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
