<?php declare (strict_types=1);

namespace Entrydo\Tests\Integration\Api;

use Damejidlo\DateTimeFactory\DateTimeImmutableFactory;
use Entrydo\Tests\DatabaseSetup;
use Mockery;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use PHPUnit\Framework\TestCase;

class PostRequestPresenterTest extends TestCase
{
	use DatabaseSetup;


	/**
	 * @dataProvider getPresenters
	 */
	public function test(string $url, string $requestBodyFile, string $responseFile): void
	{
		$container = $this->getContainer();
		$dateTimeFactory = Mockery::mock(DateTimeImmutableFactory::class);
		$dateTimeFactory->shouldReceive('getNow')->andReturn(new \DateTimeImmutable('2017-12-31 23:00:00'));
		$container->removeService('dateTimeFactory');
		$container->addService('dateTimeFactory', $dateTimeFactory);

		$httpClient = $this->createHttpClient();
		$httpClient->request(IRequest::POST, $url, [], [], [], file_get_contents($requestBodyFile));

		$response = $httpClient->getResponse();

		$this->assertContains($response->getStatus(), [IResponse::S200_OK, IResponse::S201_CREATED, IResponse::S400_BAD_REQUEST]);

		if (!empty($responseFile)) {
			$this->assertJsonStringEqualsJsonString(file_get_contents($responseFile), $response->getContent());
		}
	}


	public function getPresenters(): array
	{
		return [
			'Example' => [
				'/my-example',
				__DIR__ . '/PostRequestData/sample-request.json',
				__DIR__ . '/PostRequestData/sample-response.json',
			],
		];
	}
}
