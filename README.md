# Overview
Nicht is a nonintrusive PHP5 lightweight authorization and authentication framework for the development of small to average size web application. We been using it for a while (~2004) in various environments and thought others might be interested in using it.

# Description
This framework is as simple as it can get. The idea is to **get out of your way** and let you build the application however you want. The framework does not offer a suite of fancy libraries to answer all of your possible wishes. Instead, it will let you use whatever you want without interfering.

Nicht mainly **interface a normalized authentication, authorization and navigation scheme**. The goal of this design is to provide a consistent way of integrating with mostly any type of backend without any internal change to your application logic (e.g. RDBMS (MySQL, PostgreSQL, etc), flat file, Kerberos, LDAP, Active Directory, PAM or others).

Nicht have been built with security as a primary concern. Some basic architecture choice have been made to support this goal: 

* Minimize attack surface: All code except Nicht's index is located outside the web root (i.e. project/www/).
* Positive security model: No sections are available without prior authorization (**white-listing**: by default access is restricted). Authorization and authentication are imposed seamlessly, they apply to every sections without any additional code (e.g. You don't need to add a function call at the beginning of each sections to ensure proper authorization).
* Keep it simple stupid: Nicht is a very simple and lightweight framework. The architecture is extremely easy to understand and **easy to get right**.

## Authentication modules presently implemented:
* MySQLi [PBKDF2] (http://en.wikipedia.org/wiki/PBKDF2) (Password Based Key Derivation Function) - strong hash (sha256), multiple iteration (1024), random salt
* LDAP/LDAPs

## Authorization modules presently implemented:
* MySQLi - one table group

## Stupid Pass
[StupidPass.class.php] (https://github.com/northox/nicht/blob/master/lib/StupidPass.class.php) is a simple password quality enforcer which was found to be pretty effective. It implements 1337 speaking convertion (e.g. 1=i,4=a,0=o, etc), enforcing at least 8 characters password with multiple charsets (uppsercase, lowercase, numeric, special) and restricting use of common password based on latest password analysis (sony, phpbb, etc).

# License
BSD license. In other word it's free software, free as in free beer.

# Authors
Danny Fullerton - Mantor Organization
Jean-Francois Rioux - Mantor Organization
