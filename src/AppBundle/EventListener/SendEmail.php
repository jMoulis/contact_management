<?php
/**
 * Created by PhpStorm.
 * User: julienmoulis
 * Date: 11/03/2017
 * Time: 14:34
 */

namespace AppBundle\EventListener;


use AppBundle\Entity\Contact;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Swift_Mailer;

class SendEmail implements EventSubscriber
{
    const NOUVEAU_CONTACT= "Nouveau Contact";

    private $mailer;

    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    // Gestion des réponses
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Contact){
            return;
        }

        $from = $entity->getEmail();
        $to = 'julien.moulis@moulis.me';
        $body = $entity->getMessage();

        $this->sendEmail($from, $to, $body);

    }

    public function getSubscribedEvents()
    {
        return ['postPersist'];
    }

    private function sendEmail(string $from, string $to, string $body)
    {
        $message = $this->mailer->createMessage()
            ->setSubject(self::NOUVEAU_CONTACT)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body);
        ;
        $this->mailer->send($message);

        return $message;
    }
}