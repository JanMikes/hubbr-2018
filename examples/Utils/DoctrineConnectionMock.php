<?php declare (strict_types=1);

namespace Entrydo\Tests\Utils;

use Kdyby\Doctrine\Connection;
use Nette\SmartObject;

/**
 * @method onConnect(DoctrineConnectionMock $self)
 */
class DoctrineConnectionMock extends Connection
{
	use SmartObject;

	public $onConnect = [];


	public function connect()
	{
		if (parent::connect()) {
			$this->onConnect($this);
		}
	}
}
