<?php

namespace spec\Mannion007\BestInvestments\ProjectManagement\Domain;

use Mannion007\ValueObjects\Currency;
use Mannion007\BestInvestments\ProjectManagement\Domain\HourlyRate;
use Mannion007\BestInvestments\ProjectManagement\Domain\Specialist;
use Mannion007\BestInvestments\ProjectManagement\Domain\SpecialistId;
use PhpSpec\ObjectBehavior;

/**
 * Class SpecialistSpec
 * @package spec\Mannion007\BestInvestments\ProjectManagement\Domain
 * @mixin Specialist
 */
class SpecialistSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            SpecialistId::fromExisting('test-specialist-id'),
            'Test Specialist',
            new HourlyRate(100, Currency::gbp())
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Specialist::class);
    }
}
