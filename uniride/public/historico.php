<?php
/**
 * Tela 10: Histórico de viagens.
 * Mostra:
 *   - Caronas concluídas/canceladas onde o usuário foi MOTORISTA
 *   - Caronas concluídas/canceladas onde o usuário foi PASSAGEIRO ACEITO
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexao.php';

$uid = (int)$_SESSION['usuario_id'];

$sql = "
    SELECT c.origem, c.destino, c.data_viagem, c.horario_saida,
           c.status, c.valor_por_passageiro,
           'motorista' AS papel
      FROM caronas c
     WHERE c.motorista_id = :uid1
       AND c.status IN ('concluida','cancelada')

    UNION ALL

    SELECT c.origem, c.destino, c.data_viagem, c.horario_saida,
           c.status, c.valor_por_passageiro,
           'passageiro' AS papel
      FROM caronas c
      JOIN solicitacoes s ON s.carona_id = c.id
     WHERE s.passageiro_id = :uid2
       AND s.status = 'aceita'
       AND c.status IN ('concluida','cancelada')

    ORDER BY data_viagem DESC, horario_saida DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':uid1' => $uid, ':uid2' => $uid]);
$historico = $stmt->fetchAll();

$titulo_pagina = 'Histórico';
$pagina_ativa  = 'historico';
include __DIR__ . '/../includes/header.php';
?>

<h1>Histórico de viagens</h1>
<p style="color:#666; margin-bottom:20px;">Veja todas as viagens já realizadas ou canceladas.</p>

<?php if (empty($historico)): ?>
    <div class="vazio"><p>Você ainda não tem viagens no histórico.</p></div>
<?php else: ?>
    <table class="tabela">
        <thead>
            <tr>
                <th>Data</th><th>Horário</th><th>Rota</th>
                <th>Papel</th><th>Valor</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($historico as $h): ?>
            <tr>
                <td><?= h(data_br($h['data_viagem'])) ?></td>
                <td><?= h(substr($h['horario_saida'], 0, 5)) ?></td>
                <td><?= h($h['origem']) ?> → <?= h($h['destino']) ?></td>
                <td><?= h(ucfirst($h['papel'])) ?></td>
                <td><?= h(moeda_br((float)$h['valor_por_passageiro'])) ?></td>
                <td><span class="badge badge-<?= h($h['status']) ?>"><?= h(ucfirst($h['status'])) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
