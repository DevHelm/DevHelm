Feature: DH-10 Create Agent in the Application
  As a team member
  I want to create an agent for my project
  So that I can automate tasks and workflows

  Background:
    Given the following teams exist:
      | Name    | Plan     |
      | Example | Standard |
      | Second  | Basic    |
    Given the following accounts exist:
      | Name        | Email                   | Password  | Team    |
      | Sally Brown | sally.brown@example.org | AF@k3P@ss | Example |
      | Tim Brown   | tim.brown@example.org   | AF@k3P@ss | Example |
      | Sally Braun | sally.braun@example.org | AF@k3Pass | Second  |

  Scenario: Successfully Create an Agent
    Given I have logged in as "sally.brown@example.org" with the password "AF@k3P@ss"
    And I sent an invite to "john.brown@example.org"
    When I create an agent:
     | Name    | Marcus Vance| 
     | Project | DH          | 
    Then there should be an agent called "Marcus Vance"
    
  Scenario: Fail to create an Agent - no project
    Given I have logged in as "sally.brown@example.org" with the password "AF@k3P@ss"
    And I sent an invite to "john.brown@example.org"
    When I create an agent:
     | Name    | Marcus Vance| 
     | Project |             | 
    Then there should not be an agent called "Marcus Vance"
    And there should be an validation for no project