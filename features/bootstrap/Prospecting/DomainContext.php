<?php

namespace Mannion007\BestInvestmentsBehat\Prospecting;

use Behat\Behat\Context\Context;
use Mannion007\BestInvestments\Prospecting\Domain\HourlyRate;
use Mannion007\BestInvestments\Prospecting\Domain\Prospect;
use Mannion007\BestInvestments\Prospecting\Domain\ProspectGivenUpOnEvent;
use Mannion007\BestInvestments\Prospecting\Domain\ProspectId;
use Mannion007\BestInvestments\Prospecting\Domain\ProspectNotInterestedEvent;
use Mannion007\BestInvestments\Prospecting\Domain\ProspectRegisteredEvent;
use Mannion007\BestInvestments\Prospecting\Domain\ProspectStatus;
use Mannion007\BestInvestments\Prospecting\Infrastructure\EventPublisher\InMemoryEventPublisher;
use Mannion007\BestInvestments\EventPublisher\EventPublisher;
use Mannion007\ValueObjects\Currency;

/**
 * Defines application features from the specific context.
 */
class DomainContext implements Context
{
    /** @var InMemoryEventPublisher */
    private $eventPublisher;

    /** @var ProspectId */
    private $prospectId;

    /** @var Prospect */
    private $prospect;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->eventPublisher = new InMemoryEventPublisher();
        EventPublisher::registerPublisher($this->eventPublisher);
        $this->prospectId = ProspectId::fromExisting('test-prospect-id');
    }

    /**
     * @Given I have received a Prospect
     */
    public function iHaveReceivedAProspect()
    {
        $this->prospect = Prospect::receive($this->prospectId, 'Test Prospect', 'Prospect notes');
    }

    /**
     * @When I chase up the Prospect
     */
    public function iChaseUpTheProspect()
    {
        $this->prospect->chaseUp();
    }

    /**
     * @When The Prospect registers
     */
    public function theProspectRegisters()
    {
        $this->prospect->register(new HourlyRate(100, Currency::gbp()));
    }

    /**
     * @When I declare the Prospect as not interested
     */
    public function iDeclareTheProspectAsNotInterested()
    {
        $this->prospect->declareNotInterested();
    }

    /**
     * @When I give up on the Prospect
     */
    public function iGiveUpOnTheProspect()
    {
        $this->prospect->giveUp();
    }

    /**
     * @Then The date and time of the chase up should be recorded
     */
    public function theDateAndTimeOfTheChaseUpShouldBeRecorded()
    {
        $reflected = new \ReflectionProperty($this->prospect, 'chaseUps');
        $reflected->setAccessible(true);
        if (empty($reflected->getValue($this->prospect))) {
            throw new\Exception('The chase-up has not been recorded');
        }
    }

    /**
     * @Then The Prospect should be marked as :status
     */
    public function theProspectShouldBeMarkedAs(string $expected)
    {
        switch ($expected) {
            case 'registered':
                $status = ProspectStatus::REGISTERED;
                $this->eventShouldHaveBeenPublishedNamed(ProspectRegisteredEvent::EVENT_NAME);
                break;
            case 'in progress':
                $status = ProspectStatus::IN_PROGRESS;
                break;
            case 'not interested':
                $status = ProspectStatus::NOT_INTERESTED;
                $this->eventShouldHaveBeenPublishedNamed(ProspectNotInterestedEvent::EVENT_NAME);
                break;
            case 'not reachable':
                $status = ProspectStatus::NOT_REACHABLE;
                $this->eventShouldHaveBeenPublishedNamed(ProspectGivenUpOnEvent::EVENT_NAME);
                break;
            default:
                throw new \Exception(sprintf('Unknown status type:', $expected));
        }

        if ($this->getStatus()->isNot($status)) {
            throw new\Exception(
                sprintf('The prospect is not marked as %s', $status)
            );
        }
    }

    private function getStatus(): ProspectStatus
    {
        $reflected = new \ReflectionProperty($this->prospect, 'status');
        $reflected->setAccessible(true);
        return $reflected->getValue($this->prospect);
    }

    private function eventShouldHaveBeenPublishedNamed(string $eventName)
    {
        if ($this->eventPublisher->hasNotPublished($eventName)) {
            throw new \Exception(sprintf('An event with name %s event has not been published', $eventName));
        }
    }
}
