# Hyva Themes - GraphQL Tokens module

[![Hyvä Themes](https://repository-images.githubusercontent.com/303806175/a3973c80-479c-11eb-8716-03e369d87143)](https://hyva.io/)

## hyva-themes/magento2-graphql-tokens

![Supported Magento Versions][ico-compatibility]

This module adds GraphQL tokens to the CustomerData sections to enable GraphQL calls from a Magento template.

Compatible with Magento 2.3.4 and higher.

## What does it do?
It adds:
 - `signin_token` to the `customer` section
 - `cartId` to the `cart` section
 
The CartId is the masked `cartId` that is needed for guest carts.
 
When logged in, the customer `signin_token` can be used to get the *real cart id*.
 
## Installation
  
1. Install via composer
    ```
    composer config repositories.hyva-themes/magento2-graphql-tokens git git@github.com:hyva-themes/magento2-graphql-tokens.git
    composer require hyva-themes/magento2-graphql-tokens
    ```
2. Enable module
    ```
    bin/magento setup:upgrade
    ```
## Configuration
  
No configuration needed.
  
## How does it work?
There are `after-plugins` that add the tokens to the individual sections.
 
If a token already exists, it uses the existing one. Otherwise, a fresh token is generated on the fly.

## Credits

- [Willem Wigman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.txt) for more information.

[ico-compatibility]: https://img.shields.io/badge/magento-%202.3%20|%202.4-brightgreen.svg?logo=magento&longCache=true&style=flat-square

[link-author]: https://github.com/wigman
[link-contributors]: ../../contributors
