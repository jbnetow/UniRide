<?php
/**
 * UniRide — Guarda de autenticação.
 *
 * Inclua este arquivo no topo de toda página que exige login.
 * Carrega as funções globais e verifica a sessão.
 * Se o usuário não estiver logado, é redirecionado para a tela de login.
 */

// Carrega as funções utilitárias (h, in, flash, etc.) para todas as páginas internas.
require_once __DIR__ . '/funcoes.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
