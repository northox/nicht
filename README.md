# Warning
This code is dead is should only be used as a reference. No support provided. **Use at your own risk.**

# Nicht
Nicht is a nonintrusive, security oriented and high performance PHP5 lightweight framework for the development of small to average size web application. We been using it for a while (~2004) in various environments and thought others might be interested in using it.
![nicht](https://www.mantor.org/~northox/misc/nicht.jpg)

## Description
This framework is as simple as it can get. The idea is to **get out of your way** and let you build the application however you want. The framework does not offer a suite of fancy libraries to answer all of your possible wishes. Instead, it will let you use whatever you want without interfering.

Nicht mainly **interface a normalized authentication, authorization and navigation scheme**. The goal of this design is to provide a consistent way of integrating with mostly any type of backend without any internal change to your application logic (e.g. RDBMS (MySQL, PostgreSQL, etc), flat file, Kerberos, LDAP, Active Directory, PAM or others).

Nicht have been built with **security as a primary concern**. Some basic architecture choice have been made to support this goal: 

* Minimize attack surface: All code except Nicht's index is located outside the web root (i.e. project/www/).
* Positive security model: No sections are available without prior authorization (white-listing: by default access is restricted). Coarse-grained authorization and authentication are imposed seamlessly, they apply to every sections without any additional code, e.g., you don't need to add a function call at the beginning of each sections to ensure proper authorization.
* Keep it simple stupid: Nicht is a very simple and lightweight framework. The architecture is extremely easy to understand and easy to get right.

Read [Nicht.class.php] (https://github.com/northox/nicht/blob/master/lib/nicht/Nicht.class.php) if you want to learn how it works.

### Authentication modules presently implemented:
* MySQLi [PBKDF2] (http://en.wikipedia.org/wiki/PBKDF2) (Password Based Key Derivation Function) - strong hash (sha256), multiple iteration (20k), random salt - [auth](https://github.com/northox/nicht/blob/master/lib/nicht/MysqliNichtAuthPbkdf2.class.php#L53) , [set](https://github.com/northox/nicht/blob/master/src/admin.php#L58).
* LDAP/LDAPs - [auth](https://github.com/northox/nicht/blob/master/lib/nicht/LdapNichtAuth.class.php)

### Authorization modules presently implemented:
* MySQLi - one table group

## Password quality enforcer
To prevent common password attacks, [Stupid Password] (https://github.com/northox/stupid-password) has been integrated.

## Template system
We use PHP directly since the moment we measured the performance impacts of commons templating systems. However, if needed you can easily integrate most templating system. We tested Smarty, Template lite and Savant3.

## License
BSD license. In other word it's free software, free as in free beer.

## Authors
Danny Fullerton - Mantor Organization  
Jean-Francois Rioux - Mantor Organization
