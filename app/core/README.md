# Ushahidi Platform

## Core Application

Contains the interfaces and use cases for the Ushahidi Platform.

Has no dependencies, except for itself.

## Testing

Install Composer dependencies:

    composer install

Run phpspec tests:

    bin/phpspec run

Change configuration by copying `phpspec.yml.dist` to `phpspec.yml` and editing the new file.
