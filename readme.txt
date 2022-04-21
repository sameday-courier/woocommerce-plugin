=== SamedayCourier Shipping ===
Contributors: (samedaycourier)
Donate link: https://www.sameday.ro/contact
Tags: shipping
Requires at least: 5.8
Tested up to: 5.9
Stable tag: 6.1.0
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://sameday.ro

Sameday Courier shipping method plugin

== Description ==

= Overview =

This plug-in is intended to implement a new shipping method using the Sameday Courier service. As a store owner, after installing the plugin, you are able to import the list of Sameday Courier
service and delivery points assigned to your account. If your customer chooses the order to be delivered with Sameday Courier, you will be able to see this in the list of commands in your store's
administration panel. You will also be able to create an AWB. You can then add a new parcel in the created AWB and show the AWB as a pdf format. If you want, you can show the AWB history or delete the AWB.

For further information, contact us at software[at]sameday.ro !

= Features =

*   Config Sameday Courier shipping method
*   Import Sameday Courier pickup-points
*   Import Sameday Courier services
*   Import Sameday Courier lockers
*   Show AWB as PDF format
*   Add new parcel in AWB
*   Show AWB status and summary

== Installation ==

= Requirements =

* WordPress v5.8 or later
* WooCommerce v6.1 or later

= Plugin installation procedure =

* Open your Wordpress admin dashboard and go to Plugins/Installed Plugins menu
* Press Add new button and then press Upload Plugin.
* Drag & drop the .zip folder you downloaded and install plugin.

If every things works, activate plugin and now the new feature are ready to use.

= Setup your Plugin =

1. Go to WooCommerce/Settings and choose SamedayCourier tab.
2. Complete the form and press Save changes. If everythings works well you recive a success message.
3. After that, you are able to import the Services and Pick-up point assigned by Sameday to your account. Go to Service and press "Refresh Service". The same for pick-up points importing and (optional) for the locker list.
4. Activate the services by changing the status from "Disabled" to "Always" or "Interval".
5. Add the new SamedayCourier shipping method to your Shipping zones. Go to WooCommerce/Settings menu, open the Shipping zones tab. Choose the shipping zone for which you want to use Sameday services and press "Edit" button then press "Add shipping method" and select "SamedayCourier".

After you have followed all the steps described above, now in the checkout page of your store, your clients are able to see and choose one of the Sameday service.

In the Order page will be displayed a button "Generate awb". After awb is generated you can show it as pdf format. Also you can show the history of the awb, add a new parcel or simply remove the awb.

== Frequently Asked Questions ==

= This plugin is free to use ? =
Yes! Using this plug-in is free. However, the service offered by our company is based on a contract. The terms and conditions of the contract are negotiated individually.

After signing the contract, the client will receive a set of credentials (username and password). With these credentials, the customer will be able to use the Sameday Courier
delivery service.

= Can I make changes to the plugin's source code ? =

Sure. However, keep in mind that if you make changes to the source code of the plugin you can no longer benefit from the updates we make constantly. Because in a situation like this in case of an update your changes would be overwritten.

NOTE: We encourage customers to fork our github repository and contribute with their ideas
Link to Github: https://github.com/sameday-courier/woocommerce-plugin

= Are all easyBox delivery services available ? =

No! For the moment only the service Locker NextDay is available.

= I activated the Locker NextDay service but I don't see it on the checkout page =

The service is only visible for orders that have a single product in the cart. So make sure you don't have more than one product in your cart.

= I use the estimated cost option but the fixed cost appears on the order page =

This situation may be due to the fact that the name of the locality does not match our nomenclature of localities.

== Screenshots ==

1. Initial configuration for the plugin
2. Update services
3. Enabling services
4. Add shipping method to shipping zone
5. How clients will choose one of Sameday's shipping methods
6. Creating an AWB
7. Adding more parcels to the AWB
8. Show AWB History

== Configuration ==

= Initial configuration for the plugin =

In order to setup the plugin, you need to provide the following information (please refer to screenshot 1 - Initial configuration for the plugin):

* Go to plugin's Settings
* `Title` = The title that it will be displayed on your website
* `Username` = Username provided by Sameday as result of enrolling to our services
* `Password` = Password provided by Sameday as result of enrolling to our services
* `Default label format` = The format of paper (eg. A4) for creating the awbs
* `Is testing` = If checked, the plugin will be set in development mode. This feature is intended to test services. Should not be checked on production environment

    NOTE: Each environment (eg. test/production) has a different set of credentials. Be sure to use the set of credentials corresponding to selected environment

* `Use estimated cost` = The cost that it will be displayed for each of the services. Options: never (fixed price - refer to Edit Service form), always (estimated by Sameday). For extra information regarding to these options, please refer to the screenshot 1
* `Extra fee` = Extra fee, defined in percentage value, which will be applied on top of the estimated cost provided by Sameday
* `Open package status` = Allow the clients to open the package in the moment of delivery in order to check the conformity of the delivered product(s)
* `Open package label` = This text will be displayed on the checkout page in order to ask them if they would like to open the package on delivery. Please refer to the previous option (Open package status)
* `Locker max. items` = The maximum amount of items accepted inside the locker

= Update services, pickup-points & lockers =

To use the plugin, you need to get the available services (see screenshot 2 for details).

* In the settings page, click on the button `Services`
* Click on the button `Refresh services`

Now the list of services should be populated.

NOTE: The same procedure should be applied for pickup-points and lockers.

= Enabling services =

By default, all the services fetched using the previous indications are disabled. Thus, you need to enable those that you want to provide to your clients (please refer to screenshot 3).

* From the services page click on the service name to start editing
* `Service name` = The name that it will be displayed to your clients on the checkout page (see screenshot 5 for more details)
* `Price` = Fixed price for this service. This is the delivery price that the clients will pay if they choose this delivery service
* `Free delivery price` = The minimum order price for which the delivery is free of charge. This is a numeric value bigger than 0

NOTE: If you don't want to apply an free of charge option leave this field blank

* `Status` = Enable or disable the service. If enabled, the clients will be able to choose this delivery service

= Add shipping method to shipping zone =

By default, Sameday operates only in Romania. If you would like to deliver your packages within this shipping zone:

* Go to WooCommerce Shipping zones
* `Zone Regions`= Choose Romania or counties from Romania
* Click on the button `Add shipping method`, choose SamedayCourier and save

For more details, please refer to screenshot 4.

= Creating an AWB =

The AWB is the transport document, created by Sameday, in order to process your transport order.

* Go to the order page
* Click on the button `Generate AWB`
* In the modal, which originally is pre-completed, you can customize the shipping details (please refer to screenshot 6 for more details)
* `Repayment` = The amount that the courier should take from the client in the moment of delivery (eg. If the order is already paid, the repayment will be 0)
* `Insured value` = The amount that should be insured for the given AWB
* `Package weight` = The package weight
* `Package length` = The package length (optional)
* `Package height` = The package height (optional)
* `Package width` = The package width (optional)
* `Pickup-point` = The location from which the package will be taken
* `Package type` = The type of the package predefined by Sameday
* `AWB Payment` = Determine who will pay for delivering this package. By default, the site owner
* `Service` = A list of enabled services (refer to the `Enable services` section)
* `Observation` = Any text that will be displayed on observation field in AWB
* `Client Reference` = Set a unique reference for each AWB. This field can be associated with the id of the order.

An parcel it's a package of the types predefined by Sameday (Envelope, Parcel, Large Parcel).
Any AWB can have one or more parcels (default one).

After generating the AWB, the admin can add one or more parcels to the same AWB (please refer to screenshot 7).

== Changelog ==

= 1.2.14 =

* Add new feature. Show details about selected locker in checkout page.

= 1.2.13 =

* Refresh Sameday Api token after each new auth with another set of user credentials
  (Be aware that when you switch from one user to another you will have to redo the import of services and pick-up points !).

= 1.2.12 =

* Add client ID and country for lockers map.

= 1.2.11 =

* Fixed select2 error. Check if element exist.

= 1.2.10 =

* Set lockers list as a select2 field.

= 1.2.9 =

* Bug fix. Validate if Host Country param is undefined.

= 1.2.8 =

* Bug fix

= 1.2.7 =

* Bug fix. SamedayCourierHelperClass::getHostCountry() must be of the type string.

= 1.2.6 =

* Add new feature. The client can choose if he wants to show SamedayCourier lockerPlugins map in checkout page.
By Default the plugin shows lockers list as a drop-down field.

= 1.2.5 =

* Add new feature. Integrate ULRs in order to use SamedayCourier Hungarian API.

= 1.2.4 =

* Code improvement. Set AwbRecipientEntityObject with data of client instead of locker data.

= 1.2.3 =

* Bug fix. Set default value for is_testing option. Remove unnecessary variable "$intervals".

= 1.2.1 =

* Add new feature. Integrate Sameday LockersPlugin library.

= 1.1.1 =

* Bug fix. SamedayCourierHelperClass::getApiUrl() must be of the type string.

= 1.1.0 =

* Code improvement. Remove is_testing options. The env. mode will be setup based on the set of credentials. Remove Sameday service interval.

= 1.0.30 =

* Code improvement. Store Sameday Token into database in order to avoid unnecessary calls to Sameday Auth Method.

= 1.0.29 =

* Bug fix. Avoid package_hash notice.

= 1.0.28 =

* Add new feature. Set maximum amount of items to fit in lockers.

= 1.0.27 =

* Bug fix. Include custom.js file only in checkout page instead of globally.

= 1.0.26 =

* Add new feature. Set a unique reference for each AWB. This field can be associated with the id of the order.

= 1.0.25 =

* Add new feature. Recalculate the estimated cost after payment method is changed.

= 1.0.24 =

* Bug fix. Show AWB History.

= 1.0.23 =

* Group locker list by city. This list will be shown in the checkout page.

= 1.0.22 =

* Before the locality parameter is sent to the cost estimation method, it is parsed to remove its diacritics.

= 1.0.21 =

* Update doc.

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
