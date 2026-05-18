<?php
/**
 * Tela 3: Dashboard (painel principal).
 * Mostra estatísticas reais do usuário logado.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexao.php';

$uid = (int)$_SESSION['usuario_id'];

// Caronas ativas oferecidas pelo usuário (como motorista)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM caronas
                        WHERE motorista_id = :uid AND status = 'ativa'");
$stmt->execute([':uid' => $uid]);
$total_caronas_ativas = (int)$stmt->fetchColumn();

// Solicitações pendentes que envolvem o usuário:
//   - solicitações recebidas (em caronas que ele oferece) AINDA pendentes
//   - solicitações enviadas pelo usuário ainda pendentes
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM solicitacoes s
      JOIN caronas c ON c.id = s.carona_id
     WHERE (c.motorista_id = :uid1 OR s.passageiro_id = :uid2)
       AND s.status = 'pendente'
");
$stmt->execute([':uid1' => $uid, ':uid2' => $uid]);
$total_solicit_pendentes = (int)$stmt->fetchColumn();

// Viagens concluídas (como motorista ou passageiro aceito)
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM caronas c
     WHERE c.status = 'concluida'
       AND (c.motorista_id = :uid1
            OR EXISTS (SELECT 1 FROM solicitacoes s
                        WHERE s.carona_id = c.id
                          AND s.passageiro_id = :uid2
                          AND s.status = 'aceita'))
");
$stmt->execute([':uid1' => $uid, ':uid2' => $uid]);
$total_viagens_concluidas = (int)$stmt->fetchColumn();

$titulo_pagina = 'Dashboard';
$pagina_ativa  = 'dashboard';
include __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-saudacao">
    <h1>Olá, <?= h(explode(' ', $_SESSION['usuario_nome'])[0]) ?>!</h1>
    <p>Bem-vindo(a) ao UniRide. Veja abaixo um resumo da sua atividade.</p>
</section>

<section class="cards-resumo">
    <div class="card-resumo">
        <span class="numero"><?= $total_caronas_ativas ?></span>
        <span class="label">Caronas ativas</span>
    </div>
    <div class="card-resumo">
        <span class="numero"><?= $total_solicit_pendentes ?></span>
        <span class="label">Solicitações pendentes</span>
    </div>
    <div class="card-resumo">
        <span class="numero"><?= $total_viagens_concluidas ?></span>
        <span class="label">Viagens concluídas</span>
    </div>
</section>

<h2>O que você quer fazer?</h2>
<div class="acoes-rapidas">
    <a href="carona_criar.php"   class="btn btn-primario"   style="width:auto;">Oferecer uma carona</a>
    <a href="caronas_buscar.php" class="btn btn-secundario">Buscar uma carona</a>
    <a href="solicitacoes.php"   class="btn btn-secundario">Ver solicitações</a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
