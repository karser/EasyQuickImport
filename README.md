# EasyQuickImport — Import transactions, invoices and bills into QuickBooks Desktop from Excel or CSV

[![Build Status](https://gitlab.dev.trackmage.com/karser/easyquickimport/badges/master/pipeline.svg)](https://gitlab.dev.trackmage.com/karser/easyquickimport/pipelines)
[![Total Downloads](https://poser.pugx.org/karser/easy-quick-import/downloads)](https://packagist.org/packages/karser/easy-quick-import)

EasyQuickImport is a tool that helps you import invoices, bills, transactions,
customers and vendors into QuickBooks Desktop in multiple currencies in bulk.

### Features
- **Invoices, bills, transactions (General Journal entries), customers, and vendors import in csv, xlsx, xls formats.**
- **Multicurrency support with NON-USD base currency**. The currency of your accounts is automatically detected
  from the imported Chart of accounts. 
- **Cross Currency Transactions**. Transfer between accounts of different currencies goes through the Undeposited
  funds account. The Undeposited funds balance remains zero because EasyQuickImport uses the accurate historical exchange rate.
- **Historical exchange rate**. EasyQuickImport automatically obtains the exchange rate from European Central Bank
  on a given date for any currency for each transaction. You can use [other exchange rate sources](https://github.com/florianv/exchanger) as well.
- **Multi-tenancy**: if you have multiple company files on the same computer, you can add them all to EasyQuickImport.


## Getting started:

Either install [self-hosted](#how-to-install-easyquickimport) or [sign up](https://app.easyquickimport.com/register) for a free cloud account.

### Connect EasyQuickImport to QuickBooks Desktop

**In EasyQuickImport**:

Add a company file in Users, define username, password and specify the home currency.
It's recommended to specify the company file location if you are going to use multiple company files on the same computer.
Once it's done download the QWC file.

**In Quickbooks** click `File / Update Web Services`

Then Add an Application, in the file dialog select the downloaded QWC.
Then click Yes, then select "When company file is open" and click continue.
When it's done don't forget to specify the password that you defined in EasyQuickImport.

[![Connect EasyQuickImport to QuickBooks Desktop](https://user-images.githubusercontent.com/1675033/117167904-5a9d6780-add0-11eb-901d-8228443be18c.png)](https://www.youtube.com/watch?v=6kVJrthCQr0)

### How to import invoices from Excel into QuickBooks Desktop

[![Import invoices from Excel into QuickBooks Desktop](https://user-images.githubusercontent.com/1675033/117167991-7274eb80-add0-11eb-99ef-e6f27e72e509.png)](https://www.youtube.com/watch?v=ZKe002JUIww)

### How to import transactions from Excel into QuickBooks Desktop

[![How to import transactions from Excel into QuickBooks Desktop](https://user-images.githubusercontent.com/1675033/117168077-83bdf800-add0-11eb-8ad9-9d8668164752.png)](https://www.youtube.com/watch?v=-hmhxs72W1E)

### How to import bills and vendors from Excel into QuickBooks Desktop

[![How to import bills and vendors from Excel into QuickBooks Desktop](https://user-images.githubusercontent.com/1675033/117168137-90dae700-add0-11eb-826d-b09c1cbd1b71.png)](https://www.youtube.com/watch?v=vcSeREomzuE)

### How to import multicurrency transactions from Excel into QuickBooks Desktop

[![How to import multicurrency transactions from Excel into QuickBooks Desktop](https://user-images.githubusercontent.com/1675033/117168217-a223f380-add0-11eb-8033-def86fc9c824.png)](https://www.youtube.com/watch?v=NvMpb3wVIXc)


## How to install EasyQuickImport

1. Clone the repo
```
git clone https://github.com/karser/EasyQuickImport.git
```
2. Install packages with composer
```
composer install
```
3. Copy `.env` to `.env.local` and configure DATABASE_URL and DOMAIN
4. Recreate the database
```
bin/console doctrine:schema:drop --full-database --force \
&& bin/console doctrine:migrations:migrate -n
#fixtures
bin/console doctrine:fixtures:load
```
5. Create the user
```
bin/console app:create-user user@example.com --password pass123
```
6. Run the server
```
cd ./public
php -S 127.0.0.1:9090
```

### Tests
```
bin/phpunit
```

### phpstan
```
vendor/bin/phpstan analyse -c phpstan.neon
vendor/bin/phpstan analyse -c phpstan-tests.neon
```

### Lookup historical currency rate
``` 
bin/console app:currency:get USD --date 2020-03-05 --base HKD
```
