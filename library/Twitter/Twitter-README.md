
From: https://github.com/komola/Bootstrap-Zend-Framework (MIT)


An easy way to display forms with Zend Framework + Bootstrap
============================================================

This is designed as an easy drop-in replacement for the normal Zend Forms to
work together with Twitter Bootstrap (http://twitter.github.com/bootstrap).

Getting started
---------------

Instaliation
------------

Composer way
------------

* Add this to your composer.json:

        komola/bootstrap-zend-framework

Old way
-------

* Add this to your application.ini config:

        autoloaderNamespaces.Twitter = "Twitter_"

* Add the library/Twitter folder to your library

Usage
-----

* Instead of extending from Zend\_Form extend from Twitter\_Form

We included a small example application that shows you what you can do with
this.
The interesting parts for our "library" are in /library/Twitter.

Have fun!

If you encounter any errors, please report them here on Github. Thanks!

License
-------

Copyright (c) 2012-2013 Sebastian Hoitz, komola GmbH <hoitz@komola.de>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.