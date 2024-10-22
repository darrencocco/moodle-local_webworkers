@local_webworkers @javascript
Feature: Support of Shared Workers
  In order to support shared web workers
  As an admin
  I need to be able to observe that they are loading and responding.

  Scenario: Shared worker passing messages between tabs
    Given I log in as "admin"
    When I'm on the shared worker fixture page
    And I should not see "I have for you a modest proposal."
    And I open a tab named "secondary" on the current page
    And I click on "Send a message" "link"
    And I switch to the main tab
    Then I should see "I have for you a modest proposal."
    And I log out