test:
	project/vendor/bin/phpunit --bootstrap tests/php/bootstrap.php tests
js:
	npm run clean
	npm run build:js
css:
	npm run build:css
all: css js
