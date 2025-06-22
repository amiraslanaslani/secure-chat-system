test:
	vendor/bin/phpunit --bootstrap tests/bootstrap.php tests
js:
	npm run clean
	npm run build:js
css:
	npm run build:css
all: css js
