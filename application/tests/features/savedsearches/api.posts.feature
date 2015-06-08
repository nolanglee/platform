@oauth2Skip
Feature: Testing the Sets Posts API

	@resetFixture
	Scenario: Listing posts within a savedsearch
		Given that I want to get all "SavedSearches"
		When I request "/savedsearches/4/posts"
		Then the response is JSON
		And the "count" property equals "4"
		Then the guzzle status code should be 200

	@resetFixture
	Scenario: Search within savedsearch posts
		Given that I want to get all "SavedSearches"
		And that the request "query string" is:
			"""
			q=Explo
			"""
		When I request "/savedsearches/4/posts"
		Then the response is JSON
		And the "count" property equals "1"
		And the "results.0.name" property equals "Explosion"
		Then the guzzle status code should be 200

