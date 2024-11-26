// Função para validar o formulário antes de enviar
function validarFormulario() {
    // Obtém os valores dos campos de email e senha
    var email = document.getElementById("email").value;
    var senha = document.getElementById("senha").value;

    // Limpa mensagens de erro anteriores
    document.getElementById("erro").innerHTML = "";

    // Valida o email
    if (email === "") {
        document.getElementById("erro").innerHTML = "Por favor, informe seu email.";
        return false; // Não envia o formulário
    }

    // Valida a senha
    if (senha === "") {
        document.getElementById("erro").innerHTML = "Por favor, informe sua senha.";
        return false; // Não envia o formulário
    }

    // Se tudo estiver correto, envia o formulário
    return true;
}
