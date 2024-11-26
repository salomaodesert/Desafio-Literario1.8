<?php
// Verifica se o tipo de usuário está na URL
if (!isset($_GET['user'])) {
    echo "Acesso negado!";
    exit();
}

$userType = $_GET['user'];

echo "<h1>Bem-vindo, $userType!</h1>";

// Conteúdo específico para o tipo de usuário pode ser exibido aqui
if ($userType == 'aluno') {
    echo "<p>Área exclusiva para alunos.</p>";
} elseif ($userType == 'professor') {
    echo "<p>Área exclusiva para professores.</p>";
}
?>
