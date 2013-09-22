Quick PHP Gift Registry (DirtyGiftReg)
================================

Quick PHP Gift Registry, aka "DirtyGiftReg", is a very simple PHP Wedding Registry / PHP Gift Registry script which enables people to quickly and easily setup "fake" wedding registrys to collect money (via paypal) instead of gifts / store credits. I wrote this script in a one night coding binge after a friend complained to me about the thousands of dollars of store credit that he was "stuck" with at an expensive dinnerware store (which he registered at for his wedding 5 years ago).  

Demo
------------

http://opensource.newmediaist.com/php-gift-registry


Requirements
------------

DirtyGiftReg should work with PHP 5.3.3 or later. No databases needed - flatfiles are used instead for all data storage.

Installation
------------

Installation is super-simple - just upload it to your server, and ensure flatfile.txt, gifts.txt and the ./images/ folder are writeable by your web server.

Usage
-----
<ol>
<li>Set your paypal details at the top of giftregistry.php. </li>
<li>Edit "gifts.txt" with your favorite text editor to create your gift list. Just copy and paste URLs directly from Amazon.com, one per line. There may be a line at the top of the file which reads "#DELETE THIS LINE AND RE-SAVE TO UPDATE GIFT LIST" - if that line is there, delete it before saving.</li>
<li>Visit giftregistry.php from your browser.</li>
<li>OPTIONAL - Edit template.html as you see fit. Notice the presence of tokens in the file - {gifts} {/gifts} encapsulates a loop used to generate each of the individual gifts from the flat file. There should only be one occurrence of {gift} and {/gift} within the template file. Available tokens available to be used inside of the gift loop include {id}, {title}, {price}, {description}, {image} and {paypal}.</li>
</ol>

TODO
----

As mentioned, this script was literally programmed in one night, with the goal of being super-easy/fast to setup and use. Because of that I avoided the use of anything which could complicate things, like using external libraries or APIs which might need authentication, and instead hacked together band-aid solutions using built-in PHP features like xPath. While this script does "get the job done" in its current form, there are a few areas which could definitely be improved, i.e.:
- Process paypal IPN payments to keep track of what items have been purchased, and pull those items from the registry
- Clean up the Amazon data extraction functionality 
- Create proper thumbnails 
- Prevent item data from being re-scraped if it hasn't changed since the last scrape
- Refactor general architecture :)

License
-------

Quick PHP Gift Registry is licensed under the MIT license.
