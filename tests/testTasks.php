<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{

	public function testTaskTitleShouldNotBeEmpty()
	{
		$value1 = 10;
		$value2 = 10;
		$this->assetEquals(
            $value1,
            $value2
        );
	}

}
?>