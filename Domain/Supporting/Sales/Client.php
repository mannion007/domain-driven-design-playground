<?php

class Client
{
    private $clientId;
    private $name;
    private $contactDetails;
    private $payAsYouGoRate;
    private $status;

    /** "new client" sounds OK, no point in guarding the constructor */
    public function __construct(string $name, ContactDetails $contactDetails, Money $payAsYouGoRate)
    {
        $this->clientId = new ClientId();
        $this->name = $name;
        $this->contactDetails = $contactDetails;
        $this->payAsYouGoRate = $payAsYouGoRate;
        $this->status = ClientStatus::active();
        /** Raise a 'new_client' event */
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
        /** Raise a client_service_suspended event */
    }

    public function resumeOperations()
    {
        if ($this->status->is(ClientStatus::ACTIVE)) {
            throw new Exception('Cannot resume operations of a Client that is not suspended');
        }
        $this->status = ClientStatus::active();
        /** Raise a client_operations_resumed event */
    }
}
