<?php
/**
 * Tela 5: Criar oferta de carona.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexao.php';

$uid = (int)$_SESSION['usuario_id'];
$erros = [];
$dados = [
    'origem' => '', 'destino' => '', 'data_viagem' => '', 'horario_saida' => '',
    'vagas' => '', 'valor' => '', 'veiculo_modelo' => '', 'veiculo_placa' => '',
    'veiculo_cor' => '', 'observacoes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($dados as $k => $_) {
        $dados[$k] = in($k);
    }

    if ($dados['origem'] === '' || $dados['destino'] === '') {
        $erros[] = 'Origem e destino são obrigatórios.';
    }
    if ($dados['data_viagem'] === '' || $dados['horario_saida'] === '') {
        $erros[] = 'Data e horário são obrigatórios.';
    } else {
        // Não permitir data no passado
        $hoje = date('Y-m-d');
        if ($dados['data_viagem'] < $hoje) {
            $erros[] = 'A data da viagem não pode ser no passado.';
        }
    }
    $vagas = (int)$dados['vagas'];
    if ($vagas < 1 || $vagas > 6) {
        $erros[] = 'Informe entre 1 e 6 vagas.';
    }
    $valor = (float)str_replace(',', '.', $dados['valor']);
    if ($valor < 0) {
        $erros[] = 'O valor por passageiro não pode ser negativo.';
    }
    if ($dados['veiculo_modelo'] === '' || $dados['veiculo_placa'] === '' || $dados['veiculo_cor'] === '') {
        $erros[] = 'Informe modelo, placa e cor do veículo.';
    }

    if (empty($erros)) {
        $stmt = $pdo->prepare("
            INSERT INTO caronas
                (motorista_id, origem, destino, data_viagem, horario_saida,
                 vagas_total, vagas_disponiveis, valor_por_passageiro,
                 veiculo_modelo, veiculo_placa, veiculo_cor, observacoes, status)
            VALUES
                (:uid, :origem, :destino, :data, :hora,
                 :vt, :vd, :valor,
                 :vm, :vp, :vc, :obs, 'ativa')
        ");
        $stmt->execute([
            ':uid'    => $uid,
            ':origem' => $dados['origem'],
            ':destino'=> $dados['destino'],
            ':data'   => $dados['data_viagem'],
            ':hora'   => $dados['horario_saida'],
            ':vt'     => $vagas,
            ':vd'     => $vagas,
            ':valor'  => $valor,
            ':vm'     => $dados['veiculo_modelo'],
            ':vp'     => $dados['veiculo_placa'],
            ':vc'     => $dados['veiculo_cor'],
            ':obs'    => $dados['observacoes'],
        ]);
        flash('sucesso', 'Carona publicada com sucesso!');
        header('Location: caronas_minhas.php');
        exit;
    }
}

$titulo_pagina = 'Oferecer carona';
$pagina_ativa  = 'criar';
include __DIR__ . '/../includes/header.php';
?>

<h1>Oferecer uma carona</h1>
<p style="color:#666; margin-bottom:30px;">Preencha os dados da sua viagem para que outros estudantes possam encontrá-la.</p>

<?php if (!empty($erros)): ?>
    <div class="mensagem mensagem-erro">
        <?php foreach ($erros as $e): ?>• <?= h($e) ?><br><?php endforeach; ?>
    </div>
<?php endif; ?>

<form id="form-carona" method="POST" action="">

    <h2>Rota</h2>
    <div class="form-linha">
        <div class="form-grupo">
            <label for="origem">Origem</label>
            <input type="text" id="origem" name="origem"
                   value="<?= h($dados['origem']) ?>"
                   placeholder="Ex.: Zona Leste - Itaquera" required>
        </div>
        <div class="form-grupo">
            <label for="destino">Destino</label>
            <input type="text" id="destino" name="destino"
                   value="<?= h($dados['destino']) ?>"
                   placeholder="Ex.: SENAC Santo Amaro" required>
        </div>
    </div>

    <h2>Quando</h2>
    <div class="form-linha">
        <div class="form-grupo">
            <label for="data_viagem">Data da viagem</label>
            <input type="date" id="data_viagem" name="data_viagem"
                   value="<?= h($dados['data_viagem']) ?>" required>
        </div>
        <div class="form-grupo">
            <label for="horario_saida">Horário de saída</label>
            <input type="time" id="horario_saida" name="horario_saida"
                   value="<?= h($dados['horario_saida']) ?>" required>
        </div>
    </div>

    <h2>Vagas e valor</h2>
    <div class="form-linha">
        <div class="form-grupo">
            <label for="vagas">Número de vagas</label>
            <input type="number" id="vagas" name="vagas" min="1" max="6"
                   value="<?= h($dados['vagas']) ?>" placeholder="Ex.: 3" required>
        </div>
        <div class="form-grupo">
            <label for="valor">Valor por passageiro (R$)</label>
            <input type="number" id="valor" name="valor" min="0" step="0.50"
                   value="<?= h($dados['valor']) ?>" placeholder="Ex.: 6.00" required>
        </div>
    </div>

    <h2>Veículo</h2>
    <div class="form-linha">
        <div class="form-grupo">
            <label for="veiculo_modelo">Modelo</label>
            <input type="text" id="veiculo_modelo" name="veiculo_modelo"
                   value="<?= h($dados['veiculo_modelo']) ?>"
                   placeholder="Ex.: VW Gol 2018" required>
        </div>
        <div class="form-grupo">
            <label for="veiculo_placa">Placa</label>
            <input type="text" id="veiculo_placa" name="veiculo_placa" data-mascara="placa"
                   value="<?= h($dados['veiculo_placa']) ?>"
                   placeholder="Ex.: ABC-1D23" maxlength="8" required>
        </div>
        <div class="form-grupo">
            <label for="veiculo_cor">Cor</label>
            <input type="text" id="veiculo_cor" name="veiculo_cor"
                   value="<?= h($dados['veiculo_cor']) ?>"
                   placeholder="Ex.: Prata" required>
        </div>
    </div>

    <div class="form-grupo">
        <label for="observacoes">Observações (opcional)</label>
        <textarea id="observacoes" name="observacoes"
                  placeholder="Ex.: Não fumante, aceita pet pequeno, saída pontual."><?= h($dados['observacoes']) ?></textarea>
    </div>

    <div style="display:flex; gap:10px; margin-top:10px;">
        <button type="submit" class="btn btn-primario" style="width:auto;">Publicar carona</button>
        <a href="dashboard.php" class="btn btn-secundario">Cancelar</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
