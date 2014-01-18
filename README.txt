COMMERCE DPS
------------

[Commerce DPS](http://drupal.org/project/commerce_dps) integrates DPS
(aka PaymentExpress) with Drupal Commerce payment and checkout system.

AVAILABLE DPS PAYMENT METHODS
-----------------------------

Currently PxPay (payment via redirect to DPS-hosted page) is
available. Please file an issue or contact the author
(chris@fuzion.co.nz) to have additional methods implemented.

INSTALLING COMMERCE DPS MODULE
------------------------------

1. Download latest module from http://drupal.org/project/commerce_dps

2. Enable Commerce DPS PxPay module as usual: /admin/modules

CONFIGURING PAYMENT METHOD - PxPay
----------------------------------

1. Obtain your DPS PxPay credentials

2. Visit the Commerce Payment Methods page at admin/commerce/config/payment-methods

3. Edit the Commerce Payment Express (PxPay) method and configure accordingly.

TROUBLESHOOTING
---------------

For any questions, the first point of reference should be the issue
queue on Drupal.org. Please use the search interface provided to see
if your problem has been fixed or is currently being worked on, and if
not create a new issue to discuss it.

Always check your site error logs (both webserver and Drupal logs) and
give detail when making a report.

Documentation fixes are welcome!

FAQ
---

Q: I get mysterious validation errors!

A: If your Drupal logs direct you to issue #1799294, you may be
   running into issues with Suhosin's default configuration preventing
   the DPS response being recognised by Drupal. This requires some
   webserver configuration to resolve.

CREDITS
-------

Commerce Stripe integration has been written by [Chris Burgess](https://drupal.org/user/76026) and many contributors via Drupal.org.
