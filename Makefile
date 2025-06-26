all: install clean css js

php-test:
	project/vendor/bin/phpunit --bootstrap tests/php/bootstrap.php tests/php

js-test:
	npx vitest run -c tests/typescript/vitest.config.ts

js:
	npm run build:webpack

css:
	npm run build:css

tarball: install clean css js
	mkdir -p dist
	tar -czf ./dist/project.tar.gz -C ./project .

clean:
	rm -f dist/*
	rm -f project/*.sqlite
	rm -f project/statics/css/*
	rm -f project/statics/js/*

install:
	npm install
	composer install

js-coverage:
	npx vitest run -c tests/typescript/vitest.config.ts --coverage

php-coverage:
	XDEBUG_MODE=coverage project/vendor/bin/phpunit --bootstrap tests/php/bootstrap.php --coverage-text --coverage-html=coverage -c phpunit.xml tests/php
