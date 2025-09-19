Feature: List Agent

  Background:
    Given the following accounts exist:
      | Name        | Email                   | Password  | Team    |
      | Sally Brown | sally.brown@example.org | AF@k3P@ss | Example |
      | Tim Brown   | tim.brown@example.org   | AF@k3P@ss | Example |
      | Sally Braun | sally.braun@example.org | AF@k3Pass | Second  |

  Scenario: Successfully list agents
    Given I have logged in as "sally.brown@example.org" with the password "AF@k3P@ss"
    And there are the following agents:
       | Name         | Project |
       | Marcus Vance | DevHelm |
       | Lisa Wilson  | DevHelm |
    When I view the list of agents
    Then I should see the agent "Marcus Vance" in the list
    And I should see the agent "Lisa Wilson" in the list