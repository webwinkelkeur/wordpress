# Development

Run GNU Make to create Docker files for each PHP version:

    make all

Launch the development server using docker-compose:

    docker-compose up

This should get a WordPress instance on the latest support PHP version running
on http://localhost:13779 with the two plugins available.

Log in with username `admin` and password `password`.

Two additional instances are available for testing:

| PHP version | URL                    |
| ----------- | ---------------------- |
| 5.6.40      | http://localhost:13777 |
| 7.4.33      | http://localhost:13778 |
| 8+          | http://localhost:13779 |


# Tests

The tests use PHPUnit with the WordPress test library (`WP_UnitTestCase`).
Run them in throwaway Docker containers on PHP 7.4 and 8.4:

    make test

Or on one PHP version, with arguments for PHPUnit:

    PHP_VERSION=7.4 tests/run.sh --filter ReviewSync

`WP_VERSION` and `WC_VERSION` select the WordPress and WooCommerce versions
(default: latest). Downloads are cached in the `wwk-test-cache` Docker volume.

Tests live in `tests/*Test.php`; shared helpers are in `tests/includes/`.

GitHub Actions runs the tests on every push to master and on pull requests
(`.github/workflows/tests.yml`).


# Release

Decide on a new version number and add it to the top of `changelog.md`.

Commit the changelog, and tag the commit with the version number.

    git tag 3.12345

Release the new version:

    ./bin/publish
