<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\GetSettings;

use App\Application\BaseResponse;

class GetSettingsResponse implements BaseResponse {
    
    public string $language;
    public int $theme;
    
    public function __construct(string $language, int $theme) {
        $this->language = $language;
        $this->theme = $theme;
    }

}
