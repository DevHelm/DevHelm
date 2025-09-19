Feature: Update Agent

  Background:
    Given the following accounts exist:
      | Name        | Email                   | Password  | Team    |
      | Sally Brown | sally.brown@example.org | AF@k3P@ss | Example |
      | Tim Brown   | tim.brown@example.org   | AF@k3P@ss | Example |
      | Sally Braun | sally.braun@example.org | AF@k3Pass | Second  |

  Scenario: Update Agent info
    When I have logged in as "sally.brown@example.org" with the password "AF@k3P@ss"
    And there are the following agents:
       | Name         | Project |
       | Marcus Vance | DevHelm |
       | Lisa Wilson  | DevHelm |
    When I update the agent info via the APP for "Marcus Vance":
      | Name          | Cool Guy |
      | Project       | DevHelm  |
    Then there should be an agent called "Cool Guy"