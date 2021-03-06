<?php

namespace Mannion007\BestInvestmentsBehat\ProjectManagement;

use Behat\Behat\Context\Context;
use Pavlakis\Slim\Behat\Context\App;
use Pavlakis\Slim\Behat\Context\KernelAwareContext;
use \GuzzleHttp\Client;

/**
 * Defines application features from the specific context.
 */
class ApiContext implements Context, KernelAwareContext
{
    use App;

    private $clientId;
    private $projectManagerId;
    private $projectReference;
    private $specialistId;
    private $consultationId;
    private $guzzle;

    public function __construct()
    {
        $this->clientId = 'test-client-123';
        $this->projectManagerId = 'project-manager-123';
        $this->specialistId = 'test-specialist-id';
        $this->guzzle = new Client();
    }

    /** @BeforeScenario */
    public function before()
    {
        $this->app->getContainer()->get('redis_project_repository')->purge();
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
     * @Given I have a Project Manager
     */
    public function iHaveAProjectManager()
    {
    }

    /**
     * @Given The Specialist has not been added to the Project
     */
    public function theSpecialistHasNotBeenAddedToTheProject()
    {
    }

    /**
     * @Given I have a drafted Project
     */
    public function iHaveADraftedProject()
    {
        $response = $this->guzzle->post(
            sprintf('%s/project/set_up', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'name' => 'What could go wrong?',
                    'deadline' => '2020-05-25'
                ]
            ]
        );
        $decodedResponse = json_decode($response->getBody()->getContents());
        $this->projectReference = $decodedResponse->project_reference;
    }

    /**
     * @Given I have an active Project
     */
    public function iHaveAnActiveProject()
    {
        $response = $this->guzzle->post(
            sprintf('%s/project/set_up', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'name' => 'What could go wrong?',
                    'deadline' => '2020-05-25'
                ]
            ]
        );

        $decodedResponse = json_decode($response->getBody()->getContents());
        $this->projectReference = $decodedResponse->project_reference;

        $this->guzzle->post(
            sprintf('%s/project/start', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'project_manager_id' => $this->projectManagerId
                ]
            ]
        );
    }

    /**
     * @Given The Project has no open Consultations
     */
    public function theProjectHasNoOpenConsultations()
    {
    }

    /**
     * @Given The Project has an open Consultation
     */
    public function theProjectHasAnOpenConsultation()
    {
        $this->guzzle->post(
            sprintf('%s/project/add_specialist', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId
                ]
            ]
        );
        $this->guzzle->post(
            sprintf('%s/project/approve_specialist', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId
                ]
            ]
        );
        $response = $this->guzzle->post(
            sprintf('%s/project/schedule_consultation', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId,
                    'time' => '2020-05-15'
                ]
            ]
        );
        $decodedResponse = json_decode($response->getBody()->getContents());
        $this->consultationId = $decodedResponse->consultation_id;
    }

    /**
     * @Given I have a Potential Specialist
     */
    public function iHaveAPotentialSpecialist()
    {
    }

    /**
     * @When I report the Consultation
     */
    public function iReportTheConsultation()
    {
        $this->guzzle->post(
            sprintf('%s/project/report_consultation', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'consultation_id' => $this->consultationId,
                    'duration' => '60'
                ]
            ]
        );
    }

    /**
     * @When I discard the Consultation
     */
    public function iDiscardTheConsultation()
    {
        $this->guzzle->post(
            sprintf('%s/project/discard_consultation', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'consultation_id' => $this->consultationId
                ]
            ]
        );
    }

    /**
     * @Given The project has an un-vetted Specialist
     */
    public function theProjectHasAnUnvettedSpecialist()
    {
        $this->guzzle->post(
            sprintf('%s/project/add_specialist', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId
                ]
            ]
        );
    }

    /**
     * @Given The Specialist is approved for the Project
     */
    public function theSpecialistIsApprovedForTheProject()
    {
        $this->guzzle->post(
            sprintf('%s/project/add_specialist', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId
                ]
            ]
        );
        $this->guzzle->post(
            sprintf('%s/project/approve_specialist', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId
                ]
            ]
        );
    }

    /**
     * @When I Set Up a Project for the Client with the name :name and the deadline :deadline
     */
    public function iSetUpAProjectForTheClientWithTheNameAndTheDeadline(string $name, string $deadline)
    {
        $response = $this->guzzle->post(
            sprintf('%s/project/set_up', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'name' => $name,
                    'deadline' => $deadline
                ]
            ]
        );
        $decodedResponse = json_decode($response->getBody()->getContents());
        $this->projectReference = $decodedResponse->project_reference;
    }

    /**
     * @When I assign the Project Manager to the Project
     */
    public function iAssignTheProjectManagerToTheProject()
    {
        $this->guzzle->post(
            sprintf('%s/project/start', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'project_manager_id' => $this->projectManagerId
                ]
            ]
        );
    }

    /**
     * @When I close the Project
     */
    public function iCloseTheProject()
    {
        $this->guzzle->post(
            sprintf('%s/project/close', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference
                ]
            ]
        );
    }

    /**
     * @When I add the Specialist to the Project
     */
    public function iAddTheSpecialistToTheProject()
    {
        $this->guzzle->post(
            sprintf('%s/project/add_specialist', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId
                ]
            ]
        );
    }

    /**
     * @When I approve the Specialist
     */
    public function iApproveTheSpecialist()
    {
        $this->guzzle->post(
            sprintf('%s/project/approve_specialist', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId
                ]
            ]
        );
        $this->eventShouldHaveBeenPublishedNamed('specialist_approved');
    }

    /**
     * @When I discard the Specialist
     */
    public function iDiscardTheSpecialist()
    {
        $this->guzzle->post(
            sprintf('%s/project/discard_specialist', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId
                ]
            ]
        );
        $this->eventShouldHaveBeenPublishedNamed('specialist_discarded');
    }

    /**
     * @Then I should have a Draft of a Project
     */
    public function iShouldHaveADraftOfAProject()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/project/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->projectReference
            )
        );
        $decodedResponse = json_decode($response->getBody());
        if (200 !== $response->getStatusCode()) {
            throw new \Exception('I do not have a Project');
        }
        if ($decodedResponse->status->status !== 'draft') {
            throw new \Exception('The Project is not marked as a draft');
        }
        $this->eventShouldHaveBeenPublishedNamed('project_drafted');
    }

    /**
     * @Then The Project should be marked as active
     */
    public function theProjectShouldBeMarkedAsActive()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/project/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->projectReference
            )
        );
        $decodedResponse = json_decode($response->getBody());
        if ($decodedResponse->status->status !== 'active') {
            throw new \Exception('The project is not marked as active');
        }
        $this->eventShouldHaveBeenPublishedNamed('project_started');
    }

    /**
     * @Then The Project should be marked as closed
     */
    public function theProjectShouldBeMarkedAsClosed()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/project/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->projectReference
            )
        );
        $decodedResponse = json_decode($response->getBody());
        if ($decodedResponse->status->status !== 'closed') {
            throw new \Exception('The project is not marked as closed');
        }
        $this->eventShouldHaveBeenPublishedNamed('project_closed');
    }

    /**
     * @Then Specialists can be added to the Project
     */
    public function specialistsCanBeAddedToTheProject()
    {
        $this->guzzle->post(
            sprintf('%s/project/add_specialist', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId
                ]
            ]
        );
    }

    /**
     * @Then The Specialist should be added and marked as un-vetted
     */
    public function theSpecialistShouldBeAddedAndMarkedAsUnvetted()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/project/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->projectReference
            )
        );
        $decodedResponse = json_decode($response->getBody());
        foreach ($decodedResponse->unvetted_specialists->specialists as $specialist) {
            if ($specialist->specialist_id === $this->specialistId) {
                return true;
            }
        }
        throw new \Exception('The Specialist is not added and marked as un-vetted');
    }

    /**
     * @Then The Specialist should be marked as approved
     */
    public function theSpecialistShouldBeMarkedAsApproved()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/project/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->projectReference
            )
        );
        $decodedResponse = json_decode($response->getBody());
        foreach ($decodedResponse->approved_specialists->specialists as $specialist) {
            if ($specialist->specialist_id === $this->specialistId) {
                return true;
            }
        }
        throw new \Exception('The Specialist is not marked as approved');
    }

    /**
     * @Then The Specialist should be marked as discarded
     */
    public function theSpecialistShouldBeMarkedAsDiscarded()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/project/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->projectReference
            )
        );
        $decodedResponse = json_decode($response->getBody());
        foreach ($decodedResponse->discarded_specialists->specialists as $specialist) {
            if ($specialist->specialist_id === $this->specialistId) {
                return true;
            }
        }
        throw new \Exception('The Specialist is not marked as discarded');
    }

    /**
     * @When I schedule a Consultation with the Specialist on the Project
     */
    public function iScheduleAConsultationWithTheSpecialistOnTheProject()
    {
        //Add time?
        $response = $this->guzzle->post(
            sprintf('%s/project/schedule_consultation', $this->app->getContainer()->get('project_management_base_uri')),
            [
                'form_params' => [
                    'project_reference' => $this->projectReference,
                    'specialist_id' => $this->specialistId,
                    'time' => '2020-05-15'
                ]
            ]
        );
        $decodedResponse = json_decode($response->getBody()->getContents());
        $this->consultationId = $decodedResponse->consultation_id;

        $this->eventShouldHaveBeenPublishedNamed('consultation_scheduled');
    }

    /**
     * @When I add the Specialist to the list
     */
    public function iAddTheSpecialistToTheList()
    {
        $response = $this->guzzle->post(
            sprintf(
                '%s/potential_specialist/put_on_list',
                $this->app->getContainer()->get('project_management_base_uri')
            ),
            [
                'form_params' => [
                    'project_manager_id' => $this->projectManagerId,
                    'name' => 'Test Specialist',
                    'notes' => 'This is just a test'
                ]
            ]
        );
        $decodedResponse = json_decode($response->getBody()->getContents());
        $this->specialistId = $decodedResponse->specialist_id;
        $this->eventShouldHaveBeenPublishedNamed('specialist_put_on_list');
    }

    /**
     * @Then The Consultation should be scheduled with the Specialist on the Project
     */
    public function theConsultationShouldBeScheduledWithTheSpecialistOnTheProject()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/project/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->projectReference
            )
        );
        $decodedResponse = json_decode($response->getBody());
        foreach ($decodedResponse->consultations->consultations as $consultation) {
            if ($consultation->consultation_id->consultation_id === $this->consultationId) {
                return true;
            }
        }
        throw new \Exception('The Consultation has not been scheduled with the Specialist on the Project');
    }

    /**
     * @Then The Consultation should be marked as confirmed
     */
    public function theConsultationShouldBeMarkedAsConfirmed()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/project/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->projectReference
            )
        );
        $decodedResponse = json_decode($response->getBody());
        if ($decodedResponse->consultations->consultations[$this->consultationId]->status->status !== 'confirmed') {
            throw new \Exception('The Consultation has not been marked as confirmed');
        }
    }

    /**
     * @Then The Consultation should be marked as discarded
     */
    public function theConsultationShouldBeMarkedAsDiscarded()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/project/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->projectReference
            )
        );
        $decodedResponse = json_decode($response->getBody());
        if ($decodedResponse->consultations->consultations[$this->consultationId]->status->status !== 'discarded') {
            throw new \Exception('The Consultation has not been marked as discarded');
        }
    }

    /**
     * @Then I should have a Potential Specialist
     */
    public function iShouldHaveAPotentialSpecialist()
    {
        $response = $this->guzzle->get(
            sprintf(
                '%s/potential_specialist/%s',
                $this->app->getContainer()->get('project_management_base_uri'),
                $this->specialistId
            )
        );
        if (200 !== $response->getStatusCode()) {
            throw new \Exception('I do not have a Potential Specialist');
        }
    }

    private function eventShouldHaveBeenPublishedNamed(string $eventName)
    {
        $eventPublisher = $this->app->getContainer()->get('project_management_redis_publisher');
        if ($eventPublisher->hasNotPublished($eventName)) {
            throw new \Exception(
                'The event has not been published'
            );
        }
    }
}
