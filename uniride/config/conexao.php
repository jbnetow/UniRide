<?php
/**
 * UniRide — Conexão PDO com MySQL.
 * Carregado por todas as páginas que acessam o banco.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'uniride');
define('DB_USER', 'root');     // padrão XAMPP
define('DB_PASS', '');         // senha em branco no XAMPP padrão
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die('Erro de conexão com o banco de dados. Verifique se o MySQL está rodando no XAMPP.');
}
