<?php

namespace Entrydo\Tests\Integration\Api;

use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Entrydo\App\Routing\RouterFactory;
use Entrydo\Tests\DatabaseSetup;
use PHPUnit\Framework\TestCase;

class BillingInfoDetailPresenterTest extends TestCase
{
	use DatabaseSetup;


	/** @test */
	public function responseShouldBeValid(): void
	{
		$httpClient = $this->createHttpClient();
		$httpClient->request(IRequest::GET, RouterFactory::BILLING_ROUTE);

		$response = $httpClient->getResponse();

		$this->assertSame(IResponse::S200_OK, $response->getStatus());
	}
}
