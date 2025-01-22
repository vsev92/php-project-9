PORT ?= 8000
start:
	php -S 0.0.0.0:$(PORT) -t public
stop:
	killall -9 php
install:
	composer install
validate:
	composer validate
lint:
	composer exec --verbose phpcs -- --standard=PSR12 src public
