#!/bin/bash
docker-compose exec -T test_run vendor/bin/codecept run --debug --fail-fast

