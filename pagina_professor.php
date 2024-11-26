<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: login_professor.php");
    exit();
}

require 'db.php'; // Conexão com PDO

$mensagem = "";

// Função para validar CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/\D/', '', $cpf); // Remove caracteres não numéricos
    if (strlen($cpf) != 11) {
        return false;
    }
    // Validação dos dígitos verificadores
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $digito1 = $resto < 2 ? 0 : 11 - $resto;

    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $digito2 = $resto < 2 ? 0 : 11 - $resto;
    return $cpf[9] == $digito1 && $cpf[10] == $digito2;
}

// Função para validar e-mail
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para verificar duplicidade de CPF ou e-mail
function verificarDuplicidade($pdo, $campo, $valor) {
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE $campo = :valor");
    $stmt->bindParam(':valor', $valor);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}


// Processar ações do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cadastrar aluno
    if ($_POST['acao'] === 'cadastrar_aluno') {
        $nome = trim($_POST['login_aluno']);
        $senha = $_POST['senha_aluno'];
        $senha_confirmada = $_POST['senha_confirmada'];
        $turma = trim($_POST['turma']);
        $cpf = preg_replace('/\D/', '', $_POST['cpf']);
        $email = trim($_POST['email']);

        // Validações de formulário
        if ($senha !== $senha_confirmada) {
            $mensagem = "As senhas não coincidem!";
        } elseif (!validarCPF($cpf)) {
            $mensagem = "CPF inválido!";
        } elseif (!validarEmail($email)) {
            $mensagem = "E-mail inválido!";
        } elseif (verificarDuplicidade($pdo, 'cpf', $cpf)) {
            $mensagem = "CPF já cadastrado!";
        } elseif (verificarDuplicidade($pdo, 'email', $email)) {
            $mensagem = "E-mail já cadastrado!";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, senha, tipo, turma, cpf, email) VALUES (:nome, :senha, 'aluno', :turma, :cpf, :email)");
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':senha', $senha_hash);
            $stmt->bindParam(':turma', $turma);
            $stmt->bindParam(':cpf', $cpf);
            $stmt->bindParam(':email', $email);

            $mensagem = $stmt->execute() ? "Aluno cadastrado com sucesso!" : "Erro ao cadastrar aluno.";
        }
    }

    // Excluir aluno
    if ($_POST['acao'] === 'apagar_aluno') {
        $aluno_id = $_POST['aluno_id'];
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :aluno_id");
        $stmt->bindParam(':aluno_id', $aluno_id, PDO::PARAM_INT);
        $mensagem = $stmt->execute() ? "Aluno excluído com sucesso!" : "Erro ao excluir aluno.";
    }

    // Cadastrar tema
    if ($_POST['acao'] === 'cadastrar_tema') {
        $tema = trim($_POST['tema']);
        $stmt = $pdo->prepare("INSERT INTO temas (tema, professor_id) VALUES (:tema, :professor_id)");
        $stmt->bindParam(':tema', $tema);
        $stmt->bindParam(':professor_id', $_SESSION['usuario_id']);
        $mensagem = $stmt->execute() ? "Tema cadastrado com sucesso!" : "Erro ao cadastrar tema.";
    }

    // Avaliar redação
    if ($_POST['acao'] === 'avaliar_redacao') {
        $redacao_id = $_POST['redacao_id'];
        $nota = intval($_POST['nota']);
        $criterios = trim($_POST['criterios']);

        if ($nota < 0 || $nota > 10) {
            $mensagem = "Nota inválida! Deve ser um número entre 0 e 10.";
        } else {
            $stmt = $pdo->prepare("UPDATE redacoes SET nota = :nota, criterios = :criterios, data_correcao = NOW() WHERE id = :redacao_id");
            $stmt->bindParam(':nota', $nota);
            $stmt->bindParam(':criterios', $criterios);
            $stmt->bindParam(':redacao_id', $redacao_id);
            $mensagem = $stmt->execute() ? "Redação avaliada com sucesso!" : "Erro ao avaliar a redação.";
        }
    }
    if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'professor') {
        header("Location: login_professor.php");
        exit();
    }
    
    // Editar redação
    if ($_POST['acao'] === 'editar_redacao') {
        $redacao_id = $_POST['redacao_id'];
        $nova_redacao = $_POST['nova_redacao'] ?? '';
        $nova_nota = $_POST['nova_nota'] ?? '';
        $novos_criterios = $_POST['novos_criterios'] ?? '';

        // Verificações
        if (empty($nova_redacao)) {
            $mensagem = "A redação não pode estar vazia!";
        } elseif (!is_numeric($nova_nota) || $nova_nota < 0 || $nova_nota > 10) {
            $mensagem = "Nota inválida! Deve ser um número entre 0 e 10.";
        } else {
            $stmt = $pdo->prepare("
                UPDATE redacoes
                SET redacao = :redacao, nota = :nota, criterios = :criterios, data_correcao = NOW()
                WHERE id = :redacao_id
            ");
            $stmt->bindParam(':redacao', $nova_redacao);
            $stmt->bindParam(':nota', $nova_nota);
            $stmt->bindParam(':criterios', $novos_criterios);
            $stmt->bindParam(':redacao_id', $redacao_id);

            $mensagem = $stmt->execute() ? "Redação editada com sucesso!" : "Erro ao editar a redação.";
        }
    }
}
// Verificar se a ação é "enviar_resposta"
if (isset($_POST['acao']) && $_POST['acao'] === 'enviar_resposta') {
    // Pega o nome do remetente diretamente do formulário
    $nome_remetente = trim($_POST['nome_remetente']);
    $mensagem_resposta = trim($_POST['mensagem_resposta']);
    
    // Pega o destinatário e a resposta a ser enviada
    $destinatario_id = intval($_POST['destinatario_id']);
    $resposta_id = intval($_POST['resposta_id']);
    
    if (empty($mensagem_resposta) || empty($nome_remetente)) {
        $mensagem = "A mensagem de resposta e o nome do remetente não podem estar vazios!";
    } else {
        // Prepara a inserção na tabela de mensagens
        $stmt = $pdo->prepare("
            INSERT INTO mensagens (mensagem, remetente_nome, destinatario_id, data_envio, resposta_id)
            VALUES (:mensagem, :remetente_nome, :destinatario_id, NOW(), :resposta_id)
        ");
        
        $stmt->bindParam(':mensagem', $mensagem_resposta);
        $stmt->bindParam(':remetente_nome', $nome_remetente);
        $stmt->bindParam(':destinatario_id', $destinatario_id);
        $stmt->bindParam(':resposta_id', $resposta_id);

        // Executa a consulta e verifica se a resposta foi enviada com sucesso
        $mensagem = $stmt->execute() ? "Resposta enviada com sucesso!" : "Erro ao enviar a resposta.";
    }
}

// Buscar mensagens enviadas para o professor logado
$stmtMensagens = $pdo->prepare("
    SELECT m.mensagem, m.data_envio, u.nome AS remetente
    FROM mensagens m
    INNER JOIN usuarios u ON m.remetente_id = u.id
    WHERE m.destinatario_id = :professor_id
    ORDER BY m.data_envio DESC
");
$stmtMensagens->bindParam(':professor_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
$stmtMensagens->execute();
$mensagens = $stmtMensagens->fetchAll(PDO::FETCH_ASSOC);

// Consultar redações e temas
$redacoes_avalidas = $pdo->query("
    SELECT r.id, r.redacao, r.nota, r.criterios, u.nome AS aluno_nome
    FROM redacoes r
    INNER JOIN usuarios u ON r.aluno_id = u.id
    WHERE r.nota IS NOT NULL
")->fetchAll(PDO::FETCH_ASSOC);

$redacoes_pendentes = $pdo->query("
    SELECT r.id, r.redacao, u.nome AS aluno_nome
    FROM redacoes r
    INNER JOIN usuarios u ON r.aluno_id = u.id
    WHERE r.nota IS NULL
")->fetchAll(PDO::FETCH_ASSOC);

$temas = $pdo->query("SELECT * FROM temas WHERE professor_id = {$_SESSION['usuario_id']}")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página do Professor</title>
    <link rel="stylesheet" href="pagina_professor.css">
    <script src="pagina_professor.js"></script>
</head>
<body>

<?php if (!empty($mensagem)): ?>
    <p class="mensagem"><?= htmlspecialchars($mensagem) ?></p>
<?php endif; ?>
<div>
<a href="#" class="scroll-to-top-btn" id="scrollToTopBtn">Início</a>
</div>
<div class="header">
    <a href="logout.php" class="logout-link">Sair</a>
    <h1>Página do Professor</h1>
</div>
<section>
    <h2>Cadastrar Tema</h2>
    <form method="POST">
        <input type="hidden" name="acao" value="cadastrar_tema">
        <label for="tema">Tema:</label>
        <input type="text" id="tema" name="tema" required><br><br>
        <button type="submit">Cadastrar Tema</button>
    </form>
</section>

<section>
    <h2>Redações Pendentes</h2>
    <?php foreach ($redacoes_pendentes as $redacao): ?>
        <div>
            <p><strong>Aluno:</strong> <?= htmlspecialchars($redacao['aluno_nome']) ?></p>
            <p><strong>Redação:</strong> <?= nl2br(htmlspecialchars($redacao['redacao'])) ?></p>
            <form method="POST">
                <input type="hidden" name="acao" value="avaliar_redacao">
                <input type="hidden" name="redacao_id" value="<?= $redacao['id'] ?>">
                <label for="nota">Nota:</label>
                <input type="number" name="nota" min="0" max="10" required><br>
                <label for="criterios">Critérios:</label>
                <textarea name="criterios" required></textarea><br>
                <button type="submit">Avaliar</button>
            </form>
        </div>
    <?php endforeach; ?>
</section>

<section>
    <h2>Redações Avaliadas</h2>
    <?php foreach ($redacoes_avalidas as $redacao): ?>
        <div>
            <p><strong>Aluno:</strong> <?= htmlspecialchars($redacao['aluno_nome']) ?></p>
            <p><strong>Redação:</strong> <?= nl2br(htmlspecialchars($redacao['redacao'])) ?></p>
            <p><strong>Nota:</strong> <?= $redacao['nota']  ?></p>
            <p><strong>Critérios:</strong> <?= nl2br(htmlspecialchars($redacao['criterios'])) ?></p>
            <form method="POST" id="edit-form-<?= $redacao['id'] ?>" style="display: none;">
                <input type="hidden" name="acao" value="editar_redacao">
                <input type="hidden" name="redacao_id" value="<?= $redacao['id'] ?>">
                <label for="nova_redacao">Nova Redação:</label>
                <textarea name="nova_redacao"><?= htmlspecialchars($redacao['redacao']) ?></textarea><br>
                <label for="nova_nota">Nova Nota:</label>
                <input type="number" name="nova_nota" min="0" max="10" value="<?= $redacao['nota']  ?>"><br>
                <label for="novos_criterios">Novos Critérios:</label>
                <input type="text" name="novos_criterios" value="<?= htmlspecialchars($redacao['criterios']) ?>"><br>
                <button type="submit">Salvar Edição</button>
            </form>
            <button type="button" onclick="toggleEditRedacao(<?= $redacao['id'] ?>)">Editar Redação</button>
        </div>
    <?php endforeach; ?>
</section>
<section>
    <h2>Mensagens Recebidas</h2>
    <?php if (count($mensagens) > 0): ?>
        <?php foreach ($mensagens as $mensagem): ?>
            <div class="mensagem-recebida">
                <p><strong>De:</strong> <?= htmlspecialchars($mensagem['remetente']) ?></p>
                <p><strong>Mensagem:</strong> <?= nl2br(htmlspecialchars($mensagem['mensagem'])) ?></p>
                <p><strong>Enviada em:</strong> <?= date('d/m/Y H:i', strtotime($mensagem['data_envio'])) ?></p>

                <h2>Enviar Resposta</h2>
                <form action="pagina_professor.php" method="post">
                    <input type="hidden" name="acao" value="enviar_resposta">
                    <input type="hidden" name="destinatario_id" value="<?= $mensagem['remetente_id'] ?>">  <!-- Corrigir o destinatario -->
                    <input type="hidden" name="resposta_id" value="<?= $mensagem['id'] ?>">  <!-- Corrigir resposta_id -->
                    Nome: <input type="text" name="nome_remetente" required><br>
                    Mensagem: <textarea name="mensagem_resposta" required></textarea><br>
                    <input type="submit" value="Enviar Resposta">
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Não há mensagens recebidas.</p>
    <?php endif; ?>
</section>

    <h2>Cadastrar Aluno</h2>
    <form method="POST">
        <input type="hidden" name="acao" value="cadastrar_aluno">
        <label for="login_aluno">Nome:</label>
        <input type="text" id="login_aluno" name="login_aluno" required><br><br>
        <label for="senha_aluno">Senha:</label>
        <input type="password" id="senha_aluno" name="senha_aluno" required><br><br>
        <label for="senha_confirmada">Confirmar Senha:</label>
        <input type="password" id="senha_confirmada" name="senha_confirmada" required><br><br>
        <label for="turma">Turma:</label>
        <input type="text" id="turma" name="turma" required><br><br>
        <label for="cpf">CPF:</label>
        <input type="text" id="cpf" name="cpf" required><br><br>
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required><br><br>
        <button type="submit">Cadastrar</button>
    </form>
</section>
<footer>
        <p>&copy; 2024 Sistema de Redação</p>
    </footer>
</body>
</html>
