<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\User;

use Assert\Assertion;
//use App\Domain\Model\Users\Password;
use App\Domain\Model\Users\Password;

trait UserMainInfo {
    private string $email;
    private string $firstName;
    private string $lastName;
    private string $gender;
    private Password $password;
    /** @var array<string> $roles */
    private array $roles;
    private \DateTime $birthday;

    /**
     * @param array<string> $roles
     */
    protected function setMainInfo(
        string $email,
        Password $password,
        string $firstName,
        string $lastName,
        string $gender,
        string $birthday,
        array $roles
    ): void {
        $this->changeEmail($email);
        $this->changePassword($password);
        $this->changeFirstName($firstName);
        $this->changeLastName($lastName);
        $this->changeGender($gender);
        $this->changeBirthday($birthday);
        foreach ($roles as $role) {
            $this->addRole($role);
        }
    }

    function changeFirstName(string $firstName): void {
        Assertion::minLength($firstName, 1);
        Assertion::maxLength($firstName, 50);

        $this->firstName = $firstName;
    }
    function firstName(): string {
        return $this->firstName;
    }
    function changeLastName(string $lastName): void {
        Assertion::minLength($lastName, 1);
        Assertion::maxLength($lastName, 50);

        $this->lastName = $lastName;
    }
    function lastName(): string {
        return $this->lastName;
    }
    function changeGender(string $gender): void {
        Assertion::minLength($gender, 1);
        Assertion::maxLength($gender, 20);

        $this->gender = $gender;
    }
    function gender(): string {
        return $this->gender;
    }
    function changeEmail(string $email): void {
        Assertion::email($email);

        $this->email = $email;
    }
    function email(): string {
        return $this->email;
    }
    function changePassword(Password $password): void {
         $this->password = $password;
    }
    function password(): Password {
        return $this->password;
    }
    
    function changeBirthday(string $birthday): void {
        $this->birthday = new \DateTime($birthday);
    }
    
    function birthday(): \DateTime {
        return $this->birthday;
    }
    function addRole(string $roleName): void {
        $this->checkRoleName($roleName);
        $this->roles[] = $roleName;
    }
    /** @return array<string> */
    function roles() {
        return $this->roles;
    }
    function removeRole(string $roleName): void {
        $this->checkRoleName($roleName);
        unset($this->roles[$roleName]);
    }

    private function checkRoleName(string $roleName): void {
        Assertion::regex($roleName, '/^(user|guest|admin|banned|deleted)$/');
    }
}
