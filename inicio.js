// Função para redirecionar o usuário com base no tipo selecionado
function redirectToUserPage() {
    var userType = document.getElementById("userType").value;  // Pega o valor do campo de seleção do tipo de usuário
    if (userType === "aluno") {
        window.location.href = "login_aluno.php";  // Redireciona para a página de login do aluno
    } else if (userType === "professor") {
        window.location.href = "login_professor.php";  // Redireciona para a página de login do professor
    } else if (userType === "administrador") {
        window.location.href = "login_adm.php";  // Redireciona para a página de login do administrador
    } else {
        alert("Selecione um tipo de usuário válido.");  // Alerta caso o usuário não tenha selecionado um tipo
    }
}

// Função para validar o formulário de contato antes de enviar
document.getElementById("contactForm").addEventListener("submit", function(event) {
    // Previne o envio do formulário caso haja um erro de validação
    event.preventDefault();

    // Obtém os valores dos campos
    var cpf = document.getElementById("cpf").value;
    var nome = document.getElementById("nome").value;
    var mensagem = document.getElementById("mensagem").value;

    // Verifica se todos os campos obrigatórios foram preenchidos
    if (cpf === "" || nome === "" || mensagem === "") {
        document.getElementById("feedback").innerText = "Por favor, preencha todos os campos antes de enviar.";  // Exibe mensagem de erro
        document.getElementById("feedback").style.color = "red";
        return;  // Não envia o formulário
    }

    // Verifica se o CPF tem o formato correto (simplesmente como exemplo, um CPF com 11 dígitos)
    var cpfRegex = /^\d{11}$/;
    if (!cpfRegex.test(cpf)) {
        document.getElementById("feedback").innerText = "CPF inválido. Digite apenas números.";  // Exibe mensagem de erro
        document.getElementById("feedback").style.color = "red";
        return;  // Não envia o formulário
    }

    // Se tudo estiver correto, o formulário é enviado
    this.submit();
});

// Função para mostrar o feedback do formulário
function showFeedback(message, isError) {
    var feedbackDiv = document.getElementById("feedback");
    feedbackDiv.innerText = message;  // Define o texto da mensagem de feedback
    feedbackDiv.style.color = isError ? "red" : "green";  // Se for um erro, cor vermelha, se não, verde
}

// Função para criar uma confirmação ao enviar uma mensagem
function sendMessageConfirmation() {
    alert("Sua mensagem foi enviada com sucesso!");  // Exibe uma mensagem de sucesso ao usuário
    window.location.href = "inicio.php";  // Redireciona para a página inicial após o envio
}

// Funcionalidade adicional para melhorar a experiência do usuário
document.getElementById("contactForm").addEventListener("submit", function() {
    setTimeout(function() {
        sendMessageConfirmation();  // Chama a função de confirmação após alguns segundos
    }, 1000);  // Aguardando 1 segundo antes de mostrar a confirmação
});
