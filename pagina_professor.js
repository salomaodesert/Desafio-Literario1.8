// Função para mostrar/ocultar a área de edição de redação
function toggleEditRedacao(redacaoId) {
    var formEdit = document.getElementById("edit-form-" + redacaoId);
    formEdit.style.display = (formEdit.style.display === "none" || formEdit.style.display === "") ? "block" : "none";
}

// Função para validar o formulário de avaliação de redação
function validarFormularioAvaliacao(form) {
    var nota = form.nota.value;
    var criterios = form.criterios.value;

    // Verifica se a nota está entre 0 e 10
    if (nota === "" || isNaN(nota) || nota < 0 || nota > 10) {
        alert("Por favor, insira uma nota válida entre 0 e 10.");
        return false;
    }

    // Verifica se os critérios não estão vazios
    if (criterios.trim() === "") {
        alert("Os critérios de avaliação não podem estar vazios.");
        return false;
    }

    return true;
}

// Função para validar o formulário de edição de redação
function validarFormularioEdicao(form) {
    var novaRedacao = form.nova_redacao.value;
    var novaNota = form.nova_nota.value;
    var novosCriterios = form.novos_criterios.value;

    // Verifica se a nova redação não está vazia
    if (novaRedacao.trim() === "") {
        alert("A redação não pode estar vazia.");
        return false;
    }

    // Verifica se a nova nota está entre 0 e 10
    if (novaNota === "" || isNaN(novaNota) || novaNota < 0 || novaNota > 10) {
        alert("Por favor, insira uma nota válida entre 0 e 10.");
        return false;
    }

    // Verifica se os novos critérios não estão vazios
    if (novosCriterios.trim() === "") {
        alert("Os novos critérios de avaliação não podem estar vazios.");
        return false;
    }

    return true;
}

// Adicionar evento de validação ao formulário de avaliação de redação
document.addEventListener("DOMContentLoaded", function() {
    // Formulários de avaliação de redação
    var formulariosAvaliacao = document.querySelectorAll('form[action=""]');
    formulariosAvaliacao.forEach(function(form) {
        form.addEventListener("submit", function(event) {
            if (!validarFormularioAvaliacao(form)) {
                event.preventDefault(); // Impede o envio se não passar na validação
            }
        });
    });

    // Formulários de edição de redação
    var formulariosEdicao = document.querySelectorAll('form[action="editar_redacao"]');
    formulariosEdicao.forEach(function(form) {
        form.addEventListener("submit", function(event) {
            if (!validarFormularioEdicao(form)) {
                event.preventDefault(); // Impede o envio se não passar na validação
            }
        });
    });
});

// Função para rolar suavemente para o topo da página
let scrollToTopBtn = document.getElementById("scrollToTopBtn");

// Quando o usuário rolar para baixo 100px a partir do topo da página, mostra o botão
window.onscroll = function() {
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        scrollToTopBtn.style.display = "block";
    } else {
        scrollToTopBtn.style.display = "none";
    }
};

// Quando o botão for clicado, rola suavemente até o topo
scrollToTopBtn.onclick = function(e) {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
