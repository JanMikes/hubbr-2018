<?php declare (strict_types=1);

namespace Entrydo\Tests\Utils;

use NBrowserKit\Client;
use Nette\Application\Application;
use Nette\Application\IPresenterFactory;
use Nette\Application\IRouter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HttpClient extends Client
{
	protected function createApplication(IRequest $request, IPresenterFactory $presenterFactory, IRouter $router, IResponse $response)
	{
		/** @var Application $application */
		$application = $this->getContainer()->getByType(Application::class);

		$application->__construct(
			$presenterFactory,
			$router,
			$request,
			$response
		);

		return $application;
	}
}