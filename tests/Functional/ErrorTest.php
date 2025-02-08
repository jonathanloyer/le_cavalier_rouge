<?php 
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ErrorTest extends WebTestCase
{
    public function errorPagesProvider(): array
    {
        return [
            ['/test-error400', 400],
            ['/test-error403', 403],
            ['/test-error404', 404],
            ['/test-error500', 500],
            ['/test-error-generic', 503],
        ];
    }

    /**
     * @dataProvider errorPagesProvider
     */
    public function testErrorPages(string $url, int $expectedStatusCode): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseStatusCodeSame($expectedStatusCode);
    }
}
