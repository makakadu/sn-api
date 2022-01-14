<?php //
declare(strict_types=1);
namespace App\Application\Users\GetUserSettings;

class GetUserSettingsResponse implements \App\Application\BaseResponse {
    public string $language;
    public bool $themeIsDark;
    
    /**
     * @param array<mixed> $settings
     */
    public function __construct(array $settings) {
        $this->language = $settings['language'];
        $this->themeIsDark = $settings['themeIsDark'];
    }
}
