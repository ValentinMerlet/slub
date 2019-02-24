Feature: Improve the feedback delay between the squad and the continuous integration (CI) status
  In order to accelerate the feedback loop between the squad and the CI status of pull request (PR)
  As a squad
  We want to be notified of the PR CI status updates

  @nominal
  Scenario: Notify the squad when the CI is green for a PR
    Given a PR in review
    When the CI is green for the PR
    Then the PR should be green
    And the squad should be notified that the ci is green for the PR

  @nominal
  Scenario: Notify the squad when the CI is red for a PR
    Given a PR in review
    When the CI is red for the PR
    Then the PR should be red
    And the squad should be notified that the ci is red for the PR

  @secondary
  Scenario: It does not notify CI status changes for unsupported repositories
    When the CI status changes for a PR belonging to an unsupported repository
    Then the squad should not be not notified