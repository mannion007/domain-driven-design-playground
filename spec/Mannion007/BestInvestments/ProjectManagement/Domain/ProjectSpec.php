<?php

namespace spec\Mannion007\BestInvestments\ProjectManagement\Domain;

use Mannion007\BestInvestments\ProjectManagement\Domain\ConsultationId;
use Mannion007\BestInvestments\ProjectManagement\Domain\ConsultationScheduledEvent;
use Mannion007\BestInvestments\ProjectManagement\Domain\Project;
use Mannion007\BestInvestments\ProjectManagement\Domain\ClientId;
use Mannion007\BestInvestments\ProjectManagement\Domain\ProjectDraftedEvent;
use Mannion007\BestInvestments\ProjectManagement\Domain\ProjectManagerId;
use Mannion007\BestInvestments\ProjectManagement\Domain\ProjectStatus;
use Mannion007\BestInvestments\ProjectManagement\Domain\SpecialistApprovedEvent;
use Mannion007\BestInvestments\ProjectManagement\Domain\SpecialistId;
use Mannion007\BestInvestments\ProjectManagement\Domain\ProjectClosedEvent;
use Mannion007\BestInvestments\ProjectManagement\Infrastructure\EventPublisher\InMemoryEventPublisher;
use Mannion007\BestInvestments\EventPublisher\EventPublisher;
use PhpSpec\ObjectBehavior;

/**
 * Class TimeIncrementSpec
 * @package spec\Mannion007\BestInvestments\ProjectManagement\Domain
 * @mixin Project
 */
class ProjectSpec extends ObjectBehavior
{
    /** @var InMemoryEventPublisher */
    private $publisher;

    function let()
    {
        /** Find before suite annotation to improve this */
        $this->publisher = new InMemoryEventPublisher();
        EventPublisher::registerPublisher($this->publisher);

        $clientId = ClientId::fromExisting('test123');
        $name = 'test-project';
        $deadline = (new \DateTime())->modify('+1 year');
        $this->beConstructedThrough('setUp', [$clientId, $name, $deadline]);

        $status = new \ReflectionProperty($this->getWrappedObject(), 'status');
        $status->setAccessible(true);
        $status->setValue($this->getWrappedObject(), ProjectStatus::active());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Project::class);
        if ($this->publisher->hasNotPublished(ProjectDraftedEvent::EVENT_NAME)) {
            throw new \Exception('An event should have been published when the Project was Drafted');
        }
    }

    function it_cannot_start_when_the_project_is_not_drafted()
    {
        $this->shouldThrow(new \Exception('Cannot Start a Project that is not in Draft state'))
            ->during('start', [ProjectManagerId::fromExisting('test-project-manager-id')]);
    }

    function it_can_start_when_the_project_is_drafted()
    {
        $status = new \ReflectionProperty($this->getWrappedObject(), 'status');
        $status->setAccessible(true);
        $status->setValue($this->getWrappedObject(), ProjectStatus::draft());
        $this->start(ProjectManagerId::fromExisting('test-project-manager-id'));
    }

    function it_cannot_close_when_it_has_an_open_consultation()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->approveSpecialist(SpecialistId::fromExisting('test'));
        $this->scheduleConsultation(SpecialistId::fromExisting('test'), (new \DateTime())->modify('+1 week'));
        $this->shouldThrow(
            new \Exception('Cannot close Project until all open Consultations have been either Confirmed or Discarded')
        )->during('close');
    }

    function it_can_close()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->approveSpecialist(SpecialistId::fromExisting('test'));
        $this->scheduleConsultation(SpecialistId::fromExisting('test'), (new \DateTime())->modify('+1 week'));
        $this->reportConsultation(new ConsultationId(0), 60);
        $this->close();
        if ($this->publisher->hasNotPublished(ProjectClosedEvent::EVENT_NAME)) {
            throw new \Exception('An event should have been published when the Project was Closed');
        }
    }

    function it_cannot_add_a_specialist_when_the_project_is_not_active()
    {
        $status = new \ReflectionProperty($this->getWrappedObject(), 'status');
        $status->setAccessible(true);
        $status->setValue($this->getWrappedObject(), ProjectStatus::draft());
        $this->shouldThrow()->during('addSpecialist', [SpecialistId::fromExisting('test')]);
    }

    function it_cannot_add_a_specialist_more_than_once()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->shouldThrow(new \Exception('Cannot add a specialist more than once'))
            ->during('addSpecialist', [SpecialistId::fromExisting('test')]);
    }

    function it_can_add_a_specialist()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
    }

    function it_cannot_approve_a_specialist_that_has_not_been_added()
    {
        $this->shouldThrow(new \Exception('Cannot approve a Specialist that is not un-vetted'))
            ->during('approveSpecialist', [SpecialistId::fromExisting('test')]);
    }

    function it_cannot_approve_a_specialist_that_is_not_unvetted()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->approveSpecialist(SpecialistId::fromExisting('test'));
        $this->shouldThrow(new \Exception('Cannot approve a Specialist that is not un-vetted'))
            ->during('approveSpecialist', [SpecialistId::fromExisting('test')]);
    }

    function it_can_approve_a_specialist()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->approveSpecialist(SpecialistId::fromExisting('test'));
        if ($this->publisher->hasNotPublished(SpecialistApprovedEvent::EVENT_NAME)) {
            throw new \Exception('An event should have been published when the Specialist was Approved');
        }
    }

    function it_cannot_discard_specialists_that_have_not_been_added()
    {
        $this->shouldThrow(new \Exception('Cannot discard a Specialist that is not un-vetted'))
            ->during('discardSpecialist', [SpecialistId::fromExisting('test')]);
    }

    function it_cannot_discard_specialists_that_are_not_unvetted()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->approveSpecialist(SpecialistId::fromExisting('test'));
        $this->shouldThrow(new \Exception('Cannot discard a Specialist that is not un-vetted'))
            ->during('discardSpecialist', [SpecialistId::fromExisting('test')]);
    }

    function it_can_discard_a_specialists()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->discardSpecialist(SpecialistId::fromExisting('test'));
    }

    function it_cannot_schedule_a_consultaiton_when_it_is_not_active()
    {
        $status = new \ReflectionProperty($this->getWrappedObject(), 'status');
        $status->setAccessible(true);
        $status->setValue($this->getWrappedObject(), ProjectStatus::draft());

        $this->shouldThrow(new \Exception('Cannot schedule a Consultation for a Project that is not active'))
            ->during('scheduleConsultation', [SpecialistId::fromExisting('test'), new \DateTime()]);
    }

    function it_cannot_schedule_a_consultation_with_a_specialist_that_has_not_been_approved()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->shouldThrow(new \Exception('Cannot schedule a Consultation with a Specialist that is not approved'))
            ->during('scheduleConsultation', [SpecialistId::fromExisting('test'), new \DateTime()]);
    }

    function it_can_schedules_a_consultation()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->approveSpecialist(SpecialistId::fromExisting('test'));
        $this->scheduleConsultation(SpecialistId::fromExisting('test'), new \DateTime());
        if ($this->publisher->hasNotPublished(ConsultationScheduledEvent::EVENT_NAME)) {
            throw new \Exception('An event should have been published when the Consultation was Scheduled');
        }
    }

    function it_can_report_a_consultation()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->approveSpecialist(SpecialistId::fromExisting('test'));
        $consultationId = $this->scheduleConsultation(SpecialistId::fromExisting('test'), new \DateTime());
        $this->reportConsultation($consultationId, 60);
    }

    function it_can_discard_a_consultation()
    {
        $this->addSpecialist(SpecialistId::fromExisting('test'));
        $this->approveSpecialist(SpecialistId::fromExisting('test'));
        $consultationId = $this->scheduleConsultation(SpecialistId::fromExisting('test'), new \DateTime());
        $this->discardConsultation($consultationId);
    }

    function it_cannot_be_put_on_hold_when_it_is_not_active()
    {
        $status = new \ReflectionProperty($this->getWrappedObject(), 'status');
        $status->setAccessible(true);
        $status->setValue($this->getWrappedObject(), ProjectStatus::draft());
        $this->shouldThrow(new \Exception('Cannot put a Project On Hold when it is not Active'))
            ->during('putOnHold');
    }

    function it_can_be_put_on_hold()
    {
        $this->putOnHold();
    }

    function it_cannot_be_reactivated_when_it_is_not_on_hold()
    {
        $this->shouldThrow(new \Exception('Cannot Reactivate a Project that is not On Hold'))
            ->during('reactivate');
    }

    function it_can_be_reactivated()
    {
        $status = new \ReflectionProperty($this->getWrappedObject(), 'status');
        $status->setAccessible(true);
        $status->setValue($this->getWrappedObject(), ProjectStatus::onHold());
        $this->reactivate();
    }
}
