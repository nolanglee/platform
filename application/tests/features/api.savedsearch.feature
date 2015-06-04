@setsFixture @oauth2Skip
Feature: Testing the Sets API

	Scenario: Creating a SavedSearch
		Given that I want to make a new "SavedSearch"
		And that the request "data" is:
			"""
			{
				"name":"Search One",
				"filter": {
					"q":"zombie"
				},
				"featured": 1,
				"search": 1,
				"view":"map",
				"view_options":[],
				"visible_to":[]
			}
			"""
		When I request "/savedsearch"
		Then the response is JSON
		And the response has a "id" property
		And the type of the "id" property is "numeric"
		And the response has a "name" property
		And the "name" property equals "Search One"
		And the "featured" property equals "1"
		And the "search" property equals "1"
		And the "view" property equals "map"
		And the "filter.q" property equals "zombie"
		Then the guzzle status code should be 200


	Scenario: Updating a SavedSearch
		Given that I want to update a "SavedSearch"
		And that the request "data" is:
			"""
			{
				"name":"Updated Search One"
			}
			"""
		And that its "id" is "1"
		When I request "/savedsearch"
		Then the response is JSON
		And the response has a "id" property
		And the type of the "id" property is "numeric"
		And the "id" property equals "1"
		And the response has a "name" property
		And the "name" property equals "Updated Search One"
		Then the guzzle status code should be 200


	Scenario: Updating a non-existent SavedSearch
		Given that I want to update a "SavedSearch"
		And that the request "data" is:
			"""
			{
				"name":"Updated Set",
				"filter":"updated filter"
			}
			"""
		And that its "id" is "20"
		When I request "/savedsearch"
		Then the response is JSON
		And the response has a "errors" property
		Then the guzzle status code should be 404

	Scenario: Non admin user trying to make a SavedSearch featured fails
		Given that I want to update a "SavedSearch"
		And that the request "Authorization" header is "Bearer testbasicuser2"
		And that the request "data" is:
			"""
			{
				"name":"Updated Search One",
				"filter":"updated search filter",
				"featured":1
			}
			"""
		And that its "id" is "2"
		When I request "/savedsearch"
		Then the response is JSON
		Then the guzzle status code should be 403

	Scenario: Listing All SavedSearches
		Given that I want to get all "SavedSearch"
		When I request "/savedsearch"
		Then the response is JSON
		And the response has a "count" property
		And the type of the "count" property is "numeric"
		And the "count" property equals "1"
		Then the guzzle status code should be 200

	@resetFixture
	Scenario: Finding a non-existent SavedSearch
		Given that I want to find a "SavedSearch"
		And that its "id" is "22"
		When I request "/savedsearch"
		Then the response is JSON
		And the response has a "errors" property
		Then the guzzle status code should be 404

	Scenario: Finding a SavedSearch
		Given that I want to find a "SavedSearch"
		And that its "id" is "1"
		When I request "/savedsearch"
		Then the response is JSON
		And the response has a "id" property
		And the type of the "id" property is "numeric"
		Then the guzzle status code should be 200

	Scenario: Deleting a SavedSearch
		Given that I want to delete a "SavedSearch"
		And that its "id" is "1"
		When I request "/savedsearch"
		Then the guzzle status code should be 200

	Scenario: Deleting a non-existent SavedSearch
		Given that I want to delete a "SavedSearch"
		And that its "id" is "22"
		When I request "/savedsearch"
		And the response has a "errors" property
		Then the guzzle status code should be 404

	@resetFixture
	Scenario: Get savedsearch posts
		Given that I want to get all "SavedSearches"
		And that the request "query string" is:
			"""
			q=Explo
			"""
		When I request "/savedsearch/posts"
		Then the response is JSON
		And the "count" property equals "1"
		And the "results.0.name" property equals "Explosion"
		Then the guzzle status code should be 200
