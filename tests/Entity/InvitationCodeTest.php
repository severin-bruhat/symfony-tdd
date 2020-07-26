<?php 

namespace App\Tests\Entity;

use App\Entity\InvitationCode;
use App\DataFixtures\InvitationCodeFixtures;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InvitationCodeTest extends KernelTestCase
{
    use FixturesTrait;

    private function getEntity(): InvitationCode
    {
        return (new InvitationCode())
            ->setCode("12345")
            ->setDescription("Description de test")
            ->setExpireAt(new \DateTime());
    }

    private function assertHasErrors(InvitationCode $code, int $number = 0)
    {
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($code);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(',', $messages));
    }

    public function testValidCodeEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInValidCodeEntity()
    {
        $this->assertHasErrors($this->getEntity()->setCode("1a345"), 1);
        $this->assertHasErrors($this->getEntity()->setCode("1345"), 1);
    }

    public function testInValidBlankCodeEntity()
    {
        $this->assertHasErrors($this->getEntity()->setCode(""), 1);
    }

    public function testInValidBlankDescriptionEntity()
    {
        $this->assertHasErrors($this->getEntity()->setDescription(""), 1);
    }

    public function testInvalideUsedCode()
    {
        $this->loadFixtures([InvitationCodeFixtures::class]);
        $this->assertHasErrors($this->getEntity()->setCode("54321"), 1);
    }
}