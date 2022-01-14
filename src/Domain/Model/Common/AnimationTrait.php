<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\Users\User\User;

trait AnimationTrait {
    //private User $creator;
    
    private string $preview;
    private string $src;
    
    function preview(): string { return $this->preview; }
    function src(): string { return $this->src; }
    
//    function creator(): User {
//        return $this->creator;
//    }
    
}
