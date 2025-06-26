all: install clean css js

php-test:
	project/vendor/bin/phpunit --bootstrap tests/php/bootstrap.php tests/php

js-test:
	npm run test

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
