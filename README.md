# Prestashop Custom Contact Form
A custom module that overrides the Prestashop contact form with custom styling and a Google Map background
![alt tag](http://i.imgur.com/fqQqkEh.jpg)

# Demo
You can view a demo of this module at [http://prestashop.jason-hee.com/en/contact-us](http://prestashop.jason-hee.com/en/contact-us)

# Setup
## Download
[Download](https://github.com/jasonheecs/prestashop-contact-form-google-map/archive/master.zip) or clone this repository into your project's modules directory. The module files should reside in your `<project dir>/modules/customcontact` directory.

## Installation
Once the module files are in your project, you should be able to see the **Custom Contact** module in your admin back-office.

![alt tag](http://i.imgur.com/kOqHPuo.jpg)

Click on the **Install** button to install the module.

## Configuration
Once the module is installed, you can click on the **Configure** button to configure the module settings. The configuration screen looks like this:

![alt tag](http://i.imgur.com/xksqTj5.jpg)

## Notes
You will need a [Google Maps API key](https://developers.google.com/maps/documentation/javascript/get-api-key) in order for the Google map to load.

# Supported versions
This module is meant for Prestashop v1.6.x. It will not work on Prestashop 1.7.

# Customisation
This module overrides Prestashop's [ContactController](customcontact/override/controllers/front/ContactController.php). You can customise the module's functionality by extending this class in your theme.

The front end template consists of 2 files [contact-form.tpl](customcontact/views/templates/front/contact-form.tpl) and [map.tpl](customcontact/views/templates/front/map.tpl). You can customise the front end appearance of the module by overriding these 2 files in your theme.