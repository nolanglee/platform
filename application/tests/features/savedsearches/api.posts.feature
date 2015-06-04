@oauth2Skip
Feature: Testing the Sets Posts API

    @resetFixture
    Scenario: Listing All Posts in a Set
        Given that I want to get all "Posts"
        When I request "/savedsearch/4/posts/"
        Then the response is JSON
        And the response has a "count" property
        And the type of the "count" property is "numeric"
        And the "count" property equals "4"
        Then the guzzle status code should be 200
