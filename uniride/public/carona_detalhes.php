<?php
/**
 * Tela 8: Detalhes da carona.
 * Mostra dados completos da carona e do motorista.
 * Permite solicitar uma vaga (cria registro em 'solicitacoes').
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexao.php';

$uid       = (int)$_SESSION['usuario_id'];
$carona_id = (int)($_GET['id'] ?? $_POST['carona_id'] ?? 0);

if ($carona_id <= 0) {
    flash('erro', 'Carona inválida.');
    header('Location: caronas_buscar.php');
    exit;
}

// Busca dados da carona + motorista
$stmt = $pdo->prepare("
    SELECT c.*,
           u.nome AS motorista_nome, u.instituicao AS motorista_inst,
           u.curso AS motorista_curso, u.telefone AS motorista_tel
      FROM caronas c
      JOIN usuarios u ON u.id = c.motorista_id
     WHERE c.id = :id
     LIMIT 1
");
$stmt->execute([':id' => $carona_id]);
$carona = $stmt->fetch();

if (!$carona) {
    flash('erro', 'Carona não encontrada.');
    header('Location: caronas_buscar.php');
    exit;
}

// Verifica se o usuário já solicitou esta carona
$stmt = $pdo->prepare("SELECT id, status FROM solicitacoes
                        WHERE carona_id = :cid AND passageiro_id = :uid LIMIT 1");
$stmt->execute([':cid' => $carona_id, ':uid' => $uid]);
$sol_existente = $stmt->fetch();

// POST: Solicitar vaga
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ((int)$carona['motorista_id'] === $uid) {
        flash('erro', 'Você não pode solicitar a própria carona.');
    } elseif ($carona['status'] !== 'ativa') {
        flash('erro', 'Esta carona não está mais ativa.');
    } elseif ((int)$carona['vagas_disponiveis'] <= 0) {
        flash('erro', 'Não há mais vagas disponíveis.');
    } elseif ($sol_existente) {
        flash('aviso', 'Você já tem uma solicitação para esta carona.');
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO solicitacoes (carona_id, passageiro_id, status)
            VALUES (:cid, :uid, 'pendente')
        ");
        $stmt->execute([':cid' => $carona_id, ':uid' => $uid]);
        flash('sucesso', 'Solicitação enviada! Aguarde a resposta do motorista.');
        header('Location: solicitacoes.php?aba=enviadas');
        exit;
    }
    header("Location: carona_detalhes.php?id=$carona_id");
    exit;
}

$titulo_pagina = 'Detalhes da carona';
$pagina_ativa  = 'buscar';
include __DIR__ . '/../includes/header.php';
?>

<p style="margin-bottom:18px;"><a href="caronas_buscar.php">&larr; Voltar para a busca</a></p>

<h1>Detalhes da carona</h1>

<div class="detalhes-carona">
    <div class="rota-grande">
        <?= h($carona['origem']) ?>
        <span style="color:#0066FF;"> → </span>
        <?= h($carona['destino']) ?>
        <span class="badge badge-<?= h($carona['status']) ?>"><?= h(ucfirst($carona['status'])) ?></span>
    </div>

    <div class="grid-info">
        <div class="info"><strong>Data</strong><?= h(data_br($carona['data_viagem'])) ?></div>
        <div class="info"><strong>Horário de saída</strong><?= h(substr($carona['horario_saida'], 0, 5)) ?></div>
        <div class="info"><strong>Vagas disponíveis</strong><?= (int)$carona['vagas_disponiveis'] ?> de <?= (int)$carona['vagas_total'] ?></div>
        <div class="info"><strong>Valor por passageiro</strong><?= h(moeda_br((float)$carona['valor_por_passageiro'])) ?></div>
        <div class="info"><strong>Veículo</strong><?= h($carona['veiculo_modelo']) ?> — <?= h($carona['veiculo_cor']) ?></div>
        <div class="info"><strong>Placa</strong><?= h($carona['veiculo_placa']) ?></div>
    </div>

    <?php if (!empty($carona['observacoes'])): ?>
        <div class="info" style="margin-top:8px;">
            <strong style="display:block; color:#666; font-size:0.85rem; margin-bottom:4px;">Observações do motorista</strong>
            <?= nl2br(h($carona['observacoes'])) ?>
        </div>
    <?php endif; ?>
</div>

<div class="box-motorista">
    <h3>Motorista</h3>
    <p><strong><?= h($carona['motorista_nome']) ?></strong></p>
    <p style="color:#666; font-size:0.95rem;">
        <?= h($carona['motorista_curso']) ?> — <?= h($carona['motorista_inst']) ?>
    </p>
    <p style="color:#666; font-size:0.95rem;">Contato: <?= h($carona['motorista_tel']) ?></p>
</div>

<?php if ((int)$carona['motorista_id'] === $uid): ?>
    <div class="mensagem mensagem-aviso">Esta é a sua própria carona — você não pode solicitá-la.</div>
    <a href="caronas_minhas.php" class="btn btn-secundario">Ver em minhas caronas</a>

<?php elseif ($sol_existente): ?>
    <div class="mensagem mensagem-aviso">
        Você já tem uma solicitação para esta carona com status:
        <strong><?= h(ucfirst($sol_existente['status'])) ?></strong>.
    </div>
    <a href="solicitacoes.php?aba=enviadas" class="btn btn-secundario">Ver minhas solicitações</a>

<?php elseif ($carona['status'] !== 'ativa'): ?>
    <div class="mensagem mensagem-erro">Esta carona não está mais ativa.</div>
    <a href="caronas_buscar.php" class="btn btn-secundario">Voltar à busca</a>

<?php elseif ((int)$carona['vagas_disponiveis'] <= 0): ?>
    <div class="mensagem mensagem-erro">Sem vagas disponíveis.</div>
    <a href="caronas_buscar.php" class="btn btn-secundario">Voltar à busca</a>

<?php else: ?>
    <form method="POST" action="">
        <input type="hidden" name="carona_id" value="<?= (int)$carona['id'] ?>">
        <button type="submit" class="btn btn-primario" style="width:auto;"
                data-confirma="Confirmar a solicitação desta carona?">
            Solicitar esta carona
        </button>
        <a href="caronas_buscar.php" class="btn btn-secundario">Voltar</a>
    </form>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
