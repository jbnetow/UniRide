<?php
/**
 * Tela 6: Minhas caronas (oferecidas).
 * Permite marcar como concluída ou cancelar.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexao.php';

$uid = (int)$_SESSION['usuario_id'];

// Tratamento de ação (concluir / cancelar) — via GET para simplicidade do PoC
$acao       = $_GET['acao']     ?? '';
$carona_id  = (int)($_GET['id'] ?? 0);

if (in_array($acao, ['concluir', 'cancelar'], true) && $carona_id > 0) {
    // Garante que a carona é do usuário antes de alterar
    $stmt = $pdo->prepare("SELECT id, status FROM caronas
                            WHERE id = :id AND motorista_id = :uid LIMIT 1");
    $stmt->execute([':id' => $carona_id, ':uid' => $uid]);
    $car = $stmt->fetch();

    if (!$car) {
        flash('erro', 'Carona não encontrada ou não pertence a você.');
    } elseif ($car['status'] !== 'ativa') {
        flash('aviso', 'Esta carona já está finalizada.');
    } else {
        $novo_status = $acao === 'concluir' ? 'concluida' : 'cancelada';
        $stmt = $pdo->prepare("UPDATE caronas SET status = :s WHERE id = :id");
        $stmt->execute([':s' => $novo_status, ':id' => $carona_id]);

        // Se cancelou, marca todas as solicitações pendentes como canceladas
        if ($acao === 'cancelar') {
            $stmt = $pdo->prepare("UPDATE solicitacoes
                                      SET status = 'cancelada', data_resposta = NOW()
                                    WHERE carona_id = :id AND status = 'pendente'");
            $stmt->execute([':id' => $carona_id]);
        }
        flash('sucesso', $acao === 'concluir'
            ? 'Carona marcada como concluída.'
            : 'Carona cancelada.');
    }
    header('Location: caronas_minhas.php');
    exit;
}

// Lista caronas do motorista
$stmt = $pdo->prepare("
    SELECT c.*,
           (SELECT COUNT(*) FROM solicitacoes s
             WHERE s.carona_id = c.id AND s.status = 'pendente') AS solicitacoes_pendentes
      FROM caronas c
     WHERE c.motorista_id = :uid
     ORDER BY FIELD(c.status,'ativa','concluida','cancelada'),
              c.data_viagem DESC, c.horario_saida DESC
");
$stmt->execute([':uid' => $uid]);
$minhas_caronas = $stmt->fetchAll();

$titulo_pagina = 'Minhas caronas';
$pagina_ativa  = 'minhas';
include __DIR__ . '/../includes/header.php';
?>

<h1>Minhas caronas</h1>
<p style="color:#666; margin-bottom:20px;">Caronas que você ofereceu. Aqui você pode acompanhar e gerenciar cada uma.</p>

<a href="carona_criar.php" class="btn btn-primario" style="width:auto; margin-bottom:24px;">+ Nova carona</a>

<?php if (empty($minhas_caronas)): ?>
    <div class="vazio">
        <p>Você ainda não ofereceu nenhuma carona.</p>
        <a href="carona_criar.php" class="btn btn-primario" style="width:auto;">Oferecer minha primeira carona</a>
    </div>
<?php else: ?>
    <div class="lista-caronas">
        <?php foreach ($minhas_caronas as $c): ?>
            <div class="card-carona">
                <div class="rota">
                    <?= h($c['origem']) ?>
                    <span class="seta">→</span>
                    <?= h($c['destino']) ?>
                    <span class="badge badge-<?= h($c['status']) ?>"><?= h(ucfirst($c['status'])) ?></span>
                </div>

                <div class="info-linha">
                    <span class="item">📅 <?= h(data_br($c['data_viagem'])) ?></span>
                    <span class="item">🕐 <?= h(substr($c['horario_saida'], 0, 5)) ?></span>
                    <span class="item">👥 <?= (int)$c['vagas_disponiveis'] ?>/<?= (int)$c['vagas_total'] ?> vagas</span>
                    <span class="item">💰 <?= h(moeda_br((float)$c['valor_por_passageiro'])) ?>/pessoa</span>
                </div>

                <?php if ((int)$c['solicitacoes_pendentes'] > 0): ?>
                    <div class="mensagem mensagem-aviso" style="margin-bottom:10px;">
                        Você tem <strong><?= (int)$c['solicitacoes_pendentes'] ?></strong>
                        solicitação(ões) pendente(s) para esta carona.
                    </div>
                <?php endif; ?>

                <div class="acoes">
                    <a href="solicitacoes.php?aba=recebidas" class="btn btn-secundario btn-sm">Ver solicitações</a>
                    <?php if ($c['status'] === 'ativa'): ?>
                        <a href="?acao=concluir&id=<?= (int)$c['id'] ?>"
                           class="btn btn-sucesso btn-sm"
                           data-confirma="Marcar esta carona como concluída?">Marcar como concluída</a>
                        <a href="?acao=cancelar&id=<?= (int)$c['id'] ?>"
                           class="btn btn-perigo btn-sm"
                           data-confirma="Cancelar esta carona? Os passageiros pendentes serão notificados.">Cancelar carona</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
