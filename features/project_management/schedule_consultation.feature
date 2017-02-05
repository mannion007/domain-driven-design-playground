Feature:
  As a Research Manager
  I want to be able schedule Consultations on Projects
  So that a Consultations can take place between Clients and Specialists

  Scenario: Scheduling a Consultation
    Given I have an active Project
    And I have a Specialist
    And The Specialist is approved for the Project
    When I schedule a Consultation with the Specialist on the Project
    Then The Consultation should be scheduled with the Specialist on the Project
    And The Project Management Team should be notified that the Consultation has been scheduled