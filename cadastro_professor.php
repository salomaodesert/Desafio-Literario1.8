<?php
session_start();

// Função para validar CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($i = 9; $i < 11; $i++) {
        $soma = 0;
        for ($j = 0; $j < $i; $j++) {
            $soma += $cpf[$j] * (($i + 1) - $j);
        }
        $soma = ((10 * $soma) % 11) % 10;
        if ($cpf[$i] != $soma) {
            return false;
        }
    }
    return true;
}

$erros = [];
$sucessoCadastro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmarSenha = trim($_POST['confirmar_senha']);
    $setor = trim($_POST['setor']);
    $cpf = trim($_POST['cpf']);
    $tipo = 'professor';

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha) || empty($setor) || empty($cpf)) {
        $erros[] = "Por favor, preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "E-mail inválido.";
    } elseif ($senha !== $confirmarSenha) {
        $erros[] = "As senhas não coincidem.";
    } elseif (!validarCPF($cpf)) {
        $erros[] = "CPF inválido.";
    } else {
        include 'db.php';

        // Verifica se o e-mail ou CPF já existe
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM (
                SELECT email, cpf FROM solicitacoes_professor
                UNION ALL
                SELECT email, cpf FROM usuarios
            ) AS combined
            WHERE email = ? OR cpf = ?
        ");
        $stmt->execute([$email, $cpf]);
        $registroExistente = $stmt->fetchColumn();

        if ($registroExistente) {
            $erros[] = "O e-mail ou CPF já está cadastrado.";
        } else {
            // Insere a solicitação na tabela `solicitacoes_professor` para aprovação
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO solicitacoes_professor (nome, email, senha, cpf, data_solicitacao) VALUES (?, ?, ?, ?, NOW())");
                $hashedPassword = password_hash($senha, PASSWORD_DEFAULT);

                if ($stmt->execute([$nome, $email, $hashedPassword, $cpf])) {
                    $sucessoCadastro = "Cadastro enviado para aprovação!";
                    $pdo->commit();
                } else {
                    $pdo->rollBack();
                    $erros[] = "Erro ao enviar a solicitação de cadastro.";
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $erros[] = "Erro ao enviar a solicitação de cadastro: " . $e->getMessage();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Professor</title>
    <link rel="stylesheet" href="cadastro_professsor.css">
</head>
<body>
    <!-- Exibição das mensagens de erro e sucesso -->
    <div class="notification-container">
        <?php if (!empty($erros)): ?>
            <?php foreach ($erros as $erro): ?>
                <div class="notification error"><?= htmlspecialchars($erro) ?></div>
            <?php endforeach; ?>
        <?php elseif (!empty($sucessoCadastro)): ?>
            <div class="notification success"><?= htmlspecialchars($sucessoCadastro) ?></div>
        <?php endif; ?>
    </div>

    <!-- Formulário de cadastro -->
    <div class="back-link">
    <link rel="stylesheet" href="cadastro_professor.css"> <!-- Link para o CSS -->
    </div>

    <section class="hero">
        <div class="content">
            <h1>Cadastro de Professor</h1>
            <p>Preencha os dados abaixo para se cadastrar no sistema.</p>

<!-- Formulário de cadastro -->
<form id="cadastro-form" method="POST" action="">
    <div class="input-group">
        <input type="text" name="nome" placeholder="Nome Completo" required>
    </div>

    <div class="input-group">
        <input type="email" name="email" placeholder="Email" required>
    </div>

    <div class="input-group">
        <input type="password" name="senha" placeholder="Senha" required>
    </div>

    <div class="input-group">
        <input type="password" name="confirmar_senha" placeholder="Confirme a Senha" required>
    </div>

    <div class="input-group">
        <input type="text" name="setor" placeholder="Setor" required>
    </div>

    <div class="input-group">
        <input type="text" name="cpf" placeholder="CPF" required>
    </div>

    <!-- Botão de cadastro -->
    <button type="submit" class="btn" id="cadastrar-btn">Cadastrar</button>

    <!-- Botão voltar -->
    <a href="inicio.php" class="back-btn">Voltar</a>
</form>

        </div>
    </section>

    <script src="cadastro_professor.js"></script>
    <footer>
        <p>&copy; 2024 Sistema de Redação</p>
    </footer>
</body>
</html>
