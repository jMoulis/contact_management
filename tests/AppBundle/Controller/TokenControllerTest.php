<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 27/11/17
 * Time: 12:30
 */

namespace Tests\AppBundle\Controller;


use AppBundle\Test\ApiTestCase;

class TokenControllerTest extends ApiTestCase
{
    public function testPostCreateToken()
    {
        $this->createUser('weaverryan', 'I<3Pizza');

        $response = $this->client->post('/api/tokens', [
           'auth' => ['weaverryan', 'I<3Pizza']
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists(
            $response,
            'token'
        );
    }

    public function testPostTokenInvalidCredentials()
    {
        $this->createUser('weaverryan', 'I<3Pizza');

        $response = $this->client->post('/api/tokens', [
            'auth' => ['weaverryan', 'I8Pizza']
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Unauthorized');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'Invalid credentials.');
    }
}