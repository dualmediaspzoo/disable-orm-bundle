<?php

namespace DualMedia\DisableORMBundle\Attribute;

/**
 * Apply this attribute alongside other ORM attributes on a property to prevent it being used by the application.
 *
 * It will no longer be loaded from the database, allowing a smooth transition between versions, but migration will still see it as existing.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DisableORM
{
}
