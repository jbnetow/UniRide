<?php
/**
 * Tela 4: Perfil do usuário.
 * Mostra os dados atuais e permite atualizá-los (exceto e-mail).
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexao.php';

$uid = (int)$_SESSION['usuario_id'];
$erros = [];

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = in('nome');
    $cpf         = in('cpf');
    $telefone    = in('telefone');
    $instituicao = in('instituicao');
    $curso       = in('curso');

    if ($nome === '' || mb_strlen($nome) < 3) {
        $erros[] = 'Informe seu nome completo.';
    }
    if (strlen(so_numeros($cpf)) !== 11) {
        $erros[] = 'CPF inválido.';
    }
    if (strlen(so_numeros($telefone)) < 10) {
        $erros[] = 'Telefone inválido.';
    }
    if ($instituicao === '' || $curso === '') {
        $erros[] = 'Instituição e curso são obrigatórios.';
    }

    if (empty($erros)) {
        $stmt = $pdo->prepare("
            UPDATE usuarios
               SET nome = :nome, cpf = :cpf, telefone = :telefone,
                   instituicao = :inst, curso = :curso
             WHERE id = :uid
        ");
        $stmt->execute([
            ':nome' => $nome, ':cpf' => $cpf, ':telefone' => $telefone,
            ':inst' => $instituicao, ':curso' => $curso, ':uid' => $uid,
        ]);
        // Atualiza nome na sessão para refletir no header
        $_SESSION['usuario_nome'] = $nome;

        flash('sucesso', 'Perfil atualizado com sucesso.');
        header('Location: perfil.php');
        exit;
    }
}

// SELECT
$stmt = $pdo->prepare("SELECT nome, cpf, telefone, email, instituicao, curso
                         FROM usuarios WHERE id = :uid LIMIT 1");
$stmt->execute([':uid' => $uid]);
$usuario = $stmt->fetch();

if (!$usuario) {
    // Sessão fantasma: força logout
    header('Location: logout.php');
    exit;
}

$titulo_pagina = 'Meu perfil';
$pagina_ativa  = 'perfil';
include __DIR__ . '/../includes/header.php';
?>

<h1>Meu perfil</h1>
<p style="color:#666; margin-bottom:30px;">Atualize seus dados pessoais. O e-mail institucional não pode ser alterado.</p>

<?php if (!empty($erros)): ?>
    <div class="mensagem mensagem-erro">
        <?php foreach ($erros as $e): ?>• <?= h($e) ?><br><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST" action="">

    <div class="form-grupo">
        <label for="nome">Nome completo</label>
        <input type="text" id="nome" name="nome" value="<?= h($usuario['nome']) ?>" required>
    </div>

    <div class="form-linha">
        <div class="form-grupo">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" data-mascara="cpf"
                   value="<?= h($usuario['cpf']) ?>" maxlength="14" required>
        </div>
        <div class="form-grupo">
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" data-mascara="telefone"
                   value="<?= h($usuario['telefone']) ?>" required>
        </div>
    </div>

    <div class="form-grupo">
        <label for="email">E-mail institucional</label>
        <input type="email" id="email" name="email" value="<?= h($usuario['email']) ?>" disabled>
    </div>

    <div class="form-linha">
        <div class="form-grupo">
            <label for="instituicao">Instituição</label>
            <input type="text" id="instituicao" name="instituicao"
                   value="<?= h($usuario['instituicao']) ?>" required>
        </div>
        <div class="form-grupo">
            <label for="curso">Curso</label>
            <input type="text" id="curso" name="curso"
                   value="<?= h($usuario['curso']) ?>" required>
        </div>
    </div>

    <div style="display:flex; gap:10px; margin-top:10px;">
        <button type="submit" class="btn btn-primario" style="width:auto;">Salvar alterações</button>
        <a href="dashboard.php" class="btn btn-secundario">Cancelar</a>
    </div>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
