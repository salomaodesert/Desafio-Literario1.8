<?php
session_start();
require 'db.php'; // Conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $mensagemErro = 'Por favor, preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("SELECT id, nome, senha, tipo FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Armazena os dados do usuário na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['tipo_usuario'] = $usuario['tipo'];

            // Redireciona de acordo com o tipo de usuário
            if ($usuario['tipo'] === 'aluno') {
                header('Location: pagina_aluno.php');
            } else {
                header('Location: acesso_negado.php');
            }
            exit();
        } else {
            $mensagemErro = 'Email ou senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Aluno</title>
    <link rel="stylesheet" href="login_aluno.css">
</head>
<body>
    <main class="container">
        <form method="POST">
            <h1>Login - Aluno</h1>
            <?php if (!empty($mensagemErro)): ?>
                <p style="color: red;"><?= htmlspecialchars($mensagemErro) ?></p>
            <?php endif; ?>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
            
            <button type="submit">Entrar</button>
            
            <a href="inicio.php" class="btn-voltar">Voltar</a>
        </form>
    </main>

    <footer>
        <p>&copy; 2024 Sistema de Redação</p>
    </footer>
</body>
</html>
