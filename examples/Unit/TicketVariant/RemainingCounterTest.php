<?php declare (strict_types=1);

namespace Entrydo\Tests\Unit\TicketVariant;

use Entrydo\Domain\Model\Event\TicketVariant\TicketVariant;
use Entrydo\Domain\Model\Numeric\AmountLimitation;
use Entrydo\Domain\Model\Ticket\TicketRepository;
use Entrydo\TicketVariant\RemainingCounter;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class RemainingCounterTest extends TestCase
{
	/**
	 * @test
	 */
	public function nullShouldBeReturnedIfNotLimited(): void
	{
		$amountLimitation = m::mock(AmountLimitation::class);
		$amountLimitation->shouldReceive('isLimited')->andReturn(FALSE);

		$variant = m::mock(TicketVariant::class);
		$variant->shouldReceive('amountLimitation')->andReturn($amountLimitation);

		$repository = m::mock(TicketRepository::class);
		$counter = new RemainingCounter($repository);

		$this->assertNull($counter->count($variant));
	}


	/**
	 * @test
	 * @dataProvider correctAmountShouldBeReturnedProvider
	 */
	public function correctAmountShouldBeReturned(int $limitedAmount, int $reservedAmount, int $expectedRemaining): void
	{
		$amountLimitation = m::mock(AmountLimitation::class);
		$amountLimitation->shouldReceive('isLimited')->andReturn(TRUE);
		$amountLimitation->shouldReceive('amount')->andReturn($limitedAmount);

		$variant = m::mock(TicketVariant::class);
		$variant->shouldReceive('amountLimitation')->andReturn($amountLimitation);
		$variant->shouldIgnoreMissing();

		$repository = m::mock(TicketRepository::class);
		$repository->shouldReceive('countReservedTicketsOfvariant')->andReturn($reservedAmount);
		$counter = new RemainingCounter($repository);

		$this->assertSame($expectedRemaining, $counter->count($variant));
	}


	public function correctAmountShouldBeReturnedProvider(): array
	{
		return [
			[0, 0, 0],
			[1, 0, 1],
			[1, 5, 0]
		];
	}
}
