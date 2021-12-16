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


1. To the auth page add shortcut `[sigbro-auth redirect="/?page_id=10"]` 

2. After authorization user will be redirected to the "/?page_id=10"

3. Put on this page another shortcut: `[sigbro-property setter="ARDOR-H2W5-VZAB-9XFZ-38885" property="sigbro" value="silver" redirect="/?page_id=2" network="main"]`

4. The shortcut will verify that user can get access to the page and redirect to the "/?page_id=2" if not. You may add QR-code with template on this page to help your users transfer you payment.

5. If user allow to get the access - nobody will be printed. 

Profit!

PS. You may use `$_COOKIE["sigbro_auth_account"]` to print your user's account.
