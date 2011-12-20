Jasny extension to Symfony2
===========================

The Jasny extension to Symfony2 provides developers a powerful tools to create deployable applications extremely fast.

This project comes with 2 sets of generators:

* The Bootstrap generator employs the Twitter Bootstrap CSS library to create a clean and slick CRUD screens.
* The vanilla generator creates a scaffold to apply custom styling on.

Both generators have the option to create controllers, forms and views based on entities in another bundle. This allows for the creation of separate front and back office bundles.

+ http://www.symfony.com
+ http://jasny.github.com/bootstrap


Installation
------------

Install symfony in {PROJECT_HOME} by following http://symfony.com/doc/current/book/installation.html


```
cd {PROJECT_HOME}
git clone git://github.com/jasny/jasny-symfony.git vendor/jasny
cd vendor/jasny
git submodule init
git submodule update
cd -
patch -p1 < vendor/jasny/app.patch
php app/console assets:install web
```


Usage
-----

```
php app/console bootstrap:generate:crud
php app/console vanilla:generate:crud
```

Please use `php app/console help {COMMAND}` to find out more.


Bug tracker
-----------

Have a bug? Please create an issue here on GitHub!

https://github.com/jasny/jasny-symfony/issues


Author
-------

**Arnold Daniels**

+ http://twitter.com/JasnyArnold
+ http://github.com/jasny
+ http://www.jasny.net


Troubleshooting
---------------

> The function "attribute" does not exist in "AcmeDemoBundle:MyEntity:index.html.twig" at line 3 4

Symfony2 ships with Twig version 1.1.2, which doesn't support the attribute function yet. Please upgrade to v1.4.0 by running:

```
cd {PROJECT_HOME}
[ -e vendor/twig/.git ] || ( cp vendor/symfony/vendors.php . && rm -rf vendor/twig && php vendors.php )
cd vendor/twig
git fetch origin
git reset --hard v1.4.0
cd -
```


Copyright and license
---------------------

Copyright 2011 Jasny BV.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this work except in compliance with the License.
You may obtain a copy of the License in the LICENSE file, or at:

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
