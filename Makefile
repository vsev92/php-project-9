PORT ?= 8000
start:
	php -S 0.0.0.0:$(PORT) -t public
stop:
	killall -9 php
url:
	export DATABASE_URL=postgresql://analyzer:9maoipcvdDZOhAbXbiXOJpJXveeBLOif@dpg-cu30d29opnds7380d3a0-a.oregon-postgres.render.com/url_analyzer_db
install:
	composer install
test:
	composer exec --verbose phpunit tests
test-coverage-clover:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-clover ./build/logs/clover.xml --coverage-filter ./src
test-coverage-html:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-html ./build/reports --coverage-filter ./src
validate:
	composer validate
lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin tests
