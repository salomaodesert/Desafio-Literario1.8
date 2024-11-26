<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "desafio_resumo";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE email = ? AND tipo = 'professor'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['tipo_usuario'] = 'professor';
            header("Location: pagina_professor.php");
            exit();
        } else {
            $erro = "Senha inválida.";
        }
    } else {
        $erro = "Usuário não encontrado.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Professor</title>
    <link rel="stylesheet" href="login_professor.css">
</head>
<body>
    <!-- Container de login -->
    <div class="login-container">
        <h1>Login - Professor</h1>
        
        <!-- Exibindo mensagem de erro se houver -->
        <?php if (!empty($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
        
        <!-- Formulário de login -->
        <form method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Entrar</button>
                <!-- Botão voltar -->
             <a href="inicio.php" class="btn-voltar">Voltar</a>
    
        </form>

        <!-- Links de opções adicionais -->
        <div class="links">
            <a href="inicio.php">Esqueci minha senha!</a>  
            <a href="cadastro_professor.php">Cadastrar-se!</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Sistema de Redação</p>
    </footer>
</body>
</html>
