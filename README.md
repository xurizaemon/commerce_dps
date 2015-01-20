# Commerce DPS

[Commerce DPS](http://drupal.org/project/commerce_dps) integrates DPS
(aka PaymentExpress) with Drupal Commerce payment and checkout system.

## Features

Commerce DPS supports both merchant and gateway hosted billing methods, and token billing
([in progress](https://drupal.org/node/2397663)).

## Available Payment Methods

### PX Pay

[PX Pay](https://www.paymentexpress.com/Products/Ecommerce/Payment_Express_Hosted) is a 
payment solution hosted by Payment Express; your site won't see credit card details so
you have less requirements for compliance. It's safer and simpler to implement.

### PX Post

[PX Post](https://www.paymentexpress.com/Technical_Resources/Ecommerce_NonHosted/PxPost) 
is a payment solution you host on your site. Credit cards are entered at checkout on your
site. You need to check with your bank whether they permit merchant-hosted checkout.

## Installation & Configuration

1. Download from [Commerce DPS project page](http://drupal.org/project/commerce_dps)
2. Enable the payment processor(s) you wish to use.
3. Visit the Commerce Payment Methods page at admin/commerce/config/payment-methods
4. Enable and edit the payment methods you require.

Pretty much all the details required in the configuration interface are obtained from
Payment Express. If the inline documentation isn't clear, please file an issue and help
improve the interface!

## Troubleshooting


For any questions, the first point of reference should be the issue queue on Drupal.org. 
Please use the search interface provided to see if your problem has been fixed or is 
currently being worked on, and if not create a new issue to discuss it.

Always check your site error logs (both webserver and Drupal logs) and give detail when
making a report.

Documentation fixes are welcome!

## Contributing

We prefer that you use the Drupal.org issue queue, but contributions are welcome via
Github also.

## Credits

* The Commerce DPS maintainer is [Chris Burgess](https://drupal.org/user/76026)
* [Committers to Commerce DPS](https://www.drupal.org/node/1496210/committers)
* Payment Express team, for providing a solid and professional merchant service!
* Thanks also to those who have reported issues, made suggestions or sponsored work.
