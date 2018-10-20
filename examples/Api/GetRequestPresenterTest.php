<?php declare (strict_types=1);

namespace Entrydo\Tests\Integration\Api;

use Damejidlo\DateTimeFactory\DateTimeImmutableFactory;
use Entrydo\Tests\DatabaseSetup;
use Mockery;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use PHPUnit\Framework\TestCase;

class GetRequestPresenterTest extends TestCase
{
	use DatabaseSetup;


	/**
	 * @dataProvider getPresenters
	 */
	public function test(string $url, ?string $expectedResponseFile = null): void
	{
		$container = $this->getContainer();
		$dateTimeFactory = Mockery::mock(DateTimeImmutableFactory::class);
		$dateTimeFactory->shouldReceive('getNow')->andReturn(new \DateTimeImmutable('2017-12-31 23:00:00'));
		$container->removeService('dateTimeFactory');
		$container->addService('dateTimeFactory', $dateTimeFactory);

		$httpClient = $this->createHttpClient();
		$httpClient->request(IRequest::GET, $url);

		$response = $httpClient->getResponse();

		$this->assertSame(IResponse::S200_OK, $response->getStatus());

		if (!empty($expectedResponseFile)) {
			$this->assertJsonStringEqualsJsonString(file_get_contents($expectedResponseFile), $response->getContent());
		}
	}


	public function getPresenters(): array
	{
		return [
			'Example' => [
				'/my-route',
				__DIR__ . '/GetRequestResponses/sample.json'
			],
		];
	}
}
