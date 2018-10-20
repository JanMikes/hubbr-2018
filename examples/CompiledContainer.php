<?php declare (strict_types=1);

namespace Entrydo\Tests;

use Nette\Configurator;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Entrydo\Tests\Utils\HttpClient;

trait CompiledContainer
{
	/** @var Container */
	private $container;

	/** @var array */
	protected $extraConfigs = [];


	protected function createContainer(): Container
	{
		$configurator = new Configurator();

		$configurator->setDebugMode(FALSE);
		$configurator->setTempDirectory($this->createTempDir());
		$configurator->enableDebugger(__DIR__ . '/../var/log');

		$configurator->addParameters([
			'appDir' => __DIR__ . '/../app',
		]);

		$configurator->addConfig(__DIR__ . '/../app/config/config.neon');

		if (file_exists(__DIR__ . '/../app/config/config.local.neon')) {
			$configurator->addConfig(__DIR__ . '/../app/config/config.local.neon');
		}

		$configurator->addConfig(__DIR__ . '/tests.neon');

		foreach ($this->extraConfigs as $extraConfig) {
			$configurator->addConfig($extraConfig);
		}

		$this->registerClearTempOnShutdown();

		return $configurator->createContainer();
	}


	private function createTempDir(): string
	{
		$tempDir = __DIR__ . '/../var/temp/tests/' . getmypid();

		FileSystem::delete($tempDir);
		FileSystem::createDir($tempDir);

		return $tempDir;
	}


	private function registerClearTempOnShutdown(): void
	{
		register_shutdown_function(function () {
			FileSystem::delete(__DIR__ . '/../var/temp/tests');
		});
	}


	protected function createHttpClient(): HttpClient
	{
		$client = new HttpClient();
		$client->setContainer($this->getContainer());

		return $client;
	}


	protected function getContainer(): Container
	{
		if ($this->container === null) {
			$this->container = $this->createContainer();
		}

		return $this->container;
	}
}