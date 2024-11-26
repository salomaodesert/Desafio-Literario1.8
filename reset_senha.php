<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Verificar se o email existe no banco
    $query = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch();

    if ($usuario) {
        // Gerar um token único
        $token = bin2hex(random_bytes(16));
        $query = "UPDATE usuarios SET reset_token = :token, reset_expira = NOW() + INTERVAL 1 HOUR WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Enviar o link de redefinição por email
        $link = "http://seusite.com/nova_senha.php?token=$token";
        mail($email, "Redefinição de senha", "Clique no link para redefinir sua senha: $link");

        echo "Um link de redefinição foi enviado para seu email.";
    } else {
        echo "Email não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetar Senha</title>
</head>
<body>
    <h1>Resetar Senha</h1>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Enviar</button>
    </form>
    <footer>
        <p>&copy; 2024 Sistema de Redação</p>
    </footer>
</body>
</html>
