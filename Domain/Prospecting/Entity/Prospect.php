<?php

class Prospect
{
    private $id;
    private $name;
    private $notes;

    /** @var DateTime[]  */
    private $chaseUps = [];

    /** @var ProspectStatus */
    private $status;

    private function __construct(ProspectId $id, string $name, string $notes)
    {
        $this->id = $id;
        $this->name = $name;
        $this->notes = $notes;
        $this->status = ProspectStatus::inProgress();
        /** Raise a 'prospect_received' event */
    }

    public function receive(ProspectId $id, string $name, string $notes)
    {
        return new self($id, $name, $notes);
    }

    public function chaseUp()
    {
        if (!$this->status->is(ProspectStatus::IN_PROGRESS)) {
            throw new Exception('Prospect does not have "in progress" status');
        }
        $this->chaseUps[] = new DateTime();
    }

    public function register()
    {
        if (!$this->status->is(ProspectStatus::IN_PROGRESS)) {
            throw new Exception('Prospect does not have "in progress" status');
        }
        $this->status = ProspectStatus::registered();
    }

    public function declareNotInterested()
    {
        if (!$this->status->is(ProspectStatus::IN_PROGRESS)) {
            throw new Exception('Prospect does not have "in progress" status');
        }
        $this->status = ProspectStatus::notInterested();
        /** Raise a 'prospect_not_interested' event */
    }

    public function giveUp()
    {
        if (!$this->status->is(ProspectStatus::IN_PROGRESS)) {
            throw new Exception('Prospect does not have "in progress" status');
        }
        $this->status = ProspectStatus::notReachable();
        /** Raise a 'prospect_not_reachable' event */
    }
}
