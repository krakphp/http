
PERIDOT = ./vendor/bin/peridot

.PHONY: test doc inc

inc:
	./vendor/bin/php-inc php-inc:generate -o src/inc.php src

test:
	$(PERIDOT) test

doc:
	cd doc; make html
