dump-autoload:
	./vendor/bin/pint && composer validate && composer dump-autoload -o

# php tinker consol muhitini ishga tushurish
tinker:
	vendor/bin/testbench tinker

# Test muhitini ishga tushurish
u-tests:
	./vendor/bin/phpunit

# Laravel formatda code tashkil qilish.
format:
	./vendor/bin/pint

# Git tag yani versiyalar bilish
git-tag:
	git tag

git-dev:
	git add . && git commit -m "Finished feature X before release" && git push origin dev-main