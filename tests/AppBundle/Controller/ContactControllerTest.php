<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 17/11/17
 * Time: 09:18
 */

namespace Tests\AppBundle\Controller;


use AppBundle\Test\ApiTestCase;


class ContactControllerTest extends ApiTestCase
{
    public function testNewAction()
    {
        $data = [
            'firstname' => 'rachel',
            'lastname' => 'Selka',
            'email' => 'julien.moulis@moulis.me',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ];

        $response = $this->client->post('/api/contacts', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertStringEndsWith('/api/contacts/rachel', $response->getHeader('Location')[0]);
        $finishedData = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('firstname', $finishedData);
        $this->assertEquals('rachel', $data['firstname']);
    }

    public function testShowAction()
    {
        $this->createContact($data = [
            'firstname' => 'rachel',
            'lastname' => 'Moulis',
            'email' => 'julien.moulis@moulis.me',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ]);

        $response = $this->client->get('/api/contacts/rachel');
        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'firstname',
            'lastname',
            'email',
            'phone',
            'message'
        ]);
        $this->asserter()->assertResponsePropertyEquals($response, 'firstname', 'rachel');
    }

    public function testListAction()
    {
        $this->createContact($data = [
            'firstname' => 'rachel',
            'lastname' => 'Selka',
            'email' => 'julien.moulis@moulis.me',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ]);

        $this->createContact($data = [
            'firstname' => 'julien',
            'lastname' => 'Moulis',
            'email' => 'julien.moulis@moulis.me',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ]);

        $response = $this->client->get('/api/contacts');

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyIsArray($response, 'contacts');
        $this->asserter()->assertResponsePropertyCount($response, 'contacts', 2);
        $this->asserter()->assertResponsePropertyEquals($response, 'contacts[0].firstname', 'rachel');

    }

    public function testPutUpdateAction()
    {
        $this->createContact($data = [
            'firstname' => 'rachel',
            'lastname' => 'Selka',
            'email' => 'julien.moulis@moulis.me',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ]);

        $data = [
            'firstname' => 'rachel',
            'lastname' => 'Moulis',
            'email' => 'rachel.moulis@moulis.me',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ];

        $response = $this->client->put('/api/contacts/rachel', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'lastname', 'Selka');
        $this->asserter()->assertResponsePropertyEquals($response, 'email', 'rachel.moulis@moulis.me');
    }

    public function testPatchUpdateAction()
    {
        $this->createContact($data = [
            'firstname' => 'rachel',
            'lastname' => 'Selka',
            'email' => 'julien.moulis@moulis.me',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ]);

        $data = [
            'lastname' => 'Moulis', // Read-only shouldn't change
            'email' => 'rachel@moulis.me',
        ];

        $response = $this->client->patch('/api/contacts/rachel', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyEquals($response, 'lastname', 'Selka');
        $this->asserter()->assertResponsePropertyEquals($response, 'firstname', 'rachel');
        $this->asserter()->assertResponsePropertyEquals($response, 'email', 'rachel@moulis.me');
    }

    public function testDeleteAction()
    {
        $this->createContact($data = [
            'firstname' => 'rachel',
            'lastname' => 'Selka',
            'email' => 'julien.moulis@moulis.me',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ]);

        $response = $this->client->delete('/api/contacts/rachel');
        $this->assertEquals(204, $response->getStatusCode());

    }

    public function testValidationErrors()
    {
        $data = [
            'firstname' => 'Julien',
            'lastname' => 'Selka',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ];

        $response = $this->client->post('/api/contacts', [
            'body' => json_encode($data)
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertiesExist($response, [
            'type',
            'title',
            'errors',
        ]);
        $this->asserter()->assertResponsePropertyExists($response, 'errors.email');
        $this->asserter()->assertResponsePropertyEquals($response, 'errors.email[0]', 'Please enter a clever email');
        $this->asserter()->assertResponsePropertyDoesNotExist($response, 'errors.lastname');
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
    }

    public function testInvalidJson()
    {
        $invalidBody = <<<EOF
{
    "firstname": "JohnnyRobot",
    "lastname" : "2
    "phone": "I'm from a test!"
}
EOF;

        $response = $this->client->post('/api/contacts', [
            'body' => $invalidBody
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->asserter()->assertResponsePropertyContains(
            $response, 'type',
            'invalid_body_format'
        );
    }

    public function test404Exception()
    {
        $response = $this->client->get('/api/contacts/fake');
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/problem+json', $response->getHeader('Content-Type')[0]);
        $this->asserter()->assertResponsePropertyEquals($response, 'type', 'about:blank');
        $this->asserter()->assertResponsePropertyEquals($response, 'title', 'Not Found');
    }
}
