<?php
// Conexão com o banco de dados
include 'db.php'; // Inclui o arquivo de conexão com o banco de dados

// Consulta para buscar os alunos com as melhores notas
try {
    // Consulta para buscar os alunos com as melhores notas
    $query = "
        SELECT u.nome, r.nota, r.criterios
        FROM redacoes r
        JOIN usuarios u ON r.aluno_id = u.id
        WHERE r.nota IS NOT NULL  -- Garantir que a nota tenha sido atribuída
        ORDER BY r.nota DESC  -- Ordenar pela nota (do maior para o menor)
        LIMIT 3"; // Limitar aos 3 primeiros

    // Prepara e executa a consulta
    $stmt = $pdo->prepare($query); // Prepara a consulta SQL
    $stmt->execute(); // Executa a consulta

    // Obtém os resultados
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Busca todos os resultados como um array associativo

    // Consulta para buscar os professores
    $queryProfessores = "SELECT id, nome FROM usuarios WHERE tipo = 'professor'";
    $stmtProfessores = $pdo->prepare($queryProfessores);
    $stmtProfessores->execute();
    $professores = $stmtProfessores->fetchAll(PDO::FETCH_ASSOC); // Obtém os professores

    // Envio de Mensagem para o Professor
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obter os dados do formulário
        $nome = $_POST['nome'];  // Nome do usuário, pode ser "anônimo" ou algo similar
        $mensagem = $_POST['mensagem'];
        $professor_id = $_POST['professor_id'];

        // Verificar se os dados necessários foram fornecidos
        if (empty($mensagem) || empty($professor_id)) {
            echo "Por favor, preencha todos os campos.";
            exit;
        }

        // Tentativa de obter o ID do aluno no banco
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ?");
        $stmt->execute([$nome]);
        $aluno_id = $stmt->fetchColumn(); // Pega o ID do aluno, se existir

        // Se o aluno não existir, o remetente será "anônimo" ou algo similar
        if (!$aluno_id) {
            // Se quiser registrar como "anônimo", podemos definir um ID fictício ou uma string
            // Aqui vamos registrar como remetente "anônimo", com NULL no campo remetente_id
            $aluno_id = NULL;
        }

        // Agora, com o ID do aluno (ou NULL), inserimos a mensagem
        try {
            // Inserção de mensagem na tabela
            $query = "INSERT INTO mensagens (remetente_id, destinatario_id, mensagem) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($query);
            if ($stmt->execute([$aluno_id, $professor_id, $mensagem])) {
                echo "Mensagem enviada com sucesso!";
            } else {
                echo "Erro ao enviar a mensagem!";
            }
        } catch (Exception $e) {
            echo "Erro ao tentar inserir no banco de dados: " . $e->getMessage();
        }
    }
} catch (Exception $e) {
    echo "Erro ao buscar dados: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial</title>
    <link rel="stylesheet" href="inicio.css"> <!-- Link para o CSS -->
    <script src="inicio.js" defer></script> <!-- Link para o JavaScript -->
</head>
<body>
    <header>
        <div class="highlight-text">DESAFIO RESUMOS</div>
        <div class="description-text">"Promover o interesse pela leitura e escrita textual através de atividades criativas!"</div>
    </header>
    <main>
        <section>
            <div class="highlight-bubble">TABELA DE CLASSIFICAÇÃO</div>
            <div class="rank-container" aria-live="polite">
                <?php if (isset($errorMessage)): ?>
                    <p class="error-message"><?php echo htmlspecialchars($errorMessage); ?></p> <!-- Exibe a mensagem de erro -->
                <?php elseif (count($result) > 0): ?> <!-- Verifica se há resultados -->
                    <?php $positions = ['Primeiro', 'Segundo', 'Terceiro']; ?>
                    <?php foreach($result as $index => $row): ?> <!-- Itera sobre os resultados -->
                        <div class="rank-bubble">
                            <h4><?php echo $positions[$index]; ?> Lugar</h4>
                            <p><strong>Nome:</strong> <?php echo htmlspecialchars($row['nome']); ?></p> <!-- Exibe o nome do aluno -->
                            <p><strong>Nota:</strong> <?php echo htmlspecialchars($row['nota']); ?></p> <!-- Exibe a nota do aluno -->
                            <p><strong>Critérios:</strong> <?php echo htmlspecialchars($row['criterios']); ?></p> <!-- Exibe o critério de avaliação -->
                        </div>
                    <?php endforeach; ?>
                <?php else: ?> <!-- Caso não haja resultados -->
                    <p>Nenhum aluno encontrado.</p> <!-- Mensagem informando que não há alunos -->
                <?php endif; ?>
            </div>
        </section>

        <section>
    <div class="highlight-bubble">ACESSO USUÁRIO</div>

    <div class="button-container">
        <div class="button professor-button">
            <button class="professor-button" onclick="window.location.href='login_professor.php'">Acesso Professor</button>
    </div>

    <div class="button aluno-button">
            <button class="aluno-button" onclick="window.location.href='login_aluno.php'">Acesso Aluno</button>
    </div>

    <div class="button admin-button">
            <button class="admin-button" onclick="window.location.href='login_adm.php'">Acesso Administrador</button>
    </div>
    </div>
</section>

<section>
    <div class="highlight-bubble">ENVIAR MENSAGEM PARA PROFESSOR</div>
    <!-- Formulário para envio de mensagem -->
    <form id="contactForm" method="POST" action=""> <!-- action para enviar para a mesma página -->
    <label for="nome">Nome:</label>
    <input type="text" id="nome" name="nome" required aria-required="true" placeholder="Digite seu nome">
    
    <label for="mensagem">Mensagem:</label>
    <textarea id="mensagem" name="mensagem" required aria-required="true" placeholder="Digite sua mensagem"></textarea>
    
    <label for="professor_id">Selecione o Professor:</label>
    <select id="professor_id" name="professor_id" required>
        <?php if (count($professores) > 0): ?>
            <?php foreach ($professores as $professor): ?>
                <option value="<?php echo htmlspecialchars($professor['id']); ?>">
                    <?php echo htmlspecialchars($professor['nome']); ?>
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="">Nenhum professor encontrado</option>
        <?php endif; ?>
    </select>

    <button type="submit">Enviar</button>
</form>


    <div id="feedback" aria-live="polite"></div>
</section>

    </main>
    <footer>
        <p>&copy; 2024 Sistema de Redação</p>
    </footer>
</body>
</html>
