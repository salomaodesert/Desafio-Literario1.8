// Função para capturar a ação de clique no botão 'Voltar'
document.getElementById('backButton').addEventListener('click', function() {
    window.history.back(); // Volta para a página Inicio.php
});

// Exemplo de adicionar uma animação ao botão de login ao enviar o formulário
document.getElementById('login-form').addEventListener('submit', function() {
    // Exibe uma mensagem de carregamento enquanto processa o login
    const feedback = document.getElementById('feedback');
    feedback.style.color = 'green'; // Altera a cor da mensagem
    feedback.textContent = "Entrando..."; // Mensagem temporária durante o processamento
});
