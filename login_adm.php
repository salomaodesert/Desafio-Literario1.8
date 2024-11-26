<?php
ob_start(); // Inicia o buffer de saída
session_start([
    'cookie_lifetime' => 86400, // Sessão válida por 1 dia (86400 segundos)
    'cookie_httponly' => true,  // Impede o acesso ao cookie via JavaScript
    'cookie_secure' => isset($_SERVER['HTTPS']), // Só envia cookies via HTTPS, se disponível
    'cookie_samesite' => 'Strict' // Previne o envio do cookie em requisições cross-site
]);

// Credenciais predefinidas de administrador
$adminLoginPredefinido = 'admin';
$adminSenhaPredefinidaHash = password_hash('admin123', PASSWORD_DEFAULT);
$error = '';

// Gera o token CSRF se ainda não existir
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); // Gera um token de 32 caracteres
}

// Verifica se o formulário foi submetido via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificação CSRF
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token CSRF inválido. Tente novamente.";
    } else {
        $username = htmlspecialchars(trim($_POST['username']));
        $password = trim($_POST['password']);

        // Verificação se os campos estão vazios
        if (empty($username) || empty($password)) {
            $error = "Por favor, preencha todos os campos.";
        } else {
            // Validação do usuário e senha
            if ($username === $adminLoginPredefinido && password_verify($password, $adminSenhaPredefinidaHash)) {
                session_regenerate_id(); // Gera um novo ID de sessão para segurança
                $_SESSION['usuario_id'] = 0; // Pode-se ajustar para o ID real, se necessário
                $_SESSION['tipo'] = 'admin';
                session_write_close();

                // Redirecionamento correto para a página de administração
                header("Location: /pagina_adm.php");
                exit();
            } else {
                $error = "Usuário ou senha inválidos.";
            }
        }
    }
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="login_adm.css"> <!-- Vinculando o arquivo CSS -->
</head>
<body>
    <main class="container">
        <h1>Login Administrador</h1>
        <form id="login-form" method="POST" action="">
            <!-- Adicionando o campo CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="text" id="username" name="username" placeholder="Login" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            <input type="password" id="password" name="password" placeholder="Senha" required>
            <button type="submit" class="btn">Entrar</button>
            <button type="button" id="backButton" class="btn voltar">Voltar</button>
        </form>
        <?php if (!empty($error)): ?>
            <p id="feedback" style="color: red; display: block;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </main>
    <script src="login_adm.js"></script>
    <footer>
        <p>&copy; 2024 Sistema de Redação</p>
    </footer>
</body>
</html>
