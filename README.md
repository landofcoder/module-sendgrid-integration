# Mage2 Module Lof SendGrid

    ``landofcoder/module-sendgrid-integration``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)

## Main Functionalities
Magento 2 Sendgrid Extension supports to take control over your email marketing strategy effectively.

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Lof`
 - Enable the module by running `php bin/magento module:enable Lof_SendGrid`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require landofcoder/module-sendgrid-integration`
 - enable the module by running `php bin/magento module:enable Lof_SendGrid`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

## Configuration
1. Create SendGrid account

2. Get sendgrid api

3. Create subscribe contacts list on sendgrid

4. Create unsubscript contacts list on sendgrid

5. Config on module

 - Enabled (sendgrid/general/enabled)

 - API Key (sendgrid/general/api_key)

 - Subscribe List (sendgrid/general/subscribe_list)

 - Unsubscribe List	 (sendgrid/general/unsubscribe_list)
 
 - Other List	 (sendgrid/general/other_list)

 - Add customers without subscriptions status in SendGrid	 (sendgrid/general/add_customer)

 - webhook_url (sendgrid/general/webhook_url)

 - Webhook Url	 (sendgrid/general/list_for_new_customer)

 - Cron Enabled (sendgrid/sync/cron_enable)
