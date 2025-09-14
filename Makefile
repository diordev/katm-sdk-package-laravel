dump-autoload:
	./vendor/bin/pint && composer validate && composer dump-autoload -o

# php tinker consol muhitini ishga tushurish
tinker:
	vendor/bin/testbench tinker

# Test muhitini ishga tushurish
u-tests:
	vendor/bin/testbench tinker

# Laravel formatda code tashkil qilish.
format:
	./vendor/bin/pint

# Git version tag boshqarish.
add-version-git:
	git tag -a v0.1.0 -m "release 0.1.0" && git push origin v0.1.0