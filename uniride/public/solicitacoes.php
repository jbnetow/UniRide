<?php
/**
 * Tela 9: Solicitações.
 *
 * Duas abas:
 *   - recebidas (como motorista): aceitar/recusar
 *   - enviadas  (como passageiro): cancelar
 *
 * Ações afetam o banco com transações para manter
 * vagas_disponiveis sincronizado em caronas.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexao.php';

$uid = (int)$_SESSION['usuario_id'];
$aba = $_GET['aba'] ?? 'recebidas';
if (!in_array($aba, ['recebidas', 'enviadas'], true)) {
    $aba = 'recebidas';
}

// ----------------- Processamento de ações -----------------
$acao = $_GET['acao'] ?? '';
$sid  = (int)($_GET['sid'] ?? 0);

if ($acao && $sid > 0) {
    // Busca a solicitação com dados da carona
    $stmt = $pdo->prepare("
        SELECT s.*, c.motorista_id, c.status AS carona_status, c.vagas_disponiveis
          FROM solicitacoes s
          JOIN caronas c ON c.id = s.carona_id
         WHERE s.id = :sid LIMIT 1
    ");
    $stmt->execute([':sid' => $sid]);
    $sol = $stmt->fetch();

    if (!$sol) {
        flash('erro', 'Solicitação não encontrada.');
    } else {
        $eh_motorista  = ((int)$sol['motorista_id']   === $uid);
        $eh_passageiro = ((int)$sol['passageiro_id']  === $uid);

        try {
            $pdo->beginTransaction();

            if ($acao === 'aceitar' && $eh_motorista && $sol['status'] === 'pendente') {
                if ((int)$sol['vagas_disponiveis'] <= 0) {
                    throw new RuntimeException('Sem vagas para aceitar mais passageiros.');
                }
                $pdo->prepare("UPDATE solicitacoes
                                  SET status = 'aceita', data_resposta = NOW()
                                WHERE id = :sid")->execute([':sid' => $sid]);
                $pdo->prepare("UPDATE caronas
                                  SET vagas_disponiveis = vagas_disponiveis - 1
                                WHERE id = :cid")->execute([':cid' => $sol['carona_id']]);
                flash('sucesso', 'Solicitação aceita.');

            } elseif ($acao === 'recusar' && $eh_motorista && $sol['status'] === 'pendente') {
                $pdo->prepare("UPDATE solicitacoes
                                  SET status = 'recusada', data_resposta = NOW()
                                WHERE id = :sid")->execute([':sid' => $sid]);
                flash('sucesso', 'Solicitação recusada.');

            } elseif ($acao === 'cancelar' && $eh_passageiro && in_array($sol['status'], ['pendente','aceita'], true)) {
                // Se já tinha sido aceita, devolve a vaga
                if ($sol['status'] === 'aceita') {
                    $pdo->prepare("UPDATE caronas
                                      SET vagas_disponiveis = vagas_disponiveis + 1
                                    WHERE id = :cid")->execute([':cid' => $sol['carona_id']]);
                }
                $pdo->prepare("UPDATE solicitacoes
                                  SET status = 'cancelada', data_resposta = NOW()
                                WHERE id = :sid")->execute([':sid' => $sid]);
                flash('sucesso', 'Solicitação cancelada.');
            } else {
                throw new RuntimeException('Ação não permitida para esta solicitação.');
            }

            $pdo->commit();
        } catch (Throwable $ex) {
            $pdo->rollBack();
            flash('erro', $ex->getMessage());
        }
    }
    header("Location: solicitacoes.php?aba=$aba");
    exit;
}

// ----------------- Consultas das abas -----------------
$recebidas = [];
$enviadas  = [];

if ($aba === 'recebidas') {
    $stmt = $pdo->prepare("
        SELECT s.id, s.status, s.data_solicitacao,
               u.nome AS passageiro_nome,
               c.origem, c.destino, c.data_viagem, c.horario_saida
          FROM solicitacoes s
          JOIN caronas  c ON c.id = s.carona_id
          JOIN usuarios u ON u.id = s.passageiro_id
         WHERE c.motorista_id = :uid
         ORDER BY FIELD(s.status,'pendente','aceita','recusada','cancelada'),
                  s.data_solicitacao DESC
    ");
    $stmt->execute([':uid' => $uid]);
    $recebidas = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare("
        SELECT s.id, s.status, s.data_solicitacao,
               u.nome AS motorista_nome,
               c.origem, c.destino, c.data_viagem, c.horario_saida
          FROM solicitacoes s
          JOIN caronas  c ON c.id = s.carona_id
          JOIN usuarios u ON u.id = c.motorista_id
         WHERE s.passageiro_id = :uid
         ORDER BY FIELD(s.status,'pendente','aceita','recusada','cancelada'),
                  s.data_solicitacao DESC
    ");
    $stmt->execute([':uid' => $uid]);
    $enviadas = $stmt->fetchAll();
}

$titulo_pagina = 'Solicitações';
$pagina_ativa  = 'solicit';
include __DIR__ . '/../includes/header.php';
?>

<h1>Solicitações</h1>

<div class="abas">
    <a href="?aba=recebidas" class="<?= $aba === 'recebidas' ? 'ativa' : '' ?>">Recebidas (como motorista)</a>
    <a href="?aba=enviadas"  class="<?= $aba === 'enviadas'  ? 'ativa' : '' ?>">Enviadas (como passageiro)</a>
</div>

<?php if ($aba === 'recebidas'): ?>

    <?php if (empty($recebidas)): ?>
        <div class="vazio"><p>Você ainda não recebeu nenhuma solicitação.</p></div>
    <?php else: ?>
        <table class="tabela">
            <thead>
                <tr>
                    <th>Passageiro</th><th>Rota</th><th>Data / Horário</th>
                    <th>Solicitado em</th><th>Status</th><th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recebidas as $s): ?>
                <tr>
                    <td><strong><?= h($s['passageiro_nome']) ?></strong></td>
                    <td><?= h($s['origem']) ?> → <?= h($s['destino']) ?></td>
                    <td><?= h(data_br($s['data_viagem'])) ?> às <?= h(substr($s['horario_saida'], 0, 5)) ?></td>
                    <td><?= h(datetime_br($s['data_solicitacao'])) ?></td>
                    <td><span class="badge badge-<?= h($s['status']) ?>"><?= h(ucfirst($s['status'])) ?></span></td>
                    <td class="acoes-coluna">
                        <?php if ($s['status'] === 'pendente'): ?>
                            <a href="?aba=recebidas&acao=aceitar&sid=<?= (int)$s['id'] ?>"
                               class="btn btn-sucesso btn-sm" data-confirma="Aceitar esta solicitação?">Aceitar</a>
                            <a href="?aba=recebidas&acao=recusar&sid=<?= (int)$s['id'] ?>"
                               class="btn btn-perigo btn-sm" data-confirma="Recusar esta solicitação?">Recusar</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php else: ?>

    <?php if (empty($enviadas)): ?>
        <div class="vazio"><p>Você ainda não enviou nenhuma solicitação.</p></div>
    <?php else: ?>
        <table class="tabela">
            <thead>
                <tr>
                    <th>Motorista</th><th>Rota</th><th>Data / Horário</th>
                    <th>Solicitado em</th><th>Status</th><th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($enviadas as $s): ?>
                <tr>
                    <td><strong><?= h($s['motorista_nome']) ?></strong></td>
                    <td><?= h($s['origem']) ?> → <?= h($s['destino']) ?></td>
                    <td><?= h(data_br($s['data_viagem'])) ?> às <?= h(substr($s['horario_saida'], 0, 5)) ?></td>
                    <td><?= h(datetime_br($s['data_solicitacao'])) ?></td>
                    <td><span class="badge badge-<?= h($s['status']) ?>"><?= h(ucfirst($s['status'])) ?></span></td>
                    <td class="acoes-coluna">
                        <?php if (in_array($s['status'], ['pendente','aceita'], true)): ?>
                            <a href="?aba=enviadas&acao=cancelar&sid=<?= (int)$s['id'] ?>"
                               class="btn btn-perigo btn-sm" data-confirma="Cancelar sua solicitação?">Cancelar</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
