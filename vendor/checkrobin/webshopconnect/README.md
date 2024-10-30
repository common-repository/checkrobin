# checkrobin Business Webshopconnect

This is a PHP class library for connecting to the checkrobin Business API.

checkrobin helps you to easily compare products from different logistics partners, print labels and perform tracking of your shipments. 

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

You will need a webserver and a PHP Interpreter to run the code. 

System requirements: 
- PHP 5.3 or higher
- cURL library enabled

You can run the provided script *"check_requirements.php"* to find out if your infrastructure is matching the conditions above.

### Online Docu

You may also want to check out our online documentation at [webshopconnect.checkrobin.com](https://webshopconnect.checkrobin.com).

### Installing

Checkout the prepared coding examples in directory *"/samples"* for a quick start on how to use this library.

This piece of code is intended for usage with existing Webshops based on PHP such as Presta, XT:Commerce or Magento.

Download the code and include **autoload.php** to have the PHP classes available for integration in your PHP project. All relevant files are within the  namespace "Checkrobin".

The is a central configuration file in path *"/config/settings.php"* that rules which URLs are to be used for communication with checkrobin.

In order to start developing you will need to register a user account for the Staging version of the checkrobin Business API first.
Please drop us an email (info@checkrobin.com) to find out more.

### Deployment

Please contact info@checkrobin.com to contact our sales department in order to receive the support that you need to assist you in development and with the Go-Live of your plugin!

We have to insist that you use the predefined Staging URL of the checkrobin Business API preset in *"/config/settings.php"* for development and testing before switching to the live URL.

## Authors

* **David Tschische** - *Initial work* - [checkrobin](https://business.checkrobin.com)

## License

Please refer to the file LICENSE.txt for details.
