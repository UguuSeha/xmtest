# Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `make build` to build fresh images
3. Run `make up` (the logs will be displayed in the current shell)
4. Open `https://localhost` in web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Open `http://localhost:1080` in web browser to check emails
6. Run `make test` to run unit tests
7. Run `make down` to stop the Docker containers
