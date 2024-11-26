// script.js

// Função para validação simples do formulário
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("cadastro-form");

    form.addEventListener("submit", function(event) {
        let valid = true;

        // Verifica se todos os campos obrigatórios estão preenchidos
        const inputs = form.querySelectorAll("input[required]");
        inputs.forEach(input => {
            if (input.value.trim() === "") {
                valid = false;
                input.style.borderColor = "red"; // Marca o campo vazio com borda vermelha
            } else {
                input.style.borderColor = ""; // Reseta a borda
            }
        });

        // Se algum campo estiver vazio, previne o envio do formulário
        if (!valid) {
            event.preventDefault();
            alert("Por favor, preencha todos os campos obrigatórios.");
        }
    });
});
