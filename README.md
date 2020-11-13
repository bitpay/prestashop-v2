BitPay Checkout for Prestashop
===============================

## Build Status

[![Build Status](https://travis-ci.org/bitpay/prestashop.svg?branch=master)](https://travis-ci.org/bitpay/prestashop)

This plugin allows stores using the Prestashop shopping cart system to accept cryptocurrency payments via the BitPay gateway. It only takes a few minutes to configure.

# Requirements

This plugin requires the following:

* [Prestashop](https://www.prestashop.com/en).
* A BitPay merchant account ([Test](http://test.bitpay.com) and [Production](http://www.bitpay.com))

# Installation

Drag and drop the the zipped extension into the admin section to install.

**WARNING:** 

* It is good practice to backup your database before installing plugins. Please make sure you create backups.
* If you were using a previous version of this plugin, this version (3.0) was completely rewritten to improve the user experience and the security. You will need to renew the configuration of the plugin (fetch a new API token from the BitPay merchant dashboard).

# Plugin Fields

After the plugin downloaded, verify it is installed by going to ***Modules->Module Catalog*** and look for ***BitPay Checkout***

* If it is not installed, then install and click ***Configure***
* if it is installed, then go to ***Modules->Module Manager*** and click ***Configure***

* **Production Environment**
* Set to **Yes** to use a **Live** environment and process real payments.  Set to **No** to use a **Sandbox** environment

* **Merchant Tokens**
	* A ***development*** or ***production*** token will need to be set
* **Auto Capture Email**
	* If set to ***Yes***, BitPay will automatically pass along the users email as part of the order.  If ***No***, they will be prompted to enter one (for refund purposes from BitPay)

* **Modal Checkout**
	* If set to ***No***, then the user will be sent to an invoice at BitPay.com to complete their payment, and then redirected to the merchant site.  	
	* If set to ***Yes***, the user willl stay on the merchant site and complete their payment.
	
* **Error Page**
	* Create a page to redirect users if there is a BitPay error when placing an order.  
	
* **Error States**
	* If there is an error with the order, choose what state the order should be placed in.  You can use the pre-defined states, or create your own.
	
# How to use

* Enable the plugin
* Configure your settings as described above
* Click *Save Changes*
* BitPay Checkout will now appear as a payment option when users checkout

# IPN
BitPay Checkout provides an integrated IPN service that will update orders as the status changes.

Initial orders will be set to a **Pending** state, then progress to **Processing**, and finally to **Completed**.  If an invoices is **Expired** (ie, someone creates an invoice but does not finish the transaction), the IPN will set the order to `Canceled`

## Support

**BitPay Support:**

* Last Cart Version Tested: Prestashop 1.7.5.1
* [GitHub Issues](https://github.com/bitpay/prestashop/issues)
  * Open an issue if you are having troubles with this plugin.
* [Support](https://support.bitpay.com/hc/en-us)
  * BitPay merchant support documentation

## Troubleshooting

The latest version of this plugin can always be downloaded from the official BitPay repository located @ [https://github.com/bitpay/prestashop-v2](https://github.com/bitpay/prestashop-v2)

* This plugin requires PHP 5.5 or higher to function correctly. Contact your webhosting provider or server administrator if you are unsure which version is installed on your web server.
* Ensure a valid SSL certificate is installed on your server. Also ensure your root CA cert is updated. If your CA cert is not current, you will see curl SSL verification errors.
* Verify that your web server is not blocking POSTs from servers it may not recognize. Double check this on your firewall as well, if one is being used.
* Check the system error log file (usually the web server error log) for any errors during BitPay payment attempts. If you contact BitPay support, they will ask to see the log file to help diagnose the problem.
* Check the version of this plugin against the official plugin repository to ensure you are using the latest version. Your issue might have been addressed in a newer version!

**NOTE:** When contacting support it will help us if you provide:

* Prestahop Version
* PHP Version
* Other plugins you have installed
* Configuration settings for the plugin (Most merchants take screen grabs)
* Any log files that will help
  * Web server error logs
* Screen grabs of error message if applicable.

## Contribute

Would you like to help with this project?  Great!  You don't have to be a developer, either.  If you've found a bug or have an idea for an improvement, please open an [issue](https://github.com/bitpay/prestashop-v2/issues) and tell us about it.

If you *are* a developer wanting contribute an enhancement, bugfix or other patch to this project, please fork this repository and submit a pull request detailing your changes.  We review all PRs!

This open source project is released under the [MIT license](http://opensource.org/licenses/MIT) which means if you would like to use this project's code in your own project you are free to do so. Speaking of, if you have used our code in a cool new project we would like to hear about it!  Please send us an [email](mailto:sales-engineering@bitpay.com).

## License

Please refer to the [LICENSE](https://github.com/bitpay/prestashop-v2/blob/master/LICENSE) file that came with this project.
