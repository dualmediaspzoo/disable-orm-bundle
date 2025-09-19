<?php

declare(strict_types=1);

namespace DualMedia\DisableORMBundle\Tests\Model;

use Doctrine\ORM\Mapping as ORM;
use DualMedia\DisableORMBundle\Attribute\DisableORM;

#[ORM\Entity]
class ExampleEntity
{
    #[DisableORM]
    public int|null $disabled = 0;

    public int|null $enabled = 1;
}
