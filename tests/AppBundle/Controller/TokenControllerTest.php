<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 27/11/17
 * Time: 12:30
 */

namespace Tests\AppBundle\Controller;


use AppBundle\Controller\UserController;
use AppBundle\Test\ApiTestCase;

class TokenControllerTest extends ApiTestCase
{
    /**
     * Test works if an user is previously created by the real UserController new Action. It seems that there is a pb
     * with the encoder of the ApiTestCase and the HashPassword
     */
    public function testPostCreateToken()
    {
        $response = $this->client->post('/api/tokens', [
           'auth' => ['julien', 'test']
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyExists(
            $response,
            'token'
        );
    }

    public function testPostTokenInvalidCredentials()
    {
        $response = $this->client->post('/api/tokens', [
            'auth' => ['rachel', 'I8Pizza']
        ]);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Unauthorized');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'Invalid credentials.');
    }
}