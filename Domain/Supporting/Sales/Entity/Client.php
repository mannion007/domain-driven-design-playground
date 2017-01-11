<?php

class Client
{
    private $clientId;
    private $payAsYouGoRate;
    private $status;

    /** "new client" sounds OK, no point in guarding the constructor */
    public function __construct(ClientId $clientId, Money $payAsYouGoRate)
    {
        $this->clientId = $clientId;
        $this->payAsYouGoRate = $payAsYouGoRate;
        $this->status = ClientStatus::active();
        /** Raise a new_client event */
    }

    public function purchasePackage(
        string $name,
        DateTime $startDate,
        PackageDuration $months,
        int $nominalHours
    ) {
        return new Package($this->clientId, $name, $startDate, $months, $nominalHours);
    }

    public function suspendService()
    {
        if ($this->status->is(ClientStatus::SUSPENDED)) {
            throw new Exception('Cannot suspend the Service of a Client when it is already suspended');
        }
        $this->status = ClientStatus::suspended();
    }

    public function resumeOperations()
    {
        if ($this->status->is(ClientStatus::ACTIVE)) {
            throw new Exception('Cannot resume operations of a Client that is not suspended');
        }
        $this->status = ClientStatus::active();
    }
}