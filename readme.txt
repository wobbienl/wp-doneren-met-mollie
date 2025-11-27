=== Doneren met Mollie ===
Contributors: ndijkstra
Donate link: https://wobbie.nl/doneren
Tags: mollie,doneren,donate,ideal,recurring
Requires at least: 5.3
Tested up to: 6.8.3
Stable tag: 2.10.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin is both suitable for one-time donations and for periodic payments. All payment methods of Mollie are integrated into the plugin.

== Description ==

Are you looking for a simple donation plugin for a charity or for example the local football club? This plugin is both suitable for one-time donations and for periodic payments. All payment methods of Mollie are integrated into the plugin. The plugin is also available in several languages: Dutch, English, German and French.

= Features =

Although the plugin is very easy to install, it contains enough options:

* Donations can be found in the WordPress admin panel.
* Donors can enter their details.
* You can specify different projects, so donors can choose which project they want to donate.
* You can choose which data donors should provide for the donation.
* You can set a redirect page yourself.
* You can specify by default the choice of the donor.
* You can style the form as desired.

= Recurring payments =

In addition to one-time donations, this plugin is also useful for collecting periodic amounts. This works on the basis of Mollie's "Subscription API". This system makes it possible, for example, to collect a certain amount monthly, quarterly or annually by credit card or by SEPA Direct Debit.

= Multicurrency =

Let your donors donate in their own currency. Enable this setting so the donor can select a currency when donating, or set a default currency for all donations.

Please take a look at [Mollie Forms](https://wordpress.org/plugins/mollie-forms/) which contains more features to create forms with payments.

== Frequently Asked Questions ==

= Can I use shortcodes? =

Yes! The following shortcodes are available:

* [doneren_met_mollie] To display the form
* [doneren_met_mollie_total] To display the total raised money
* [doneren_met_mollie_total project="My project"] To display the total raised money of a specific project
* [doneren_met_mollie_total start="500.20"] To display the total raised money and start at a specific amount
* [doneren_met_mollie_goal goal="1000" text="Goal reached!"] Countdown to your goal. Goal must be higher then 0 and the text will be displayed when the goal is reached
* [doneren_met_mollie_donors start="0" unique_email="true"] To display the number of donors.

== Installation ==

1. Install "Doneren met Mollie" in Wordpress
1. Activate plugin in Wordpress
1. Create an account at Mollie.com and copy the Live API-key
1. Go to the settings page in "Doneren met Mollie" and fill in the Live API-key
1. Place the shortcode [doneren_met_mollie] on a page to show the form
1. You can also use the optional shortcode [doneren_met_mollie_total] to show the total raised money

== Changelog ==

= 2.10.10 - 27/11/2025 =
* Fix deprecation messages

= 2.10.9 - 25/03/2025 =
* Move variable translations out of constructor to get rid of notice about translation loading
* Fixed error about settlementAmount when using Test API-key

= 2.10.8 - 11/03/2025 =
* XSS vulnerability fix for goal shortcode

= 2.10.7 - 25/11/2024 =
* Send correct User-Agent for Mollie API requests

= 2.10.6 - 20/11/2024 =
* Moved load_plugin_textdomain in init action

= 2.10.5 - 29/07/2024 =
* Don't show minimum amount error if no amount is set (Thanks to [@LukeSerne](https://github.com/LukeSerne))

= 2.10.4 - 12/03/2024 =
* Update readme

= 2.10.3 - 07/03/2024 =
* Security fix

= 2.10.2 - 14/12/2023 =
* Fix webhook for starting subscriptions

= 2.10.1 - 22/11/2023 =
* Show subscription details on donation detail page

= 2.10.0 - 03/07/2022 =
* Added Google reCaptcha V3 integration
* Added total donated amount to donors and subscriptions table

= 2.9.4 - 06/04/2022 =
* Fixed bug where all donations were marked as recurring
* If subscription is already cancelled at Mollie, the plugin will now update the status when you are trying to cancel it

= 2.9.3 - 08/12/2021 =
* Also creating customers/donors for one-time payments
* Fixed another issue with amounts with comma for default amount

= 2.9.2 - 29/11/2021 =
* Fixed issue with amounts with comma for default amount

= 2.9.1 - 29/11/2021 =
* Fixed issue with amounts with comma

= 2.9.0 - 23/11/2021 =
* Fixed currency symbol when donor choose other currency
* Changed input type to number instead of text for own amount field
* Tested up to Wordpress 5.8.2
* Minimum Wordpress version is now set to 5.3

= 2.8.11 - 18/07/2021 =
* Fixed issue with multiple classes

= 2.8.10 - 20/03/2021 =
* Fixed issue with multiple classes in submit button

= 2.8.9 - 02/03/2021 =
* Fixed bug with permission checkbox that stayed hidden

= 2.8.8 - 11/02/2021 =
* Added labels to fields.

= 2.8.7 - 21/01/2021 =
* Fixed issue with projects dropdown

= 2.8.6 - 21/01/2021 =
* Fixed issue with tabs in settings

= 2.8.5 - 16/01/2021 =
* Currency dropdown now also uses fields class
* Fixed security issue with exports.
* Escaped all output

= 2.8.4 - 06/12/2020 =
* Added the possibility to bulk delete donations and donors.

= 2.8.3 - 17/03/2020 =
* Changed e-mail field to type email
* Added more currencies

= 2.8.2 - 31/01/2020 =
* Added option to delete donors. Please note that this will also cancel all the subscriptions.
* Added shortcode [doneren_met_mollie_donors] to display the number of donors. Use [doneren_met_mollie_donors unique_email=false] to include duplicate or empty email addresses

= 2.8.1 - 14/07/2019 =
* Fixed recurring settings page

= 2.8.0 - 11/07/2019 =
* Replaced Mollie PHP client with own version to resolve conflicts

= 2.7.0 - 05/07/2019 =
* Added currency column to exports
* Added option to settings to disable the metadata that are sent to Mollie
* Added the filter 'dmm_donate_form_validation' to add form errors for custom form fields (credits to van-ons.nl)
* Added the action 'dmm_donate_form_posted' to process post data for custom form fields (credits to van-ons.nl)
* Added the action 'dmm_donate_form_paid' to do something after a successful donation payment (credits to van-ons.nl)
* Added the action 'dmm_donate_form_top' to add custom fields to the top of the donate form (credits to van-ons.nl)
* Added the action 'dmm_donate_form_bottom' to add custom fields to the bottom of the donate form (credits to van-ons.nl)
* Updated Mollie PHP Client to v2.10.0

= 2.6.0 - 12/04/2019 =
* More data in the Mollie metadata
* You can now get the total raised money per project [doneren_met_mollie_total project="My project"]
* You can now get the total raised money starting at another amount [doneren_met_mollie_total start="500.20"]
* Added actions "dmm_payment_paid", "dmm_payment_chargedback", "dmm_payment_refunded", "dmm_payment_open" and "dmm_payment_failed"
* Fixed some translations
* Other minor fixes

= 2.5.10 - 01/02/2019 =
* Fixed warning about undefined constant

= 2.5.9 - 15/08/2018 =
* Updated Mollie PHP Client

= 2.5.8 - 15/08/2018 =
* Bugfix when creating subscription

= 2.5.7 - 15/08/2018 =
* Bugfixes

= 2.5.6 - 20/06/2018 =
* Bugfixes

= 2.5.5 - 20/06/2018 =
* Prepared plugin for new payment methods Giropay & eps
* Fixed bug for not displaying Bancontact and ING Home'Pay for recurring payments

= 2.5.3 - 29/05/2018 =
* Added settlement amount to donation in admin
* Bugfixes

= 2.5.2 - 28/05/2018 =
* Bugfix when not selecting a payment method in dropdown
* Bugfix in counting goal at refunds and chargebacks
* Fixed column header in csv export

= 2.5.1 - 28/05/2018 =
* Option to add GDPR checkbox to form
* Bugfixes

= 2.5.0 - 27/05/2018 =
* Multicurrency
* Updated Mollie API Client to v2.0.1

= 2.4.11 - 16/02/2018 =
* Updated Mollie API Client to v1.9.6

= 2.4.10 - 10/01/2018 =
* Updated Mollie API Client to v1.9.4

= 2.4.9 - 20/12/2017 =
* Bugfixes

= 2.4.8 - 30/11/2017 =
* Added shortcode [doneren_met_mollie_goal]. See the FAQ for more info
* Bugfixes

= 2.4.7 - 29/11/2017 =
* Bugfixes

= 2.4.6 - 27/11/2017 =
* Translation bugfixes

= 2.4.5 - 06/11/2017 =
* Use translations from wordpress.org

= 2.4.4 =
* Update Mollie API Client to v1.9.1
* Deleting donations is now possible
* Bug fixed with messages in export

= 2.4.3 =
* Variable {interval} possible in description, for the interval of the payment
* Visible in the export if the payment is recurring

= 2.4.2 =
* Use home url instead of site url

= 2.4.1 =
* Project is now visible in projects table
* Setting default interval possible

= 2.4.0 =
* Metadata is also used for first payments
* Set rights for donations and subscriptions
* Visible in donations if payment is recurring

= 2.3.3 =
* Bug fixes

= 2.3.2 =
* Bug fix when redirecting to page in some Wordpress installations

= 2.3.1 =
* Address fields didn't show, this is now fixed

= 2.3.0 =
* [doneren_met_mollie_total] shortcode added to display the total raised money
* At list display the first option is selected by default

= 2.2.4 =
* Bug fixes

= 2.2.3 =
* Bug fixes

= 2.2.2 =
* Fix when Wordpress is used in a sub directory

= 2.2.1 =
* Fixed bug with cancelling subscriptions
* Default payment description set

= 2.2.0 =
* Recurring wasn't possible after change at Mollie API, this is now solved
* Export donations to CSV possible

= 2.1.7 =
* First amount for recrring payment is now the amount of the first period instead of €0,01

= 2.1.6 =
* Email address possible in description with variable {email}
* Update Mollie API client to v1.7.1
* Bug fixes

= 2.1.5 =
* Bug fixes

= 2.1.4 =
* Search function for donations added
* Webhook system updated
* Translation bug fixes
* Several bug fixes

= 2.1.3 =
* Database problems solved

= 2.1.2 =
* Problem solved with updating fields when recurring is active

= 2.1.1 =
* Webhook issues resolved
* Selection menu interval not visible if recurring not enabled
* Fixed problem with translation options payment methods
* Message field now also has full width
* If a recurring payment, only available verification methods are visible

= 2.1.0 =
* Recurring Payments now available!
* Possibility to set the minimum amount to be donated

= 2.0.1 =
* Plugins now also translated into French and German!

= 2.0.0 =
* Settings made more clear
* Free entry and drop-down amount at the same time
* Variables are included in the description
* Choose the display of payment methods
* Possible to add projects
* Added more fields
* Make fields active and / or mandatory
* Add more classes possible
* Translated into Dutch and English
* Code improved
* Bugs resolved


== Upgrade Notice ==

= 2.5.0 =
New! Receive multicurrency payments.

= 2.0.0 =
Herhaalbetalingen (recurring payments) zijn nu beschikbaar!

== Screenshots ==

1. Donations visible in the admin
2. More information about donation and donor
3. General settings
4. Form settings
5. Class settings
6. Mollie settings
7. Subscriptions (recurring payments)
8. Recurring settings
