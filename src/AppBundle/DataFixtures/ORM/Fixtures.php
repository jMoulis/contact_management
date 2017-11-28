<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 28/11/17
 * Time: 11:01
 */
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class Fixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail('julien.moulis+'.$i.'@moulis.me');
            $user->setUsername('julien'.$i);
            $user->setPassword('test');
            $manager->persist($user);
        }
        $manager->flush();
    }
}