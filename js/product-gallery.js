jQuery(document).ready(function($) {

    // Evento de clique nas miniaturas
    $('.img-product-gallery a').on('click', function(e) {
        e.preventDefault();
        
        var novaUrl = $(this).attr('data-large');
        var $imagemPrincipal = $('.img-product-principal');
        
        // Fade out da imagem atual
        $imagemPrincipal.find('img').fadeOut(300, function() {
            // Cria nova imagem com fade in
            $imagemPrincipal.html('<img src="' + novaUrl + '" class="main-product-image" style="display: none;" />');
            $('.main-product-image').fadeIn(300);
        });
        
        // Atualiza estado ativo
        $('.img-product-gallery a').removeClass('active');
        $(this).addClass('active');
    });

    // Marca primeira miniatura como ativa inicialmente
    $('.img-product-gallery a:first').addClass('active');

    // Popup da imagem
    function initializePopup() {
        var $mainImage = $('.img-product-principal img');
        var $popup = $('.popup');
        var $popupImage = $('.popup-content img');
        var $closePopup = $('.popup .close');

        // Abrir popup
        $mainImage.on('click', function() {
            var currentSrc = $(this).attr('src');
            $popupImage.attr('src', currentSrc);
            $popup.addClass('active');
            $('body').css('overflow', 'hidden');
        });

        // Fechar popup no botão
        $closePopup.on('click', function() {
            $popup.removeClass('active');
            $('body').css('overflow', '');
        });

        // Fechar popup clicando fora
        $popup.on('click', function(e) {
            if (e.target === this) {
                $popup.removeClass('active');
                $('body').css('overflow', '');
            }
        });

        // Fechar com ESC
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $popup.hasClass('active')) {
                $popup.removeClass('active');
                $('body').css('overflow', '');
            }
        });
    }

    // Inicializa o popup
    initializePopup();

    // Reinicializa o popup quando a imagem principal é trocada
    $('.img-product-gallery a').on('click', function() {
        setTimeout(initializePopup, 400); // Espera o fade terminar
    });

});
