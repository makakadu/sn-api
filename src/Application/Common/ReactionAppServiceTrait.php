<?php
declare(strict_types=1);
namespace App\Application\Common;

trait ReactionAppServiceTrait {

    function validateParamReactionType(int $type): void {
        if($type < 0 || $type > 8) {
            throw new \App\Application\Exceptions\ValidationException("Incorrect reaction type");
        }
    }
}