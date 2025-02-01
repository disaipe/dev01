dc=docker compose

all:
	@echo Usage:
	@echo
	@echo "CONTAINERS MANAGEMENT"
	@echo "  make up - start docker containers"
	@echo "  make stop - stop docker containers"
	@echo "  make restart - restart docker containers"
	@echo "  make exec - enter to application container"
	@echo "  make root - enter to application container as root"
	@echo "  make clean - remove all containers and volumes"

up:
	@$(dc) up -d
stop:
	@$(dc) stop
restart:
	@$(dc) stop && $(dc) up -d
root:
	@$(dc) exec php /bin/bash
exec:
	@./bin/exec
clean:
	@./bin/clean
