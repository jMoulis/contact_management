<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 17/11/17
 * Time: 09:18
 */

namespace Tests\AppBundle\Controller;


use AppBundle\Test\ApiTestCase;


class UserControllerTest extends ApiTestCase
{
    public function testNewAction()
    {
        $data = [
            'username' => 'julien',
            'plainpassword' => 'test',
            'email' => 'julien.moulis@moulis.me',
        ];

        $response = $this->client->post('/api/users', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringEndsWith('/login', $response->getHeader('Location')[0]);
        $this->assertEquals('julien', $data['username']);
    }

    public function testShowAction()
    {
        $response = $this->client->get('/api/users/rachel',
            [
                'headers' => $this->getAuthorizedHeaders('rachel')
            ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'username',
        ]);
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'username',
            'rachel'
        );
        $this->asserter()->assertResponsePropertyEquals($response,
            '_links.self',
            '/api/users/rachel'
        );
    }

    public function testListAction()
    {
        $this->createUser('julien', 'test');

        $response = $this->client->get('/api/users',
            [
                'headers' => $this->getAuthorizedHeaders('rachel')
            ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'items');
        $this->asserter()->assertResponsePropertyCount($response, 'items', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'items[0].username', 'rachel');

    }

    public function testListPaginatedAction()
    {

        for ($i = 0; $i < 25; $i++) {
            $this->createUser('Rachel'.$i, 'test');
        }

        // page 1
        $response = $this->client->get('/api/users?filter=user',
            [
                'headers' => $this->getAuthorizedHeaders('rachel')
            ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[1].username',
            'Rachel0'
        );

        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);
        $this->asserter()->assertResponsePropertyEquals($response, 'total', 26);
        $this->asserter()->assertResponsePropertyExists($response, '_links.next');

        // page 2
        $nextLink = $this->asserter()->readResponseProperty($response, '_links.next');
        $response = $this->client->get($nextLink,
            [
                'headers' => $this->getAuthorizedHeaders('rachel')
            ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[5].username',
            'Rachel20'
        );
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 10);

        $lastLink = $this->asserter()->readResponseProperty($response, '_links.last');
        $response = $this->client->get($lastLink,
            [
                'headers' => $this->getAuthorizedHeaders('rachel')
            ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals(
            $response,
            'items[4].username',
            'Rachel8'
        );

        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'items[5].name');
        $this->asserter()->assertResponsePropertyEquals($response, 'count', 6);
    }

    public function testPutUpdateAction()
    {

        $data = [
            'username' => 'julien',
            'email' => 'rachel.moulis@moulis.me', //Read Only
        ];

        $response = $this->client->put('/api/users/rachel', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('rachel')
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'username', 'julien');
        $this->asserter()->assertResponsePropertyEquals($response, 'email', 'rachel@moulis.me');
    }

    public function testPatchUpdateAction()
    {
        $data = [
            'email' => 'julien.moulis@moulis.me', // Read-only shouldn't change
        ];

        $response = $this->client->patch('/api/users/rachel', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('rachel')
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'email', 'rachel@moulis.me');
    }

    public function testDeleteAction()
    {
        $this->createUser('julien', 'test');

        $response = $this->client->delete('/api/users/julien',
            [
                'headers' => $this->getAuthorizedHeaders('rachel')
            ]);
        $this->assertEquals(204, $response->getStatusCode());

    }

    public function testValidationErrors()
    {
        $data = [
            'username' => 'rachel',
        ];

        $response = $this->client->post('/api/users', [
            'body' => json_encode($data),
            'headers' => $this->getAuthorizedHeaders('rachel')
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'type',
            'title',
            'errors',
        ]);
        $this->asserter()->assertResponsePropertyExists($response, 'errors.email');
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.email[0]', 'Please provide a clever email');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.username');
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
    }

    public function testInvalidJson()
    {
        $invalidBody = <<<EOF
{
    "username": "rachel",
    "email" : "2

}
EOF;

        $response = $this->client->post('/api/users', [
            'body' => $invalidBody,
            'headers' => $this->getAuthorizedHeaders('rachel')
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyContains(
            $response, 'type',
            'invalid_body_format'
        );
    }

    public function test404Exception()
    {
        $response = $this->client->get('/api/users/fake', [
            'headers' => $this->getAuthorizedHeaders('rachel')
        ]);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
        $this->asserter()->assertResponsePropertyEquals($response, 'detail', 'No contact fake bummer');
    }

    public function testRequiresAuthentification()
    {
        $response = $this->client->get('/api/users/rachel');
        $this->assertEquals(401, $response->getStatusCode());
    }


}
