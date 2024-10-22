@local_webworkers @javascript
Feature: Support of dedicated Workers
  In order to support dedicated Web Workers
  As an admin
  I need to be able to observe that they are loading and responding.

  Scenario: Dedicated Web Worker that echos back messages sent to it
    Given I log in as "admin"
    When I'm on the dedicated worker fixture page
    And I click on "Send a message" "link"
    Then I should see "I have for you a modest proposal."
    And I log out
    