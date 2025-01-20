<?php
function vime_enqueue_scripts() {
    // CSS Principal
    wp_enqueue_style(
        'main-style',
        get_template_directory_uri() . '/css/main.css',
        array(),
        '1.0.0'
    );

    // Primeiro carrega o jQuery
    wp_enqueue_script('jquery');

    // Depois carrega o script da galeria
    if (is_singular('produto')) {
        wp_enqueue_script(
            'product-gallery',
            get_template_directory_uri() . '/js/product-gallery.js',
            array('jquery'),
            '1.0.2',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'vime_enqueue_scripts');
