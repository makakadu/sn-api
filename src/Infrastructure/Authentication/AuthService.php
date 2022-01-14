<?php

declare(strict_types=1);

namespace App\Infrastructure\Authentication;

use App\Domain\Model\AuthenticationService;

class AuthService implements AuthenticationService {
    private $currentUserId;
    private $expiresIn;
    private $currentUserRoles;

    function setCurrentUserId($id): void {
        $this->currentUserId = $id;
    }

    function currentUserId(): ?string {
        return $this->currentUserId;
    }

    function setCurrentUserRoles(array $roles): void {
        $this->currentUserRoles = $roles;
    }

    function currentUserRoles(): array {
        return $this->currentUserRoles;
    }

    function isAuthenticated(): bool {
        //echo is_string($this->currentUserId);exit();
        return $this->currentUserId !== null;
    }
    
    function expiresIn(): ?int {
        return $this->expiresIn;
    }
    
    function setExpiresIn(?int $expiresIn): void {
        $this->expiresIn = $expiresIn;
    }
//    private function validate(array $signUpData):bool {
//        $email = $signUpData['email'];
//        $firstName = $signUpData['firstname'];
//        $lastName = $signUpData['lastname'];
//        $password = $signUpData['password'];
//
//        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//            $this->$validationError = "Вы ввели неоректный E-mail";
//        //} else if (!$this->emailIsUnique($email)) {
//        //    $this->$validationError = "Этот эмейл уже зарегистрирован";
//        } else if (strlen($firstName) < 2 || strlen($firstName) > 30) {
//            $this->$validationError = "Имя должно быть не меньше 2 символов и не больше 30";
//        } else if (strlen($lastName) < 2 || strlen($lastName) > 30) {
//            $this->$validationError = "Фамилиия должна быть не меньше 2 символов и не больше 30";
//        } else if (strlen($password) < 16 || strlen($password) > 40) {
//            $this->$validationError = "Пароль должен быть не меньше 16 символов и не больше 30";
//        }
//
//        if($this->$validationError) {
//            return false;
//        }
//
//        return true;
//    }

//    private function emailIsUnique(string $email):bool {
//        $user = $this->userRepository->getByEmail($email);
//        if($user) {
//            return false;
//        }
//        return true;
//    }
}