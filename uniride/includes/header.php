<?php
/**
 * Cabeçalho de páginas internas (pós-login).
 * Espera que a sessão já esteja aberta (via auth.php).
 */

require_once __DIR__ . '/funcoes.php';

$nome_usuario_logado = $_SESSION['usuario_nome'] ?? '';
$pagina_ativa        = $pagina_ativa ?? '';
$titulo_pagina       = $titulo_pagina ?? 'UniRide';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($titulo_pagina) ?> — UniRide</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <div class="logo">Uni<span>Ride</span></div>
        <nav>
            <ul>
                <li><a href="dashboard.php"        class="<?= $pagina_ativa === 'dashboard' ? 'ativo' : '' ?>">Início</a></li>
                <li><a href="carona_criar.php"     class="<?= $pagina_ativa === 'criar'     ? 'ativo' : '' ?>">Oferecer carona</a></li>
                <li><a href="caronas_buscar.php"   class="<?= $pagina_ativa === 'buscar'    ? 'ativo' : '' ?>">Buscar carona</a></li>
                <li><a href="caronas_minhas.php"   class="<?= $pagina_ativa === 'minhas'    ? 'ativo' : '' ?>">Minhas caronas</a></li>
                <li><a href="solicitacoes.php"     class="<?= $pagina_ativa === 'solicit'   ? 'ativo' : '' ?>">Solicitações</a></li>
                <li><a href="historico.php"        class="<?= $pagina_ativa === 'historico' ? 'ativo' : '' ?>">Histórico</a></li>
                <li><a href="perfil.php"           class="<?= $pagina_ativa === 'perfil'    ? 'ativo' : '' ?>">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
<?= exibir_flash() ?>
