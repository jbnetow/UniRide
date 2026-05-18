<?php
/**
 * Tela 2: Cadastro de usuário.
 * Valida no servidor, insere no banco com senha em hash bcrypt.
 */

session_start();
require_once __DIR__ . '/../includes/funcoes.php';

// Se já está logado, manda para o dashboard
if (!empty($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erros = [];
$dados = [
    'nome' => '', 'cpf' => '', 'telefone' => '', 'email' => '',
    'instituicao' => '', 'curso' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/conexao.php';

    $dados['nome']        = in('nome');
    $dados['cpf']         = in('cpf');
    $dados['telefone']    = in('telefone');
    $dados['email']       = strtolower(in('email'));
    $dados['instituicao'] = in('instituicao');
    $dados['curso']       = in('curso');
    $senha           = (string)($_POST['senha'] ?? '');
    $senha_confirma  = (string)($_POST['senha_confirma'] ?? '');

    // Validações
    if ($dados['nome'] === '' || mb_strlen($dados['nome']) < 3) {
        $erros[] = 'Informe seu nome completo.';
    }
    if (strlen(so_numeros($dados['cpf'])) !== 11) {
        $erros[] = 'CPF inválido. Use o formato 000.000.000-00.';
    }
    if (strlen(so_numeros($dados['telefone'])) < 10) {
        $erros[] = 'Telefone inválido.';
    }
    if (!email_institucional_valido($dados['email'])) {
        $erros[] = 'O e-mail deve ser institucional (terminar com .edu.br).';
    }
    if ($dados['instituicao'] === '') {
        $erros[] = 'Informe a instituição de ensino.';
    }
    if ($dados['curso'] === '') {
        $erros[] = 'Informe o curso.';
    }
    if (strlen($senha) < 6) {
        $erros[] = 'A senha deve ter no mínimo 6 caracteres.';
    }
    if ($senha !== $senha_confirma) {
        $erros[] = 'A confirmação de senha não coincide.';
    }

    // Verifica se já existe e-mail
    if (empty($erros)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $dados['email']]);
        if ($stmt->fetch()) {
            $erros[] = 'Este e-mail já está cadastrado.';
        }
    }

    // Insere
    if (empty($erros)) {
        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, cpf, telefone, instituicao, curso)
            VALUES (:nome, :email, :senha, :cpf, :telefone, :instituicao, :curso)
        ");
        $stmt->execute([
            ':nome'        => $dados['nome'],
            ':email'       => $dados['email'],
            ':senha'       => $hash,
            ':cpf'         => $dados['cpf'],
            ':telefone'    => $dados['telefone'],
            ':instituicao' => $dados['instituicao'],
            ':curso'       => $dados['curso'],
        ]);

        flash('sucesso', 'Cadastro realizado com sucesso! Faça login para continuar.');
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro — UniRide</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <div class="logo">Uni<span>Ride</span></div>
    </div>
</header>

<main>
    <div class="form-centralizado">
        <h1>Cadastro</h1>

        <?php if (!empty($erros)): ?>
            <div class="mensagem mensagem-erro">
                <?php foreach ($erros as $e): ?>
                    • <?= h($e) ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form id="form-cadastro" method="POST" action="">
            <div class="form-grupo">
                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome"
                       value="<?= h($dados['nome']) ?>" placeholder="Nome completo" required>
            </div>

            <div class="form-grupo">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" data-mascara="cpf"
                       value="<?= h($dados['cpf']) ?>"
                       placeholder="000.000.000-00" maxlength="14" required>
            </div>

            <div class="form-grupo">
                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" data-mascara="telefone"
                       value="<?= h($dados['telefone']) ?>"
                       placeholder="(00) 00000-0000" required>
            </div>

            <div class="form-grupo">
                <label for="email">E-mail institucional</label>
                <input type="email" id="email" name="email"
                       value="<?= h($dados['email']) ?>"
                       placeholder="seu.nome@instituicao.edu.br" required>
            </div>

            <div class="form-grupo">
                <label for="instituicao">Instituição de ensino</label>
                <input type="text" id="instituicao" name="instituicao"
                       value="<?= h($dados['instituicao']) ?>"
                       placeholder="Ex.: SENAC" required>
            </div>

            <div class="form-grupo">
                <label for="curso">Curso</label>
                <input type="text" id="curso" name="curso"
                       value="<?= h($dados['curso']) ?>"
                       placeholder="Ex.: Análise e Desenvolvimento de Sistemas" required>
            </div>

            <div class="form-grupo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha"
                       placeholder="Mínimo 6 caracteres" required minlength="6">
            </div>

            <div class="form-grupo">
                <label for="senha_confirma">Confirmar senha</label>
                <input type="password" id="senha_confirma" name="senha_confirma"
                       placeholder="Repita a senha" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primario">Cadastrar</button>

            <p class="btn-link-rodape">
                Já tem conta? <a href="index.php">Faça login</a>
            </p>
        </form>
    </div>
</main>

<footer class="site-footer">
    UniRide &copy; 2026 — Projeto Integrador SENAC.
</footer>

<script src="../assets/js/script.js"></script>
</body>
</html>
