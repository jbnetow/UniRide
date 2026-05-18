<?php
/**
 * Tela 7: Buscar carona (passageiro).
 * SELECT real com filtros opcionais: origem, destino, data.
 * Esconde caronas do próprio usuário e mostra apenas ativas com vaga.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexao.php';

$uid = (int)$_SESSION['usuario_id'];

$f_origem  = in('origem');
$f_destino = in('destino');
$f_data    = in('data_viagem');

$where  = [
    "c.motorista_id <> :uid",
    "c.status = 'ativa'",
    "c.vagas_disponiveis > 0",
    "c.data_viagem >= CURDATE()",
];
$params = [':uid' => $uid];

if ($f_origem !== '') {
    $where[] = "c.origem LIKE :origem";
    $params[':origem'] = "%$f_origem%";
}
if ($f_destino !== '') {
    $where[] = "c.destino LIKE :destino";
    $params[':destino'] = "%$f_destino%";
}
if ($f_data !== '') {
    $where[] = "c.data_viagem = :data";
    $params[':data'] = $f_data;
}

$sql = "SELECT c.*, u.nome AS motorista_nome
          FROM caronas c
          JOIN usuarios u ON u.id = c.motorista_id
         WHERE " . implode(' AND ', $where) . "
         ORDER BY c.data_viagem ASC, c.horario_saida ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$caronas_disponiveis = $stmt->fetchAll();

$titulo_pagina = 'Buscar carona';
$pagina_ativa  = 'buscar';
include __DIR__ . '/../includes/header.php';
?>

<h1>Buscar carona</h1>
<p style="color:#666; margin-bottom:20px;">Encontre uma carona disponível com colegas da sua instituição.</p>

<form method="GET" action="" class="filtros-busca">
    <div class="form-linha">
        <div class="form-grupo">
            <label for="f_origem">Origem</label>
            <input type="text" id="f_origem" name="origem"
                   value="<?= h($f_origem) ?>" placeholder="Qualquer origem">
        </div>
        <div class="form-grupo">
            <label for="f_destino">Destino</label>
            <input type="text" id="f_destino" name="destino"
                   value="<?= h($f_destino) ?>" placeholder="Qualquer destino">
        </div>
        <div class="form-grupo">
            <label for="f_data">Data</label>
            <input type="date" id="f_data" name="data_viagem"
                   value="<?= h($f_data) ?>">
        </div>
        <div class="form-grupo">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-primario">Filtrar</button>
        </div>
    </div>
</form>

<h2><?= count($caronas_disponiveis) ?> carona(s) encontrada(s)</h2>

<?php if (empty($caronas_disponiveis)): ?>
    <div class="vazio">
        <p>Nenhuma carona disponível com os filtros atuais.</p>
    </div>
<?php else: ?>
    <div class="lista-caronas">
        <?php foreach ($caronas_disponiveis as $c): ?>
            <div class="card-carona">
                <div class="rota">
                    <?= h($c['origem']) ?>
                    <span class="seta">→</span>
                    <?= h($c['destino']) ?>
                </div>
                <div class="info-linha">
                    <span class="item">🧑 <?= h($c['motorista_nome']) ?></span>
                    <span class="item">📅 <?= h(data_br($c['data_viagem'])) ?></span>
                    <span class="item">🕐 <?= h(substr($c['horario_saida'], 0, 5)) ?></span>
                    <span class="item">👥 <?= (int)$c['vagas_disponiveis'] ?> vaga(s)</span>
                    <span class="item">💰 <?= h(moeda_br((float)$c['valor_por_passageiro'])) ?></span>
                </div>
                <div class="acoes">
                    <a href="carona_detalhes.php?id=<?= (int)$c['id'] ?>"
                       class="btn btn-primario btn-sm" style="width:auto;">Ver detalhes</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
