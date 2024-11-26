<?php
ob_start();
session_start();

// Configuração do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'desafio_resumo');
define('DB_USER', 'admin');
define('DB_PASS', 'admin123');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Verificação de Autenticação e Redirecionamento ao Login
function verificarAutenticacao() {
    if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo'] !== 'admin') {
        header("Location: /login_adm.php"); // Redireciona para a página de login
        exit();
    }
}

// Função para gerar o Token CSRF apenas se o usuário estiver autenticado
if (empty($_SESSION['csrf_token']) && isset($_SESSION['usuario_id'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'] ?? '';

// Verificação de acesso ao painel
verificarAutenticacao();

// Funções de CRUD para solicitações
function listarSolicitacoes($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM solicitacoes_professor WHERE status IN ('pendente', 'aprovado')");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function aprovarSolicitacao($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE solicitacoes_professor SET status = 'aprovado' WHERE id = :id");
    $stmt->execute(['id' => $id]);

    $stmt = $pdo->prepare("SELECT nome, email, senha FROM solicitacoes_professor WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $solicitacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($solicitacao) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, 'professor')");
        $stmt->execute([
            'nome' => $solicitacao['nome'],
            'email' => $solicitacao['email'],
            'senha' => $solicitacao['senha']
        ]);
    }
}

function rejeitarSolicitacao($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE solicitacoes_professor SET status = 'rejeitado' WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

function excluirSolicitacao($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM solicitacoes_professor WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

function editarSolicitacao($pdo, $id, $status) {
    $stmt = $pdo->prepare("UPDATE solicitacoes_professor SET status = :status WHERE id = :id");
    $stmt->execute(['id' => $id, 'status' => $status]);
}

// Funções de CRUD para usuários
function listarUsuarios($pdo) {
    $stmt = $pdo->prepare("SELECT id, nome, email, tipo FROM usuarios");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function excluirUsuario($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

// Processamento das ações do formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Token de CSRF inválido.');
    }

    $acao = $_POST['acao'];
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null; // Novo campo para editar o status

    if ($acao === 'aprovar' && $id) {
        aprovarSolicitacao($pdo, $id);
        $_SESSION['mensagem'] = "Solicitação aprovada e professor cadastrado com sucesso!";
    } elseif ($acao === 'rejeitar' && $id) {
        rejeitarSolicitacao($pdo, $id);
        $_SESSION['mensagem'] = "Solicitação rejeitada.";
    } elseif ($acao === 'excluir_solicitacao' && $id) {
        excluirSolicitacao($pdo, $id);
        $_SESSION['mensagem'] = "Solicitação excluída.";
    } elseif ($acao === 'editar_solicitacao' && $id && $status) {
        editarSolicitacao($pdo, $id, $status);
        $_SESSION['mensagem'] = "Solicitação editada com sucesso!";
    }

    if ($acao === 'excluir_usuario' && $id) {
        excluirUsuario($pdo, $id);
        $_SESSION['mensagem'] = "Usuário excluído.";
    }

    header("Location: /pagina_adm.php?token=" . urlencode($token));
    exit();
}

$solicitacoes = listarSolicitacoes($pdo);
$usuarios = listarUsuarios($pdo);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Gerenciamento</title>
    <link rel="stylesheet" href="pagina_adm.css">
    <script src="pagina_adm.js" defer></script>
</head>
<body>
    <div>
        <!-- Botão "Início" no canto superior esquerdo -->
        <a href="inicio.php" class="btn inicio">Início</a>
        
        <!-- Restante do conteúdo da página -->

    </div>

    <?php if (isset($_SESSION['mensagem'])): ?>
        <p><?php echo $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?></p>
    <?php endif; ?>

    <!-- Painel de Solicitações (Pendentes e Aprovadas) -->
    <h1>Solicitações</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($solicitacoes as $solicitacao): ?>
            <tr>
                <td><?php echo htmlspecialchars($solicitacao['id']); ?></td>
                <td><?php echo htmlspecialchars($solicitacao['nome']); ?></td>
                <td><?php echo htmlspecialchars($solicitacao['email']); ?></td>
                <td><?php echo htmlspecialchars($solicitacao['status']); ?></td>
                <td>
                    <?php if ($solicitacao['status'] === 'pendente'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $solicitacao['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                            <button name="acao" value="aprovar">Aprovar</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $solicitacao['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                            <button name="acao" value="rejeitar">Rejeitar</button>
                        </form>
                    <?php else: ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $solicitacao['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                            <select name="status">
                                <option value="aprovado" <?php echo $solicitacao['status'] === 'aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                                <option value="rejeitado" <?php echo $solicitacao['status'] === 'rejeitado' ? 'selected' : ''; ?>>Rejeitado</option>
                            </select>
                            <button name="acao" value="editar_solicitacao">Editar</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $solicitacao['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                            <button name="acao" value="excluir_solicitacao">Excluir</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Listagem de Usuários -->
    <h1>Usuários</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                <td><?php echo htmlspecialchars($usuario['tipo']); ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
                        <button name="acao" value="excluir_usuario">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Botão "Sair" no canto inferior direito -->
    <a href="logout.php" class="btn sair">Sair</a>
    
    <footer>
        <p>&copy; 2024 Sistema de Redação</p>
    </footer>
</body>
</html>
