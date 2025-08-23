Feature: Create Agent
  In order to manage my development agents
  As a team member
  I need to be able to create new agents for my team

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

  Scenario: Successfully create a new agent
    Given I have logged in as "sally.brown@example.org" with the password "AF@k3P@ss"
    When I visit the agents page
    And I click on "Create Agent"
    And I fill in the agent name with "DevBot"
    And I fill in the project with "DH"
    And I click "Submit"
    Then I should see "DevBot" in the agents list
    And I should see "DH" as the project for "DevBot"

  Scenario: Create agent with validation errors
    Given I have logged in as "sally.brown@example.org" with the password "AF@k3P@ss"
    When I visit the agents page
    And I click on "Create Agent"
    And I fill in the agent name with ""
    And I fill in the project with ""
    And I click "Submit"
    Then I should see validation errors for required fields

  Scenario: Create agent with duplicate name
    Given I have logged in as "sally.brown@example.org" with the password "AF@k3P@ss"
    And an agent with name "TestBot" already exists
    When I visit the agents page
    And I click on "Create Agent"
    And I fill in the agent name with "TestBot"
    And I fill in the project with "DH"
    And I click "Submit"
    Then I should see an error message "Agent with this name already exists"

  Scenario: View agents list
    Given I have logged in as "sally.brown@example.org" with the password "AF@k3P@ss"
    And the following agents exist for my team:
      | Name    | Project |
      | DevBot  | DH      |
      | TestBot | WEB     |
    When I visit the agents page
    Then I should see "DevBot" in the agents list
    And I should see "TestBot" in the agents list
    And I should see "DH" as the project for "DevBot"
    And I should see "WEB" as the project for "TestBot"

  Scenario: Empty agents list
    Given I have logged in as "sally.brown@example.org" with the password "AF@k3P@ss"
    And my team has no agents
    When I visit the agents page
    Then I should see "No agents found" or similar empty state message