php-test:
	project/vendor/bin/phpunit --bootstrap tests/php/bootstrap.php tests/php
js-test:
	npm run test
js:
	npm run build:webpack
css:
	npm run build:css
all: css js
