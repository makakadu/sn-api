<?php
namespace App\Infrastructure\Domain\Model\Users\User\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use App\Domain\Model\Users\User\UserId;

/**
 * Description of DoctrineUserId
 *
 * @author aleksey
 */
class DoctrineUserId extends GuidType {
    public function getName() {
        return 'UserId';
    }
    
    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        return $value;
    }
    
    public function convertToPHPValue($value, AbstractPlatform $platform) {
        return $value ? new UserId($value) : null; // https://stackoom.com/question/3wmjk/%E6%9F%A5%E8%AF%A2%E5%85%B7%E6%9C%89%E5%BF%85%E9%9C%80%E5%AD%97%E6%AE%B5%E7%9A%84Doctrine%E7%B1%BB%E8%A1%A8%E7%BB%A7%E6%89%BF
    }
}