<?php

namespace Mannion007\BestInvestments\Prospecting\Command;

class DeclareProspectNotInterestedCommand
{
    private $prospectId;

    public function __construct(string $prospectId)
    {
        $this->prospectId = $prospectId;
    }

    public function getProspectId(): string
    {
        return $this->prospectId;
    }

    public static function fromPayload(array $payload): DeclareProspectNotInterestedCommand
    {
        return new self($payload['prospect_id']);
    }
}
