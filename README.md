Jasny for Symfony
===========================

This library is an extension to Symfony2. With the Jasny bundles, developers can create deployable applications extremely fast, *so they can go out to enjoy the sun :)*.


Installation
------------

Install Symfony in {PROJECT_HOME} by following http://symfony.com/doc/current/book/installation.html

```
cd {PROJECT_HOME}
git clone git://github.com/jasny/jasny-symfony.git vendor/jasny
make -C vendor/jasny
```

To upgrade, run
```
make -C vendor/jasny upgrade
```

### Requirements ###

The jasny bundles require at least Symfony **2.0.8**.


Entities
--------

The ORM bundle adds much functionality to Doctrine ORM:

* Spatial support through the new `Point` type and `DISTANCE` function.
* Implement the `Referenceable` interface to find an enitity by a unique reference (e.g. slug or username) instead of the id.
* A unique reference (slug) is automatically set for entities that implement the `AutoReferencing` interface.
* Easily store an uploaded file for the entity with `FileBinding`.

Newly generated entites will use the extended repository manager from the ORM bundle, supporting `Referenceable` and order by for `findOneBy` as well as adding methods
`FindFirst` and `count`.


Forms
-----

The Framework bundle comes with an extended form base class and a customized twig form view, to enable more features:

* By default the HTML5 form validation is disabled. This can be enabled with form option 'html-validation'. *TODO*
* Each form has an attribute 'mode', which is set to either 'new' or 'edit'. *TODO*
* A form is marked 'dirty' when values are changed using bindRequest. *TODO*

### Form types ###

Several form field types are added:

* Date / Datetime - Overwrites standard symfony date/datetime type, to support the locale
* Editor          - A textarea with classname 'editor'
* CKEditor        - CKEditor, including support for KCFinder (*yeah baby :D*)
* AutoReference   - For automatically generated references (e.g. slugs) *TODO*
* File            - Overwrites the way to basic symfony file type


Twig
----

The Jasny Framework bundle adds the following filters to Twig:

* localdate / localtime / localdatetime  - Format a date value as a string based on the current locale
* preg_match / preg_replace / preg_split - Exposing PCRE to Twig
* paragraph                              - Add HTML paragraph and line breaks to text
* more                                   - Cut of text on a pagebreak
* truncate                               - Cut of text if it's to long
* linkify                                - Turn all URLs in clickable links (also supports Twitter @user and #subject)
* split                                  - Split text into an array (explode)


CRUD Generators
---------------

This project comes with 2 sets of generators:

* The Bootstrap generator employs the Twitter Bootstrap CSS library to create a clean and slick CRUD screens. *(See http://jasny.github.com/bootstrap)*
* The vanilla generator creates a scaffold on which you can apply your own custom styling.

Both generators have the option to create controllers, forms and views based on entities in another bundle. This allows for the creation of separate front and back office bundles.

### Commands ###

```
php app/console bootstrap:generate:crud
php app/console vanilla:generate:crud
```

Please use `php app/console help {COMMAND}` to find out more.

### Customize ###

You can easily create a custom CRUD generator.

* Copy the 'Command' and 'Resources' directory from Jasny/VanillaBundle to your own bundle.
* Modify the namespace and bundle name in both commands.
* Change the skeleton and public files from the 'Resources' directory


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
