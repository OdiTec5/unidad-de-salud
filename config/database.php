<?php

class Database
{
    private static string $host = 'localhost';
    private static string $bd = 'unidad_salud';
    private static string $usuario = 'root';
    private static string $password = '';
    private static ?PDO $pdo = null;

    public static function obtener(): PDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . self::$host . ';dbname=' . self::$bd . ';charset=utf8mb4';
            self::$pdo = new PDO($dsn, self::$usuario, self::$password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return self::$pdo;
    }
}
