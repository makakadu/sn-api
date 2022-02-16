<?php

declare(strict_types=1);

//use Zend\Permissions\Rbac\Rbac;
//use App\Application\SimpleImage;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Password;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\User\Username;

require './prepare.php';

$date = '13-05-1993';

try {
    $hashedPassword = new Password('1111111');

    $user = new User('fsdfsdf@mail.ru', $hashedPassword, 'Упал', 'Намоченный', new Username('oper'), 'male', 'ru', $date, []);
    $user2 = new User('anton_petrov@mail.ru', $hashedPassword, 'Anton', 'Petrov', new Username('antoha'), 'male', 'ru', $date, []);
    $user3 = new User('igor_petrov@mail.ru', $hashedPassword, 'Igor', 'Petrov', new Username('igor_net'), 'male', 'ru', $date, []);
    $user4 = new User('qwerty@mail.ru', $hashedPassword, 'Petr', 'Antonov', new Username('petruha'), 'male', 'ru', $date, []);
    $user5 = new User('galyapetrova@mail.ru', $hashedPassword, 'Galya', 'Petrova', new Username('galka'), 'female', 'ru', $date, []);
    $user6 = new User('peyutyutytya@mail.ru', $hashedPassword, 'Evgeniy', 'Vasilyev', new Username('yozhyk'), 'male', 'ru', $date, []);
    $user7 = new User('yrtyrtyr@mail.ru', $hashedPassword, 'Anton', 'Zarubin', new Username('zaruba'), 'male', 'ru', $date, []);
    $user8 = new User('werwerwer@mail.ru', $hashedPassword, 'Denis', 'Fanta', new Username('fanta'), 'male', 'ru', $date, []);
    $user9 = new User('gdfgdfgdg@mail.ru', $hashedPassword, 'Fedor', 'Pomedor', new Username('barbos'), 'male', 'ru', $date, []);
    $user10 = new User('fsdfsdfdf@mail.ru', $hashedPassword, 'Georgiy', 'Glinomesov', new Username('gay_orgy'), 'female', 'ru', $date, []);
    $user11 = new User('fsdffsdf@mail.ru', $hashedPassword, 'Sergey', 'Pushkin', new Username('ser_gey'), 'male', 'ru', $date, []);
    $user12 = new User('antfon_petrov@mail.ru', $hashedPassword, 'Pavel', 'Petrushkin', new Username('pashtet'), 'male', 'ru', $date, []);
    $user13 = new User('igfor_petrov@mail.ru', $hashedPassword, 'Denis', 'Vatrushkin', new Username('denis_penis'), 'male', 'ru', $date, []);
    $user14 = new User('qwefrty@mail.ru', $hashedPassword, 'Zina', 'Magazinova', new Username('zina_s_magazina'), 'male', 'ru', $date, []);
    $user15 = new User('galfyapetrova@mail.ru', $hashedPassword, 'Lolita', 'Kekova', new Username('lola'), 'female', 'ru', $date, []);
    $user16 = new User('peyfutyutytya@mail.ru', $hashedPassword, 'Diana', 'Magadanova', new Username('dianon'), 'male', 'ru', $date, []);
    $user17 = new User('yrtfyrtyr@mail.ru', $hashedPassword, 'Zinoviy', 'Pypka', new Username('pipitr'), 'male', 'ru', $date, []);

    $rep = $container->get(UserRepository::class);

    $rep->add($user);
    $rep->add($user2);
    $rep->add($user3);
    $rep->add($user4);
    $rep->add($user5);
    $rep->add($user6);
    $rep->add($user7);
    $rep->add($user8);
    $rep->add($user9);
    $rep->add($user10);
    $rep->add($user11);
    $rep->add($user12);
    $rep->add($user13);
    $rep->add($user14);
    $rep->add($user15);
    $rep->add($user16);
    $rep->add($user17);

    $rep->flush();
} catch (\Exception|\Error $ex) {
    echo $ex->getTraceAsString() . '<br><br>' . $ex->getMessage();exit();
}
exit();