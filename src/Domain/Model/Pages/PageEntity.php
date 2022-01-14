<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages;

use App\Domain\Model\Pages\Page\Page;

trait PageEntity {
    
    protected Page $owningPage;
    
    function owningPage(): Page {
        return $this->owningPage;
    }
}
