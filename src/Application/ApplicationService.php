<?php
declare(strict_types=1);
namespace App\Application;

interface ApplicationService {
    function execute(BaseRequest $request): BaseResponse;
}