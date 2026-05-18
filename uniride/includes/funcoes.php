<?php
/**
 * UniRide — Funções auxiliares globais.
 */

/**
 * Saída segura contra XSS. Use sempre que imprimir conteúdo de banco/usuário.
 */
function h($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Remove tudo que não é dígito (útil para CPF, telefone, placa).
 */
function so_numeros(string $valor): string {
    return preg_replace('/\D+/', '', $valor);
}

/**
 * Valida se o e-mail é institucional (.edu.br).
 */
function email_institucional_valido(string $email): bool {
    $email = trim(strtolower($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return str_ends_with($email, '.edu.br');
}

/**
 * Grava uma mensagem flash (sucesso/erro/aviso) para ser exibida na próxima página.
 */
function flash(string $tipo, string $mensagem): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/**
 * Renderiza a mensagem flash (se houver) e a remove da sessão.
 */
function exibir_flash(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $tipo = $_SESSION['flash']['tipo'] ?? 'aviso';
    $msg  = $_SESSION['flash']['mensagem'] ?? '';
    unset($_SESSION['flash']);

    $classe = match($tipo) {
        'sucesso' => 'mensagem-sucesso',
        'erro'    => 'mensagem-erro',
        default   => 'mensagem-aviso',
    };
    return '<div class="mensagem ' . $classe . '">' . h($msg) . '</div>';
}

/**
 * Formata data Y-m-d para d/m/Y.
 */
function data_br(?string $data): string {
    if (!$data) return '—';
    $ts = strtotime($data);
    return $ts ? date('d/m/Y', $ts) : '—';
}

/**
 * Formata datetime Y-m-d H:i:s para d/m/Y H:i.
 */
function datetime_br(?string $dt): string {
    if (!$dt) return '—';
    $ts = strtotime($dt);
    return $ts ? date('d/m/Y H:i', $ts) : '—';
}

/**
 * Formata um valor decimal como moeda BR.
 */
function moeda_br(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Atalho seguro para pegar input do POST/GET.
 */
function in(string $chave, $padrao = '') {
    return trim((string)($_REQUEST[$chave] ?? $padrao));
}
