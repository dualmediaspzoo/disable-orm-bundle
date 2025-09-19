[![Packagist Downloads](https://img.shields.io/packagist/dt/dualmedia/disable-orm-bundle)](https://packagist.org/packages/dualmedia/disable-orm-bundle)

# DisableORM Bundle

A Symfony + Doctrine bundle to allow disabling fields from being seen by DoctrineORM.

## Install

Simply `composer require dualmedia/disable-orm-bundle`

Then add the bundle to your `config/bundles.php` file like so

```php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    // other bundles ...
    DualMedia\DisableORMBundle\DisableORMBundle::class => ['all' => true],
];
```

## Setup


## Usage

