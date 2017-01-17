<?php

namespace Mannion007\BestInvestments\Domain\Prospecting;

use Mannion007\BestInvestments\Event\EventInterface;

class ProspectRegistered implements EventInterface
{
    const EVENT_NAME = 'prospect_registered';

    private $prospectId;
    private $hourlyRate;
    private $occurredAt;

    public function __construct(
        ProspectId $prospectId,
        Money $hourlyRate,
        \DateTime $occurredAt = null
    ) {
        $this->prospectId = $prospectId;
        $this->hourlyRate = $hourlyRate;
        $this->occurredAt = is_null($occurredAt) ? new \DateTime() : $occurredAt;
    }

    public function getProspectId(): ProspectId
    {
        return $this->prospectId;
    }

    public function getHourlyRate(): string
    {
        return $this->hourlyRate;
    }

    public function getEventName() : string
    {
        return self::EVENT_NAME;
    }

    public function getOccurredAt() : \DateTime
    {
        return $this->occurredAt;
    }

    public function getPayload(): array
    {
        return
        [
            'prospect_id' => (string)$this->prospectId,
            'hourly_rate' => $this->hourlyRate
        ];
    }

    public static function fromPayload(array $payload) : ProspectRegistered
    {
        return new self(
            ProspectId::fromExisting($payload['prospect_id']),
            $payload['hourly_rate']
        );
    }
}
