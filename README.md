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
| 7.0.33      | http://localhost:13778 |
| 8+          | http://localhost:13779 |
