<?php

namespace App\Tests\Validator;

use App\Repository\ConfigRepository;
use App\Validator\EmailDomain;
use PHPUnit\Framework\TestCase;
use App\Validator\EmailDomainValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class EmailDomainValidatorTest extends TestCase
{

    public function getValidator($expectedViolation = false, $dbBlockedDomain = []) {

        $repository = $this->getMockBuilder(ConfigRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
            ->method('getAsArray')
            ->with('blocked_domains')
            ->willReturn($dbBlockedDomain);

        $validator = new EmailDomainValidator($repository);
        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        
        if($expectedViolation) {
            $violation = $this->getMockBuilder(ConstraintViolationBuilderInterface::class)->getMock();
            $violation->expects($this->any())->method('setParameter')->willReturn($violation);
            $violation->expects($this->once())->method('addViolation');
            
            $context
                ->expects($this->once())
                ->method('buildViolation')
                ->willReturn($violation);
        } else {
            $context
            ->expects($this->never())
            ->method('buildViolation');
        }
        
        $validator->initialize($context);

        return $validator;  
    }

    public function testCatchBadDomains() 
    {
        $constraint = new EmailDomain([
            'blocked' => ['baddomain.fr', 'aze.com'],
        ]);  
        $this->getValidator(true)->validate('demo@baddomain.fr', $constraint);
    }

    public function testAcceptGoodDomains() 
    {
        $constraint = new EmailDomain([
            'blocked' => ['baddomain.fr', 'aze.com'],
        ]);  
        $this->getValidator(false)->validate('demo@gooddomain.fr', $constraint);
    }

    public function testBlockedDomainFromDatabase() 
    {
        $constraint = new EmailDomain([
            'blocked' => ['baddomain.fr', 'aze.com'],
        ]);  
        $this->getValidator(true, ['baddbdomain.fr'])->validate('demo@baddbdomain.fr', $constraint);
    }

}