
    // Função para alternar a exibição do formulário de edição
    function toggleEditForm(id) {
        // Obtém o formulário de edição correspondente pelo ID
        const form = document.getElementById('edit-form-' + id);
        // Alterna a exibição do formulário: se estiver oculto, exibe; se estiver visível, oculta
        form.style.display = form.style.display === 'none' || form.style.display === '' ? 'table-row' : 'none';
    }
