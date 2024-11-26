<?php
session_start();

// Verifica se o usuário está logado e se é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: inicio.php"); // Redireciona para a página de login se não for admin
    exit();
}

// Conexão com o banco de dados (modifique conforme sua configuração)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "desafio_resumo";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica se houve algum erro na conexão com o banco de dados
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Verifica se o formulário foi enviado corretamente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida o ID do professor
    if (isset($_POST['prof_id']) && is_numeric($_POST['prof_id'])) {
        $prof_id = intval($_POST['prof_id']);

        // Verifica se a ação é "aprovar" ou "rejeitar"
        if (isset($_POST['aprovar'])) {
            // Aprova o cadastro do professor
            $sql = "UPDATE professores SET status = 'aprovado' WHERE id = ?";
        } elseif (isset($_POST['rejeitar'])) {
            // Rejeita o cadastro do professor
            $sql = "UPDATE professores SET status = 'rejeitado' WHERE id = ?";
        } else {
            // Ação inválida
            header("Location: painel_admin.php?token=" . $_SESSION['token'] . "&erro=acao_invalida");
            exit();
        }

        // Prepara e executa a query
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $prof_id);
        
        if ($stmt->execute()) {
            // Redireciona de volta para o painel de administração com uma mensagem de sucesso
            header("Location: painel_admin.php?token=" . $_SESSION['token'] . "&sucesso=acao_realizada");
            exit();
        } else {
            // Caso ocorra algum erro na execução
            header("Location: painel_admin.php?token=" . $_SESSION['token'] . "&erro=falha_execucao");
            exit();
        }

        // Fecha a preparação e a conexão
        $stmt->close();
    } else {
        // Redireciona se o ID do professor for inválido
        header("Location: painel_admin.php?token=" . $_SESSION['token'] . "&erro=id_invalido");
        exit();
    }
}

// Fecha a conexão com o banco de dados
$conn->close();

// Redireciona para a página do painel caso a solicitação não tenha sido feita via POST
header("Location: painel_admin.php?token=" . $_SESSION['token']);
exit();
?>
