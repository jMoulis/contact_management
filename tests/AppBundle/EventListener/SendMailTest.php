<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 20/11/17
 * Time: 10:44
 */

namespace Tests\AppBundle\EventListener;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SendMailTest extends WebTestCase
{
    public function testMailIsSentAndContentIsOk()
    {
        $client = static::createClient();

        // Enable the profiler for the next request (it does nothing if the profiler is not available)
        $client->enableProfiler();

        $data = [
            'firstname' => 'Julien',
            'lastname' => 'Selka',
            'phone' => '+33643390714',
            'message' => 'Lorem ipsum manaveat clovitic narvalo'
        ];

        $client->request('POST', '/api/contacts', [
            'body' => json_encode($data)
        ]);

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an email was sent
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Hello Email', $message->getSubject());
        $this->assertEquals('send@example.com', key($message->getFrom()));
        $this->assertEquals('recipient@example.com', key($message->getTo()));
        $this->assertEquals(
            'You should see me from the profiler!',
            $message->getBody()
        );
    }
}