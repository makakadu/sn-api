<?php
declare(strict_types=1);
namespace App\Domain\Model\Users;

use App\Domain\Model\ValueObject;
use Assert\Assertion;

class Password implements ValueObject {
    /** @var mixed $value */
    private $value;

    function __construct(string $password) {
        Assertion::minLength($password, 7);
        Assertion::maxLength($password, 30);

        $this->value = \password_hash($password, PASSWORD_DEFAULT);
    }

    /** @return mixed */
    function value() {
        return $this->value;
    }

    function equals(ValueObject $object) : bool {
        return $this->value === $object->value();
    }

    public static function fromNative(string $password): self {
        $hashedPassword = new self('lolkekcheburek');
        $hashedPassword->value = $password;
        return $hashedPassword;
    }
}
