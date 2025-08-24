Feature: Create Agent

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
    And there will be an API key for the agent called "Marcus Vance"