
ViMbAdmin :: Virtual Mailbox Administration
============================================

The **ViMbAdmin** project (*vim-be-admin*) provides an web based virtual mailbox 
administration system to allow mail administrators to easily manage domains, mailboxes 
and aliases. 

**ViMbAdmin** was written in PHP using our own web application framework which includes 
the Zend Framework, the Doctrine ORM and the Smarty templating system with JQuery and Bootstrap.

**ViMbAdmin** is hosted on its own GitHub project page where you can find documentation, 
browse the source code and access our Git repository. We have also set up a Google Groups 
discussion group or you can follow the [blog posts](http://www.barryodonovan.com/index.php/category/vimbadmin-2) on our MD's personal site.


* Lead Author: [Barry O'Donovan](http://www.barryodonovan.com) (founder and MD of Open Solutions)
* Contributing Authors: Roland Huszti, Nerijus Barauskas


Features
---------

Standard and enhanced features from Postfix Admin include:

* Super admin(s) user level with full access;
* Admin(s) user level with access only to assigned domains and their mailboxes and aliases;
* Super admins can create and modify super admins and admins;
* JQuery Datatable throughout for quick in browser searching and pagination;
* Create, modify and purge domains including limited the number of mailboxes and aliases a non-super admin can create per-domain;
* Activate / deactivate admins, domains, mailboxes and aliases at the click of a button;
* Full logging;
* Facility for users (mailbox owners) to change their password;

Additional features include:

* Very configurable including:

    * set default values for quotas, number of mailboxes and aliases for domain creation;
    * added additional columns to the mailbox schema (including UID, GID, homedir and maildir);
    * templated welcome and settings email for users;

* Either plain or hashed and salted mailbox password support;
* Admin users table is secured with salted SHA passwords;
* Forgotten Password / Password Reset function for admins and mailboxes;


More Information, Live Demos and Screenshots
----------------------------------------------

Please see the following links for more information:

* [Open Solutions' ViMbAdmin page](http://www.opensolutions.ie/open-source/vimbadmin) (with screenshots);
* [Live demo](http://www.opensolutions.ie/vimbadmin);
* [GitHub project page](https://github.com/opensolutions/ViMbAdmin);
* Various [blog posts](http://www.barryodonovan.com/index.php/category/vimbadmin-2);,
* [GitHub wiki](https://github.com/opensolutions/ViMbAdmin/wiki)

Copyright, License and Redistribution
--------------------------------------

Copyright (c) 2011 - 2012 [Open Source Solutions Limited](http://www.opensolutions.ie/), Dublin, Ireland.

**ViMbAdmin** is free software: you can redistribute it and/or modify
it under the terms of the [GNU General Public License](http://www.gnu.org/licenses/gpl-3.0-standalone.html) as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

    ViMbAdmin is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with ViMbAdmin.  If not, see http://www.gnu.org/licenses/.

