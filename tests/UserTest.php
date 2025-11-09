phpunit.xml
<phpunit bootstrap="vendor/autoload.php">
    <testsuites>
        <testsuite name="User Test Suite">
            <directory>./tests</directory>
        </testsuite>
    </testsuites>
</phpunit>

tests/UserTest.php
<?php
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }
}