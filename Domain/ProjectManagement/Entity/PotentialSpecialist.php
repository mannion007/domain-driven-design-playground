<?php

class PotentialSpecialist
{
    private $id;
    private $projectManagerId;
    private $name;
    private $notes;

    private function __construct(
        ProjectManagerId $projectManagerId,
        string $name,
        string $notes
    ) {
        $this->id = new SpecialistId();
        $this->projectManagerId = $projectManagerId;
        $this->name = $name;
        $this->notes = $notes;
        /** Raise a 'potential_specialist_put_on_list' event */
    }

    public static function putOnList(ProjectManagerId $projectManagerId, string $notes, string $name)
    {
        return new self($projectManagerId, $name, $notes);
    }

    public function register()
    {
        return Specialist::register($this->id, $this->name);
    }
}
