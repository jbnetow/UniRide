<?php
/**
 * Tela 1: Login.
 *
 * Se já houver sessão ativa, redireciona direto para o dashboard.
 * Senão, processa o POST do formulário de login.
 */

session_start();
require_once __DIR__ . '/../includes/funcoes.php';

// Se já está logado, vai direto para o dashboard
if (!empty($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/conexao.php';

    $email = trim(strtolower(in('email')));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($email === '' || $senha === '') {
        $erro = 'Informe e-mail e senha.';
    } else {
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, ativo
                                 FROM usuarios
                                WHERE email = :email
                                LIMIT 1");
        $stmt->execute([':email' => $email]);
        $u = $stmt->fetch();

        if (!$u || !password_verify($senha, $u['senha'])) {
            $erro = 'E-mail ou senha incorretos.';
        } elseif ((int)$u['ativo'] !== 1) {
            $erro = 'Sua conta está inativa. Procure o administrador.';
        } else {
            // Login bem-sucedido — regenera o ID para evitar session fixation
            session_regenerate_id(true);
            $_SESSION['usuario_id']    = (int)$u['id'];
            $_SESSION['usuario_nome']  = $u['nome'];
            $_SESSION['usuario_email'] = $u['email'];

            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — UniRide</title>
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
        <h1>Login</h1>

        <?= exibir_flash() ?>

        <?php if ($erro): ?>
            <div class="mensagem mensagem-erro"><?= h($erro) ?></div>
        <?php endif; ?>

        <form id="form-login" method="POST" action="">
            <div class="form-grupo">
                <label for="email">E-mail institucional</label>
                <input type="email" id="email" name="email"
                       value="<?= h($_POST['email'] ?? '') ?>"
                       placeholder="seu.nome@instituicao.edu.br" required autofocus>
            </div>

            <div class="form-grupo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Sua senha" required>
            </div>

            <button type="submit" class="btn btn-primario">Entrar</button>

            <p class="btn-link-rodape">
                Não tem conta? <a href="cadastro.php">Cadastre-se</a>
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
