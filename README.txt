=== Sigbro Auth 2.0===
Contributors: scor2k
Tags: sigbro, nxter
Donate link: https://www.nxter.org/sigbro
Requires at least: 5.0
Tested up to: 5.9
Requires PHP: 7.1
License: MIT
License URI: https://opensource.org/licenses/MIT

Wordpress plugin which add the ability for any user to log in to the Wordpress site without using Wordpress authorization mechanism. 
The user won't have the access to the wordpress profile

==== How to use ====

Add shortcut `[sigbro-auth redirect="/?page_id=10"]` and add `[sigbro-welcome]` to the `/?page_id=10` page. It will shoud welcome message and accountRS. 
You may use `$_COOKIE["sigbro_auth_account"]` to know your user's account.
