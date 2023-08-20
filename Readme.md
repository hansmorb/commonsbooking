[![PHP Composer](https://github.com/wielebenwir/commonsbooking/actions/workflows/php.yml/badge.svg)](https://github.com/wielebenwir/commonsbooking/actions/workflows/php.yml) 
[![WP compatibility](https://plugintests.com/plugins/wporg/commonsbooking/wp-badge.svg)](https://plugintests.com/plugins/wporg/commonsbooking/latest) 
[![PHP compatibility](https://plugintests.com/plugins/wporg/commonsbooking/php-badge.svg)](https://plugintests.com/plugins/wporg/commonsbooking/latest)
[![codecov](https://codecov.io/gh/wielebenwir/commonsbooking/branch/master/graph/badge.svg?token=STJC8WPWIC)](https://codecov.io/gh/wielebenwir/commonsbooking)

# CommonsBooking

Contributors: wielebenwirteam, m0rb, flegfleg, chriwen  
Donate link: https://www.wielebenwir.de/verein/unterstutzen  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

Commons Booking is a plugin for management and booking of common goods. This plugin gives associations, groups and individuals the ability to share items (e.g. cargo bikes, tools) with users. It is based on the idea of Commons and sharing resources for the benefit of the community. 

* [Wordpress Plugin Page](https://wordpress.org/plugins/commonsbooking/)
* [Official Website](https://commonsbooking.org)
* For users get [Support](https://commonsbooking.org/kontakt/)
* For developers use the [Bug-Tracker](https://github.com/wielebenwir/commonsbooking/issues) 


## Development Installation

1. Make sure that composer is installed on your system
2. Navigate into your wp-content/plugins directory
3. Open a terminal and run `git clone https://github.com/wielebenwir/commonsbooking`
4. cd into the directory commonsbooking and run `composer install`
> This might fail, if you don't have the PHP extension [uopz](https://www.php.net/manual/en/book.uopz.php) installed. Try running `composer install --no-dev` if you just quickly want to test a specific branch without installing the extension.
5. Activate the plugin in the Plugin dashboard
> 
