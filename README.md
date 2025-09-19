[![Packagist Downloads](https://img.shields.io/packagist/dt/dualmedia/disable-orm-bundle)](https://packagist.org/packages/dualmedia/disable-orm-bundle)

# DisableORM Bundle

A Symfony + Doctrine bundle to allow disabling fields from being seen by DoctrineORM.

## Why

This bundle will allow you to seamlessly update your application, assuming you have multiple versions running at the same time, with different database expectations.

While adding new entities is not an issue, as they're simply not present in the old application versions, this is not the case for entities being modified. You must either shut down all instances before running migrations
which could cause differences in database schema expectations or... use this bundle.

With this, you're able to safely "remove" a field, so that the old version of the application can still use it before update, and the new one can ignore it.

> As this modifies the ORM metadata the field is transparently removed from ORM and is not usable outside of raw SQL queries.

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

You must manually set the doctrine class metadata factory name value under your entity manager to use this bundle.

```yaml
doctrine:
  orm:
    entity_managers:
      <manager_name>:
        class_metadata_factory_name: DualMedia\DisableORMBundle\Factory
```

## Config

```yaml
dm_disable_orm:
  # you can specify commands which should not be affected by DisableORM logic
  # defaults are below, you don't need to modify them unless you want to
  disable_on_commands:
    - 'doctrine:schema:validate'
    - 'doctrine:migrations:diff'
```

## Usage

The `#[DisableORM]` attribute prevents doctrine from loading and creating the column from the database.

> You should set a default value for the column before this, as otherwise you might get issues inserting new entities!

Use your entities as usual, when you decide you want to remove a field from it simply add `#[DisableORM]` on it, then update your application once.

From then, you can safely remove the field with a migration while not causing any downtime.

## Example

```php
#[ORM\Entity]
class FooEntity {
    #[ORM\Column]
    public int|null $normalField = null;

    #[DisableORM]
    #[ORM\Column(options: ['default' => false])]
    public int|null $someField = null;
    
    // ... getters and setters, etc.
}
```

