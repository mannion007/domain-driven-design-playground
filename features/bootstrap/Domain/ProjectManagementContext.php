<?php

namespace Mannion007\BestInvestmentsBehat\Domain;

use Behat\Behat\Context\Context;
use Mannion007\BestInvestments\Domain\Invoicing\ConsultationId;
use Mannion007\BestInvestments\Domain\ProjectManagement\ClientId;
use Mannion007\BestInvestments\Domain\ProjectManagement\ConsultationCollection;
use Mannion007\BestInvestments\Domain\ProjectManagement\ConsultationScheduledEvent;
use Mannion007\BestInvestments\Domain\ProjectManagement\Project;
use Mannion007\BestInvestments\Domain\ProjectManagement\ProjectClosedEvent;
use Mannion007\BestInvestments\Domain\ProjectManagement\ProjectDraftedEvent;
use Mannion007\BestInvestments\Domain\ProjectManagement\SpecialistApprovedEvent;
use Mannion007\BestInvestments\Domain\ProjectManagement\SpecialistDiscardedEvent;
use Mannion007\BestInvestments\Domain\ProjectManagement\ProjectManagerId;
use Mannion007\BestInvestments\Domain\ProjectManagement\ProjectStatus;
use Mannion007\BestInvestments\Domain\ProjectManagement\SpecialistCollection;
use Mannion007\BestInvestments\Domain\ProjectManagement\SpecialistId;
use Mannion007\BestInvestments\Event\EventPublisher;
use Mannion007\BestInvestments\Event\InMemoryHandler;

/**
 * Defines application features from the specific context.
 */
class ProjectManagementContext implements Context
{
    /** @var InMemoryHandler */
    private $eventHandler;

    /** @var ClientId */
    private $clientId;

    /** @var ProjectManagerId */
    private $projectManagerId;

    /** @var Project */
    private $project;

    /** @var SpecialistId */
    private $specialistId;

    /** @var ConsultationId */
    private $consultationId;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->eventHandler = new InMemoryHandler();
        EventPublisher::registerHandler($this->eventHandler);

        $this->clientId = ClientId::fromExisting('test-client-123');
        $this->projectManagerId = ProjectManagerId::fromExisting('project-manager-123');
        $this->specialistId = SpecialistId::fromExisting('test-specialist-id');
    }

    /**
     * @Given I have a Client
     */
    public function iHaveAClient()
    {
    }

    /**
     * @Given I have a Specialist
     */
    public function iHaveASpecialist()
    {
    }

    /**
     * @Given I have a drafted Project
     */
    public function iHaveADraftedProject()
    {
        $this->project = Project::setUp($this->clientId, 'My Lovely Project', new \DateTime('+1 year'));
    }

    /**
     * @Given I have an active Project
     */
    public function iHaveAnActiveProject()
    {
        $this->project = Project::setUp($this->clientId, 'My Lovely Project', new \DateTime('+1 year'));
        $this->project->start($this->projectManagerId);
    }

    /**
     * @Given I have an on hold Project
     */
    public function iHaveAnOnHoldProject()
    {
        $this->project = Project::setUp($this->clientId, 'My Lovely Project', new \DateTime('+1 year'));
        $this->project->start($this->projectManagerId);
        $this->project->putOnHold();
    }

    /**
     * @Given The project has an un-vetted Specialist
     */
    public function theProjectHasAnUnvettedSpecialist()
    {
        $this->project->addSpecialist($this->specialistId);
    }

    /**
     * @Given The Specialist is approved for the Project
     */
    public function theSpecialistIsApprovedForTheProject()
    {
        $this->project->addSpecialist($this->specialistId);
        $this->project->approveSpecialist($this->specialistId);
    }

    /**
     * @When I Set Up a Project for the Client with the name :name and the deadline :deadline
     */
    public function iSetUpAProjectForTheClientWithTheNameAndTheDeadline($name, $deadline)
    {
        $deadline = \DateTime::createFromFormat('Y-m-d', $deadline);
        $this->project = Project::setUp($this->clientId, $name, $deadline);
    }

    /**
     * @When I assign a Project Manager to the Project
     */
    public function iAssignAProjectManagerToTheProject()
    {
        $this->project->start($this->projectManagerId);
    }

    /**
     * @When I close the Project
     */
    public function iCloseTheProject()
    {
        $this->project->close();
    }

    /**
     * @When I put the Project on hold
     */
    public function iPutTheProjectOnHold()
    {
        $this->project->putOnHold();
    }

    /**
     * @When I reactivate the Project
     */
    public function iReactivateTheProject()
    {
        $this->project->reactivate();
    }

    /**
     * @When I add the Specialist to the Project
     */
    public function iAddTheSpecialistToTheProject()
    {
        $this->project->addSpecialist($this->specialistId);
    }

    /**
     * @When I approve the Specialist
     */
    public function iApproveTheSpecialist()
    {
        $this->project->approveSpecialist($this->specialistId);
    }

    /**
     * @When I discard the Specialist
     */
    public function iDiscardTheSpecialist()
    {
        $this->project->discardSpecialist($this->specialistId);
    }

    /**
     * @Then I should have a Draft of a Project
     */
    public function iShouldHaveADraftOfAProject()
    {
        if ($this->project->isNot(ProjectStatus::DRAFT)) {
            throw new \Exception('The project is not drafted');
        }
    }

    /**
     * @Then I should get a Project Reference
     */
    public function iShouldGetAProjectReference()
    {
        if (is_null($this->project->getReference())) {
            throw new \Exception('I did not get a Project Reference');
        }
    }

    /**
     * @Then The Project should be marked as active
     */
    public function theProjectShouldBeMarkedAsActive()
    {
        if ($this->project->isNot(ProjectStatus::ACTIVE)) {
            throw new \Exception('The Project is not active');
        }
    }

    /**
     * @Then The Project should be marked as closed
     */
    public function theProjectShouldBeMarkedAsClosed()
    {
        if ($this->project->isNot(ProjectStatus::CLOSED)) {
            throw new \Exception('The Project is not closed');
        }
    }

    /**
     * @Then Specialists can be added to the Project
     */
    public function specialistsCanBeAddedToTheProject()
    {
        $this->project->addSpecialist(SpecialistId::fromExisting('test-specialist-id'));
    }

    /**
     * @Then The Specialist should be added and marked as un-vetted
     */
    public function theSpecialistShouldBeAddedAndMarkedAsUnvetted()
    {
        $reflectionProperty = new \ReflectionProperty($this->project, 'unvettedSpecialists');
        $reflectionProperty->setAccessible(true);
        /** @var SpecialistCollection */
        $unvettedSpecialists = $reflectionProperty->getValue($this->project);
        if (!$unvettedSpecialists->contains($this->specialistId)) {
            throw new \Exception('The Specialist is not marked as un-vetted');
        }
    }

    /**
     * @Then The Specialist should be marked as approved
     */
    public function theSpecialistShouldBeMarkedAsApproved()
    {
        $reflectionProperty = new \ReflectionProperty($this->project, 'approvedSpecialists');
        $reflectionProperty->setAccessible(true);
        /** @var SpecialistCollection */
        $approvedSpecialists = $reflectionProperty->getValue($this->project);
        if (!$approvedSpecialists->contains($this->specialistId)) {
            throw new \Exception('The Specialist is not marked as approved');
        }
    }

    /**
     * @Then The Specialist should be marked as discarded
     */
    public function theSpecialistShouldBeMarkedAsDiscarded()
    {
        $reflectionProperty = new \ReflectionProperty($this->project, 'discardedSpecialists');
        $reflectionProperty->setAccessible(true);
        /** @var SpecialistCollection */
        $discardedSpecialists = $reflectionProperty->getValue($this->project);
        if (!$discardedSpecialists->contains($this->specialistId)) {
            throw new \Exception('The Specialist is not marked as discarded');
        }
    }

    /**
     * @When I schedule a Consultation with the Specialist on the Project
     */
    public function iScheduleAConsultationWithTheSpecialistOnTheProject()
    {
        $this->consultationId = $this->project->scheduleConsultation($this->specialistId, new \DateTime('+1 week'));
    }

    /**
     * @Then The Consultation should be scheduled with the Specialist on the Project
     */
    public function theConsultationShouldBeScheduledWithTheSpecialistOnTheProject()
    {
        $reflected = new \ReflectionProperty($this->project, 'consultations');
        $reflected->setAccessible(true);
        /** @var ConsultationCollection */
        $consultations = $reflected->getValue($this->project);
        if (!$consultations->contains($this->consultationId)) {
            throw new \Exception('The Consultation has not been scheduled on the Project');
        }
    }

    /**
     * @Then The Project should be marked as on hold
     */
    public function theProjectShouldBeMarkedAsOnHold()
    {
        if ($this->project->isNot(ProjectStatus::ON_HOLD)) {
            throw new \Exception('The Project is not on hold');
        }
    }

    /**
     * @Then A Senior Project Manager should be notified that the Project has been drafted
     */
    public function aSeniorProjectManagerShouldBeNotifiedThatTheProjectHasBeenDrafted()
    {
        if ($this->eventHandler->hasNotPublished(ProjectDraftedEvent::EVENT_NAME)) {
            throw new \Exception('A Senior Project Manager has not been notified that the Project has been drafted');
        }
    }

    /**
     * @Then The Invoicing Team should be notified that the Project has closed
     */
    public function theInvoicingTeamShouldBeNotifiedThatTheProjectHasClosed()
    {
        if ($this->eventHandler->hasNotPublished(ProjectClosedEvent::EVENT_NAME)) {
            throw new \Exception('The Invoicing Team has not been notified the Project has closed');
        }
    }

    /**
     * @Then The Project Management team should be notified that the Specialist has been approved
     */
    public function theProjectManagementTeamShouldBeNotifiedThatTheSpecialistHasBeenApproved()
    {
        if ($this->eventHandler->hasNotPublished(SpecialistApprovedEvent::EVENT_NAME)) {
            throw new \Exception('The Project Management Team has not been notified the Specialist has been approved');
        }
    }

    /**
     * @Then The Project Management team should be notified that the Specialist has been discarded
     */
    public function theProjectManagementTeamShouldBeNotifiedThatTheSpecialistHasBeenDiscarded()
    {
        if ($this->eventHandler->hasNotPublished(SpecialistDiscardedEvent::EVENT_NAME)) {
            throw new \Exception('The Project Management Team has not been notified the Specialist has been discarded');
        }
    }

    /**
     * @Then The Project Management Team should be notified that the Consultation has been scheduled
     */
    public function theProjectManagementTeamShouldBeNotifiedThatTheConsultationHasBeenScheduled()
    {
        if ($this->eventHandler->hasNotPublished(ConsultationScheduledEvent::EVENT_NAME)) {
            throw new \Exception(
                'The Project Management Team has not been notified the Consultation has been scheduled'
            );
        }
    }
}