dump-autoload:
	./vendor/bin/pint && composer validate && composer dump-autoload -o

tinker:
	vendor/bin/testbench tinker

u-tests:
	vendor/bin/testbench tinker

format:
	./vendor/bin/pint