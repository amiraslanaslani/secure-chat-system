test:
	vendor/bin/phpunit --bootstrap tests/bootstrap.php tests
build:
	npm run clean
	npm run build
