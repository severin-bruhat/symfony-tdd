<?php

namespace App\DataFixtures;

use App\Entity\InvitationCode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class InvitationCodeFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $code = (new InvitationCode())
            ->setCode("54321")
            ->setDescription("test code")
            ->setExpireAt(new \DateTime("+1 year"));

        $manager->persist($code);    
        $manager->flush();
    }

}
