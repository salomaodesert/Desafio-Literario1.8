<?php
session_start();
require 'db.php'; // Conexão com o banco de dados

// Verifica se o usuário está logado e é um aluno
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'aluno') {
    header('Location: login_usuario.php');
    exit();
}

// Lista os temas disponíveis
$temas = $pdo->query("SELECT * FROM temas")->fetchAll();

// Processa o envio da redação (caso a redação seja nova)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_redacao'])) {
    $tema_id = $_POST['tema_id'];
    $redacao = $_POST['redacao'];
    $aluno_id = $_SESSION['usuario_id'];

    $stmt = $pdo->prepare("INSERT INTO redacoes (aluno_id, tema_id, redacao) VALUES (?, ?, ?)");
    if ($stmt->execute([$aluno_id, $tema_id, $redacao])) {
        $mensagem = "Redação enviada com sucesso!";
    } else {
        $mensagem = "Erro ao enviar a redação.";
    }
}

// Consulta a última redação enviada pelo aluno
$stmt = $pdo->prepare("SELECT * FROM redacoes WHERE aluno_id = ? ORDER BY data_envio DESC LIMIT 1");
$stmt->execute([$_SESSION['usuario_id']]);
$redacao_enviada = $stmt->fetch();

// Obtém o tema da última redação, se houver
if ($redacao_enviada) {
    $tema_id = $redacao_enviada['tema_id'];
    $stmt_tema = $pdo->prepare("SELECT tema FROM temas WHERE id = ?");
    $stmt_tema->execute([$tema_id]);
    $tema = $stmt_tema->fetch();
    $tema_selecionado = $tema['tema'];
    $redacao_conteudo = $redacao_enviada['redacao']; // Certifique-se de que este campo contém o texto da redação
} else {
    $tema_selecionado = "";
    $redacao_conteudo = "";
}

// Processa a edição da redação (caso o formulário de edição seja enviado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_redacao'])) {
    $novo_tema_id = $_POST['tema_id'];
    $nova_redacao = $_POST['redacao'];

    // Atualiza a redação no banco de dados
    $stmt = $pdo->prepare("UPDATE redacoes SET tema_id = ?, redacao = ? WHERE id = ?");
    if ($stmt->execute([$novo_tema_id, $nova_redacao, $redacao_enviada['id']])) {
        $mensagem = "Redação editada com sucesso!";
        // Atualiza a variável de conteúdo com a nova redação
        $redacao_conteudo = $nova_redacao;
    } else {
        $mensagem = "Erro ao editar a redação.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Aluno</title>
    <link rel="stylesheet" href="pagina_aluno.css">
</head>

<body>
<header>
    <div class="container">
    <header>
        <h1>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</h1>
    </header>
        <?php if (isset($mensagem)): ?>
            <p><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="tema_id">Selecione um tema:</label>
            <select id="tema_id" name="tema_id" required>
                <?php foreach ($temas as $tema): ?>
                    <option value="<?= $tema['id'] ?>" <?= ($tema['tema'] === $tema_selecionado) ? 'selected' : '' ?>><?= htmlspecialchars($tema['tema']) ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <label for="redacao">Escreva sua redação:</label>
            <textarea id="redacao" name="redacao" rows="10" cols="50" required><?= htmlspecialchars($redacao_conteudo) ?></textarea>
            <br>
            <button type="submit" name="enviar_redacao">Enviar Redação</button>
        </form>

        <?php if ($redacao_enviada): ?>
            <h2>Editar Sua última redação enviada:</h2>
            <form method="POST">
                <textarea rows="10" cols="50" name="redacao" required><?= htmlspecialchars($redacao_conteudo) ?></textarea>
                <br>
                <label for="tema_id">Selecione o tema (caso queira mudar):</label>
                <select id="tema_id" name="tema_id" required>
                    <?php foreach ($temas as $tema): ?>
                        <option value="<?= $tema['id'] ?>" <?= ($tema['id'] === $tema_id) ? 'selected' : '' ?>><?= htmlspecialchars($tema['tema']) ?></option>
                    <?php endforeach; ?>
                </select>
                <br>
                <button type="submit" name="editar_redacao">Salvar Alterações</button>
            </form>
            <br>
            <p>Você enviou este texto sobre o tema: <?= htmlspecialchars($tema_selecionado) ?></p>
        <?php endif; ?>

        <a href="logout.php" class="logout-btn">Sair</a> <!-- Alterado para usar a classe "logout-btn" -->
        <footer>
            <p>&copy; 2024 Sistema de Redação</p>
        </footer>
    </div>
</body>
</html>
