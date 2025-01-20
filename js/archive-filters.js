jQuery(document).ready(function($) {
    const priceRange = $('#price-range');
    const minPriceOutput = $('#min-price');
    const maxPriceOutput = $('#max-price');
    const minPriceInput = $('#min-price-input');
    const maxPriceInput = $('#max-price-input');

    priceRange.on('input', function() {
        const value = $(this).val();
        const min = $(this).attr('min');
        const max = $(this).attr('max');
        
        // Atualiza os valores exibidos
        minPriceOutput.text(formatPrice(value));
        maxPriceOutput.text(formatPrice(max));
        
        // Atualiza os inputs hidden
        minPriceInput.val(value);
        maxPriceInput.val(max);
    });

    function formatPrice(value) {
        return parseFloat(value).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Função para atualizar a URL com os parâmetros selecionados
    function updateQueryString(key, value) {
        var baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        var urlParams = new URLSearchParams(window.location.search);
        
        if (value) {
            urlParams.set(key, value);
        } else {
            urlParams.delete(key);
        }
        
        var newUrl = baseUrl + '?' + urlParams.toString();
        window.history.replaceState(null, '', newUrl);
    }

    // Atualiza a URL quando os checkboxes são alterados
    $('.filter-checkbox input').on('change', function() {
        var type = $(this).attr('name').replace('[]', '');
        var selectedValues = [];
        
        $('input[name="' + $(this).attr('name') + '"]:checked').each(function() {
            selectedValues.push($(this).val());
        });
        
        updateQueryString(type, selectedValues.join(','));
    });
}); 