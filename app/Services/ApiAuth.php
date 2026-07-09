<?php

namespace App\Services;

class ApiAuth
{
    private static ?array $user = null;
    private static ?string $token = null;

    public static function setUser(array $user): void
    {
        self::$user = $user;
    }

    public static function getUser(): ?array
    {
        return self::$user;
    }

    public static function setToken(string $token): void
    {
        self::$token = $token;
    }

    public static function getToken(): ?string
    {
        return self::$token;
    }
}
