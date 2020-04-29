=== SamedayCourier Shipping ===
Contributors: (samedaycourier)
Donate link: https://www.sameday.ro/contact
Tags: shipping
Requires at least: 4.7
Tested up to: 5.3.1
Stable tag: 3.4.7
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://sameday.ro

Sameday Courier shipping method plugin

== Description ==

Overview

This plug-in is intended to implement a new shipping method using the Sameday Courier service. As a store owner, after installing the plugin, you are able to import the list of Sameday Courier
service and delivery points assigned to your account. If your customer chooses the order to be delivered with Sameday Courier, you will be able to see this in the list of commands in your store's
administration panel. You will also be able to create an AWB. You can then add a new parcel in the created AWB and show the AWB as a pdf format. If you want, you can show the AWB history or delete the AWB.

For further information, contact us at software@sameday.ro !

Features:

*   Config Sameday Courier shipping method
*   Import Sameday Courier pickup-points
*   Import Sameday Courier services
*   Import Sameday Courier lockers
*   Show AWB as PDF format
*   Add new parcel in AWB
*   Show AWB status and summary

== Installation ==

In order to be able to use this plug-in, be sure you have at least 4.7 Wordpress Version and the latest WooCommerce version installed.
Open your Wordpress admin dashboard and go to Plugins/Installed Plugins menu. Press Add new button and then press Upload Plugin. Drag&drop the .zip folder you download for and install plugin.
If every things works, activate plugin and now the new feature are ready to use.

Settup your Plugin

Go to WooCommerce/Settings and choose SamedayCourier tab. Complete the form and press Save changes. If everythings works well you recive a success message.
After that, you are able to import the Services and Pick-up point assinged by Sameday to your account. Go to Service and press "Refresh Service". The same for pick-up points importing
and (optional) for the locker list.
Activate the services by changing the status from "Disabled" to "Always" or "Interval".
Add the new SamedayCourier shipping method to your Shipping zones. Go to WooCommerce/Settings menu, open the Shipping zones tab. Choose the shipping zone for which you want to use
Sameday services and press "Edit" button then press "Add shipping method" and select "SamedayCourier".
After you have followed all the steps described above, now in the checkout page of your store, your clients are able to see and choose one of the Sameday service.
In the Order page will be displayed a button "Generate awb". After awb is generated you can show it as pdf format. Also you can show the history of the awb, add a new parcel or simply
remove the awb.

== Frequently Asked Questions ==

= This plugin is free to use ? =
Yes! Using this plug-in is free. However, the service offered by our company is based on a contract. The terms and conditions of the contract are negotiated individually.
After signing the contract, the client will receive a set of credentials (username and password). With these credentials, the customer will be able to use the Sameday Courier
delivery service.

== Changelog ==

= 1.0.20 =
* Add new feature. A new option has been added for the "Use estimated cost" field.
  The admin can choose to display on the site the price of the transit cost calculated by Sameday, only if its value
  exceeds the value of the fixed cost assigned by the admin for each service (exp. Additional km situation).

= 1.0.19 =
* Add new feature. This option allows customers to use the package opening service when their parcels are delivered to them by the courier.
  Usage: In order to implement this new feature in your site, after update plugin you MUST refresh the Service list.

= 1.0.18 =
* Add an improvement. Skip decimal validation on Edit Service menu on "Price" field.

= 1.0.17 =
* Add new feature. Add hiperlink in admin zone that land you to Sameday eAWB platform.

= 1.0.16 =
* Update documentation.

= 1.0.15 =
* Add new feature. Add SamedayCourier shipping method in shipping zone.

= 1.0.14 =
* Bug fix. Show awb history. Catches an exception if no data has been found.

= 1.0.13 =
* Add new feature. Fill "Repayment" field in the "Generate awb" form with the value of total amount of order only if the payment method is set as "COD" otherwise leave value as 0.
However you are able to change this value as you want.

= 1.0.12 =
* Bug fix. Estimate cost method.

= 1.0.11 =
* Add new feature. Apply extra fee on expedition estimated cost. This feature is useful for those who wish to apply an additional charge to the transport cost over the transport cost calculated by Sameday for each individual shipment.
Especially useful for those who have unpaid VAT companies.

= 1.0.10 =
* Bug fix. Error notification. Show an error message details if the awb generation was unsuccesful.

= 1.0.9 =
* Bug fix & code refactor.

= 1.0.8 =
* Bug fix. easyBox id must become required if client choose Locker NextDay service in checkout page before submit the order.

= 1.0.7 =
* Minor update. Show Compnay name on SamedayCourier Awb if the client fill "company" field with data.

= 1.0.6 =
* Bug fix. Change "Repayment" field <html> validation.

= 1.0.5 =
* Bug fix & code refactor.

= 1.0.4 =
* Bug fix & code refactor.

= 1.0.3 =
* Code improvement. The administrator can change the delivery service before generating the awb regardless of the option chosen by the customer.

= 1.0.2 =
* Change Version iteration from 1.0.1 to 1.0.2

= 1.0.1 =
* SamedayCourier init version.

== Upgrade Notice ==

= 1.0 =

New features will be added soon.

== Screenshots ==

1. You found some screenshots in /assets/screenshots folder
