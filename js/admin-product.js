document.addEventListener('DOMContentLoaded', function() {
    const tagItems = document.querySelectorAll('.tag-item');
    const tagsInput = document.getElementById('produto_tags');
    const newTagInput = document.getElementById('new_tag');
    const addTagButton = document.getElementById('add_tag');
    const tagsCloud = document.querySelector('.tags-cloud');

    // Função para atualizar o input hidden com as tags selecionadas
    function updateSelectedTags() {
        const selectedTags = document.querySelectorAll('.tag-item.selected');
        const tagIds = Array.from(selectedTags).map(tag => tag.dataset.tagId);
        tagsInput.value = tagIds.join(',');
    }

    // Toggle seleção de tags existentes
    tagItems.forEach(tag => {
        tag.addEventListener('click', function() {
            this.classList.toggle('selected');
            updateSelectedTags();
        });
    });

    // Adicionar nova tag
    addTagButton.addEventListener('click', function() {
        const tagName = newTagInput.value.trim();
        if (tagName) {
            // Criar nova tag
            const tagElement = document.createElement('span');
            tagElement.className = 'tag-item selected';
            tagElement.textContent = tagName;
            tagElement.dataset.tagId = 'new_' + tagName; // Identificador temporário
            
            // Adicionar à nuvem de tags
            tagsCloud.appendChild(tagElement);
            
            // Limpar input
            newTagInput.value = '';
            
            // Adicionar evento de click
            tagElement.addEventListener('click', function() {
                this.classList.toggle('selected');
                updateSelectedTags();
            });
            
            updateSelectedTags();
        }
    });
}); 