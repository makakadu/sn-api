<?php
declare(strict_types=1);
namespace App\Domain\Model\Users;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Connection\Connection;
use App\Domain\Model\Users\ConnectionsList\ConnectionsList;

interface ComplexPrivacySetting {
    function ownerId(): string;
    
    function accessLevel(): int;
    /** @return Collection<int, Connection> */
    function allowedConnections(): Collection;
    /** @return Collection<int, Connection> */
    function unallowedConnections(): Collection;
    /** @return Collection<int, ConnectionsList> */
    function allowedLists(): Collection;
    /** @return Collection<int, ConnectionsList> */
    function unallowedLists(): Collection;
}
