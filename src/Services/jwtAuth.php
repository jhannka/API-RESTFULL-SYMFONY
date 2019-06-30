<?php

namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;

class jwtAuth {

    public $manaager;

    public function __construc($manaager) {

        $this->manaager = $manaager;
    }

    public function singUp($email, $password) {

        $user = $this->manaager->getRepository(User::class)->findOneBy([
            'email' => $email,
            'password' => $password
        ]);

        $sinup = false;

        if (is_object($user)) {
            $sinup = true;
        }

        if ($sinup) {

            $token = [
                'sub' => $user->getId(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'email' => $user->getEmail(),
                'iat' => time(),
                'iat' => time() + (7 * 24 * 60 *60),
            ];
        }
    }

}
