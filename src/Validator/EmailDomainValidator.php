<?php

namespace App\Validator;

use App\Repository\ConfigRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EmailDomainValidator extends ConstraintValidator
{
    /** @var ConfigRepository */
    private $configRepository;

    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\EmailDomain */

        if (null === $value || '' === $value) {
            return;
        }

        $domain = substr($value, strpos($value, '@') + 1);
        $blockDomain = array_merge($constraint->blocked, $this->configRepository->getAsArray('blocked_domains'));

        if(in_array($domain, $blockDomain)) {
            $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
        }
        
    }
}