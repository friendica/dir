test:
	cd tests && phpunit --coverage-html=coverage.html && x-www-browser ./coverage.html/index.html