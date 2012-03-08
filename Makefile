JASNY_REPO = git://github.com/jasny/jasny-symfony.git
JASNY_TAG = HEAD
TWIG_REPO = http://github.com/fabpot/Twig.git
TWIG_TAG = v1.6.0

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
	git clone ${JASNY_REPO} jasny && \
    git --work-tree=jasny --git-dir=jasny/.git reset --hard ${JASNY_TAG} && \
	make -C jasny

upgrade-twig:
	rm -rf ../twig
	git clone ${TWIG_REPO} ../twig
	git --work-tree=../twig --git-dir=../twig/.git reset --hard ${TWIG_TAG}
	rm -rf ../twig/.git `ls -d -1 ../twig/* | grep -v lib`

.PHONY: install upgrade newclone upgrade-twig

