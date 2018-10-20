<?php declare (strict_types=1);

namespace Entrydo\Tests\Utils;

use h4kuna\Ares\Ares;
use Mockery\MockInterface;
use Mockery as m;

class AresMockFactory
{
	/** @return MockInterface|Ares */
	public static function create(): Ares
	{
		return m::mock(Ares::class);
	}
}
