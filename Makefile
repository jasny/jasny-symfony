TWIG_TAG = v1.4.0

all: install

install:
	git submodule init
	git submodule update
	( [ -e ../../.gitmodules ] && grep -q vendor/jasny ../../.gitmodules  ) || rm -rf `find -name '.git*'`
	grep -q Jasny ../../app/autoload.php || patch -p1 -d ../.. < app.patch
	php ../../app/console assets:install ../../web
	find ../../web/bundles -name .DS_Store -delete

upgrade:
	cd .. && \
	rm -rf jasny && \
	git clone git://github.com/jasny/jasny-symfony.git jasny && \
	make -C jasny

upgrade-twig:
	rm -rf ../twig
	git clone http://github.com/fabpot/Twig.git ../twig
	git --work-tree=../twig --git-dir=../twig/.git reset --hard ${TWIG_TAG}
	rm -rf ../twig/.git `ls -d -1 ../twig/* | grep -v lib`

.PHONY: install upgrade newclone upgrade-twig

