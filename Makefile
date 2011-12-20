all:
	git submodule init
	git submodule update
	( [ -e ../../.gitmodules ] && grep -q vendor/jasny ../../.gitmodules  ) || rm -rf `find -name '.git*'`
	grep -q Jasny ../../app/autoload.php || patch -p1 -d ../.. < app.patch
	php ../../app/console assets:install ../../web

