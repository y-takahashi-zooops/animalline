<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Security\Core\Encoder;

use Eccube\Common\EccubeConfig;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class PasswordEncoder implements PasswordHasherInterface
{
    /**
     * @var string
     */
    public $auth_magic;

    /**
     * @var string
     */
    public $auth_type;

    /**
     * @var string
     */
    public $password_hash_algos;

    public function __construct(EccubeConfig $eccubeConfig)
    {
        $this->auth_magic = $eccubeConfig->get('eccube_auth_magic');
        $this->auth_type = $eccubeConfig->get('eccube_auth_type');
        $this->password_hash_algos = $eccubeConfig->get('eccube_password_hash_algos');
    }

    /**
     * Set Auth Magic.
     *
     * @param $authMagic
     */
    public function setAuthMagic($authMagic)
    {
        $this->auth_magic = $authMagic;
    }
    
    /**
     * Symfony 5.3+ のインターフェースに必須のメソッド
     */
    public function hash(string $plainPassword): string
    {
        return $this->encodePassword($plainPassword, '');
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return $this->isPasswordValid($hashedPassword, $plainPassword, '');
    }

    public function needsRehash(string $hashedPassword): bool
    {
        // 再ハッシュが必要なら true を返す（今回は常に false）
        // return false;

        // 現在のハッシュが設定と一致するかを動的に判断 2025/6/13 高橋マサ
        // return password_needs_rehash($hashedPassword, PASSWORD_ARGON2ID); // ハッシュ化にsodiumを使用の場合
        return password_needs_rehash($hashedPassword, PASSWORD_BCRYPT, ['cost' => 12]); // ハッシュ化にbcryptを使用の場合
    }

    /**
     * Checks a raw password against an encoded password.
     *
     * @param string $encoded An encoded password
     * @param string $raw A raw password
     * @param string $salt The salt
     *
     * @return bool true if the password is valid, false otherwise
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        if ($encoded == '') {
            return false;
        }

        if ($this->auth_type == 'PLAIN') {
            if ($raw === $encoded) {
                return true;
            }
        } else {
            // 旧バージョン(2.11未満)からの移行を考慮
            if (empty($salt)) {
                $hash = sha1($raw.':'.$this->auth_magic);
            } else {
                $hash = $this->encodePassword($raw, $salt);
            }

            if ($hash === $encoded) {
                return true;
            }
        }

        return false;
    }

    /**
     * Encodes the raw password.
     *
     * @param string $raw The password to encode
     * @param string $salt The salt
     *
     * @return string The encoded password
     */
    public function encodePassword($raw, $salt)
    {
        if ($salt == '') {
            $salt = $this->auth_magic;
        }
        if ($this->auth_type == 'PLAIN') {
            $res = $raw;
        } else {
            dd($this->password_hash_algos, $raw . ':' . $this->auth_magic, $salt);
            $res = hash_hmac($this->password_hash_algos, $raw.':'.$this->auth_magic, $salt);
        }

        return $res;
    }

    /**
     * saltを生成する.
     *
     * @param int $length
     *
     * @return string
     */
    public function createSalt($length = 5)
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}
