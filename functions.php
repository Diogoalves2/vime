<?php

// Adiciona suporte a thumbnails
add_theme_support('post-thumbnails');
add_theme_support('title-tag');
add_theme_support('custom-logo');
add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

// Adiciona suporte a SVG
function add_svg_support($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'add_svg_support');

// Corrige o preview de SVG no Media Library
function fix_svg_preview($response) {
    if ($response['mime'] === 'image/svg+xml') {
        $response['sizes'] = [
            'full' => [
                'url' => $response['url'],
                'width' => '100%',
                'height' => '100%'
            ]
        ];
    }
    return $response;
}
add_filter('wp_prepare_attachment_for_js', 'fix_svg_preview');

// Corrige a exibição de SVG no admin
function fix_svg_thumb_display() {
    echo '<style>
        td.media-icon img[src$=".svg"], img[src$=".svg"].attachment-post-thumbnail { 
            width: 100% !important; 
            height: auto !important; 
        }
    </style>';
}
add_action('admin_head', 'fix_svg_thumb_display');

// Define tamanhos de imagem personalizados
add_image_size('produto-thumbnail', 300, 300, array('center', 'center'));
add_image_size('produto-square-medium', 600, 600, array('center', 'center'));
add_image_size('produto-square-large', 900, 900, array('center', 'center'));
add_image_size('produto-medium', 800, 600, true);
add_image_size('produto-large', 1200, 800, true);

// Registra o menu principal
register_nav_menu('primary', 'Menu Principal');

// Inclui o arquivo de produtos
require_once get_template_directory() . '/inc/produtos.php';

// Inclui os arquivos de estilo e scripts
if (file_exists(get_template_directory() . '/inc/enqueue.php')) {
    require_once get_template_directory() . '/inc/enqueue.php';
}

// Debug de imagens destacadas
function vime_debug_featured_image($post_id) {
    if (current_user_can('administrator') && get_post_type($post_id) === 'produto') {
        $has_thumbnail = has_post_thumbnail($post_id);
        $thumbnail_id = get_post_thumbnail_id($post_id);
        $thumbnail_url = get_the_post_thumbnail_url($post_id, 'large');
        
        error_log('Debug Imagem Destacada:');
        error_log('Post ID: ' . $post_id);
        error_log('Tem thumbnail? ' . ($has_thumbnail ? 'Sim' : 'Não'));
        error_log('Thumbnail ID: ' . $thumbnail_id);
        error_log('Thumbnail URL: ' . $thumbnail_url);
    }
}
add_action('save_post', 'vime_debug_featured_image');

function adicionar_suporte_produto() {
    add_post_type_support('produto', 'thumbnail');
}
add_action('init', 'adicionar_suporte_produto');

// Debug para salvar campos personalizados
function debug_save_post($post_id) {
    if (get_post_type($post_id) === 'produto') {
        $gallery = get_post_meta($post_id, 'produto_galeria', true);
        error_log('Saving produto_galeria for post ' . $post_id);
        error_log('Gallery value: ' . print_r($gallery, true));
    }
}
add_action('save_post', 'debug_save_post');

// Adicionar suporte para templates de taxonomia personalizados
function check_taxonomy_template($template) {
    if (is_tax('category')) {
        error_log('Template sendo usado: ' . $template);
    }
    return $template;
}
add_filter('template_include', 'check_taxonomy_template');

// Adicionar debug para template
function debug_template_being_loaded($template) {
    error_log('Template sendo carregado: ' . $template);
    return $template;
}
add_filter('template_include', 'debug_template_being_loaded');

// Filtrar produtos por categoria
function filter_products_by_category($query) {
    if (!is_admin() && $query->is_main_query()) {
        // Se for uma página de produto individual, não altere a query
        if (is_singular('produto')) {
            return;
        }
        
        // Se for arquivo ou categoria
        if ($query->is_archive() || $query->is_category()) {
            $query->set('post_type', 'produto');
        }
    }
}
add_action('pre_get_posts', 'filter_products_by_category');

function enqueue_admin_scripts($hook) {
    if ('post.php' === $hook || 'post-new.php' === $hook) {
        wp_enqueue_media();
        
        // Adicione outros scripts necessários aqui
        wp_enqueue_script('jquery');
    }
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

// Alterar o título do arquivo de produtos
function custom_archive_title($title) {
    // Verifica se é a página de arquivo de produtos
    if (is_post_type_archive()) {
        $post_type = get_post_type_object(get_post_type());
        if ($post_type->name === 'produto') {
            return 'Produtos';
        }
    }
    // Remove "Arquivos:" do início do título
    if (is_archive()) {
        return str_replace('Arquivos: ', '', $title);
    }
    return $title;
}
add_filter('get_the_archive_title', 'custom_archive_title');

// Função para incrementar visualizações do produto
function increment_product_views() {
    if (is_singular('produto')) {
        $post_id = get_the_ID();
        $views = get_post_meta($post_id, 'produto_views', true);
        
        if ($views === '') {
            add_post_meta($post_id, 'produto_views', '1');
        } else {
            $views++;
            update_post_meta($post_id, 'produto_views', $views);
        }
    }
}
add_action('wp', 'increment_product_views');

// Debug para campos ACF
add_action('acf/init', function() {
    error_log('Campos ACF registrados:');
    if(function_exists('acf_get_fields')) {
        $fields = acf_get_fields('group_faqhome'); // Substitua pelo ID do seu grupo
        error_log(print_r($fields, true));
    }
});

// Função para garantir que as imagens sejam cortadas corretamente
function adjust_image_crop_positions($crop_position, $attachment_id) {
    // Pegar informações da imagem
    $image_data = wp_get_attachment_metadata($attachment_id);
    
    if (!empty($image_data)) {
        $width = $image_data['width'];
        $height = $image_data['height'];
        
        // Se a imagem for mais alta que larga
        if ($height > $width) {
            return array('center', 'top');
        }
        // Se a imagem for mais larga que alta
        else if ($width > $height) {
            return array('center', 'center');
        }
    }
    
    // Padrão: centro
    return array('center', 'center');
}
add_filter('image_resize_coordinates', 'adjust_image_crop_positions', 10, 2);

// Função para regenerar miniaturas quando necessário
function maybe_regenerate_thumbnails($attachment_id) {
    $metadata = wp_get_attachment_metadata($attachment_id);
    
    if (empty($metadata['sizes']['produto-thumbnail']) || 
        empty($metadata['sizes']['produto-square-medium']) || 
        empty($metadata['sizes']['produto-square-large'])) {
        
        wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, get_attached_file($attachment_id)));
    }
}
add_action('add_attachment', 'maybe_regenerate_thumbnails');

// Melhorar a busca do WordPress
function improve_product_search($query) {
    if ($query->is_search && !is_admin() && $query->is_main_query()) {
        // Configura o tipo de post para produto
        $query->set('post_type', 'produto');
        
        // Modifica a query de busca
        add_filter('posts_search', function($search, $wp_query) {
            global $wpdb;
            
            if (empty($search) || !$wp_query->is_search) {
                return $search;
            }
            
            $terms = $wp_query->get('s');
            $search = '';
            
            // Busca no título, conteúdo e excerpt
            $search .= "AND (
                {$wpdb->posts}.post_title LIKE '%{$terms}%'
                OR {$wpdb->posts}.post_content LIKE '%{$terms}%'
                OR {$wpdb->posts}.post_excerpt LIKE '%{$terms}%'
            )";
            
            return $search;
        }, 10, 2);
        
        // Define a ordenação
        $query->set('orderby', 'post_title');
        $query->set('order', 'ASC');
        
        // Define o número de posts por página
        $query->set('posts_per_page', 12);
    }
    return $query;
}
add_action('pre_get_posts', 'improve_product_search');

// Adicione este código temporariamente para debug
add_action('init', 'debug_rewrite_rules', 999);
function debug_rewrite_rules() {
    global $wp_rewrite;
    
    if (isset($_GET['debug_rewrite'])) {
        echo '<pre>';
        print_r($wp_rewrite->rules);
        echo '</pre>';
        exit;
    }
}

add_action('template_redirect', 'debug_template', 999);
function debug_template() {
    if (isset($_GET['debug_template'])) {
        global $wp_query;
        echo '<pre>';
        echo 'Post Type: ' . get_post_type() . "\n";
        echo 'Is 404? ' . ($wp_query->is_404() ? 'yes' : 'no') . "\n";
        echo 'Query Vars: ';
        print_r($wp_query->query_vars);
        echo '</pre>';
        exit;
    }
}

// Adicione esta função de debug
function debug_produto_query($query) {
    if (is_singular('produto')) {
        error_log('Debug Produto Query:');
        error_log('Query vars: ' . print_r($query->query_vars, true));
        error_log('Post type: ' . $query->get('post_type'));
        error_log('Name: ' . $query->get('name'));
    }
}
add_action('pre_get_posts', 'debug_produto_query');

// Adicione esta função para debug de template
function debug_template_hierarchy($templates) {
    error_log('Template Hierarchy: ' . print_r($templates, true));
    return $templates;
}
add_filter('template_hierarchy', 'debug_template_hierarchy');

function debug_permalink_structure() {
    global $wp_rewrite;
    error_log('Estrutura de Permalinks:');
    error_log('Permalink Structure: ' . get_option('permalink_structure'));
    error_log('Produto Rewrite Rules:');
    $rules = $wp_rewrite->wp_rewrite_rules();
    foreach ($rules as $key => $value) {
        if (strpos($value, 'produto') !== false) {
            error_log($key . ' => ' . $value);
        }
    }
}
add_action('init', 'debug_permalink_structure', 999);

// Adicione temporariamente para debug
add_action('template_redirect', function() {
    if (is_404()) {
        global $wp_query;
        error_log('404 Debug:');
        error_log('Requested URL: ' . $_SERVER['REQUEST_URI']);
        error_log('Query Vars: ' . print_r($wp_query->query_vars, true));
        error_log('Post Type: ' . get_post_type());
    }
});

// Adicione esta função temporariamente
function debug_product_gallery_save($post_id) {
    if (get_post_type($post_id) === 'produto') {
        $gallery = get_post_meta($post_id, 'produto_galeria', true);
        error_log('=== Debug Salvamento da Galeria ===');
        error_log('Post ID: ' . $post_id);
        error_log('Dados salvos: ' . print_r($gallery, true));
        error_log('================================');
    }
}
add_action('save_post', 'debug_product_gallery_save');

// Registra o Custom Post Type quando o tema é ativado
function theme_register_post_types() {
    $labels = array(
        'name'               => 'Produtos',
        'singular_name'      => 'Produto',
        'menu_name'          => 'Produtos',
        'add_new'           => 'Adicionar Novo',
        'add_new_item'      => 'Adicionar Novo Produto',
        'edit_item'         => 'Editar Produto',
        'new_item'          => 'Novo Produto',
        'view_item'         => 'Ver Produto',
        'search_items'      => 'Buscar Produtos',
        'not_found'         => 'Nenhum produto encontrado',
        'not_found_in_trash'=> 'Nenhum produto encontrado na lixeira',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'produto'),
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'taxonomies'         => array('category', 'post_tag'),
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-cart',
        'show_in_menu'       => false
    );

    register_post_type('produto', $args);
}
add_action('init', 'theme_register_post_types');

// Adicione esta função para forçar a atualização das regras de rewrite
function force_rewrite_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules(true);
}

// Registre as ações
add_action('after_switch_theme', 'force_rewrite_rules');
register_activation_hook(__FILE__, 'force_rewrite_rules');

// Adicione esta função de debug
function debug_single_produto() {
    if (is_404()) {
        global $wp_query, $wp_rewrite;
        
        error_log('=== Debug Single Produto ===');
        error_log('URL Requisitada: ' . $_SERVER['REQUEST_URI']);
        error_log('Post Type: ' . get_post_type());
        error_log('Query Vars: ' . print_r($wp_query->query_vars, true));
        error_log('Is Single? ' . (is_single() ? 'Sim' : 'Não'));
        error_log('Post Type Query: ' . $wp_query->get('post_type'));
        error_log('Nome do Post: ' . $wp_query->get('name'));
        error_log('Regras de Rewrite:');
        error_log(print_r($wp_rewrite->rules, true));
        
        // Tenta encontrar o post diretamente
        $post = get_page_by_path('cadeira-de-marmore-2026', OBJECT, 'produto');
        if ($post) {
            error_log('Post encontrado por slug:');
            error_log('ID: ' . $post->ID);
            error_log('Post Type: ' . $post->post_type);
            error_log('Status: ' . $post->post_status);
        } else {
            error_log('Post não encontrado por slug');
        }
    }
}
add_action('template_redirect', 'debug_single_produto');

function enqueue_product_scripts() {
    if (is_singular('produto')) {
        wp_enqueue_script(
            'product-gallery', 
            get_template_directory_uri() . '/js/product-gallery.js', 
            array('jquery'), 
            '1.0.1', // Alterado para forçar atualização do cache
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_product_scripts');

function enqueue_archive_scripts() {
    if (is_post_type_archive('produto')) {
        wp_enqueue_script(
            'archive-filters',
            get_template_directory_uri() . '/js/archive-filters.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_archive_scripts');

// Registrar taxonomia de materiais
function register_material_taxonomy() {
    $labels = array(
        'name' => 'Materiais',
        'singular_name' => 'Material',
        'menu_name' => 'Materiais',
        'all_items' => 'Todos os Materiais',
        'edit_item' => 'Editar Material',
        'view_item' => 'Ver Material',
        'update_item' => 'Atualizar Material',
        'add_new_item' => 'Adicionar Novo Material',
        'new_item_name' => 'Novo Nome de Material',
        'search_items' => 'Buscar Materiais',
        'popular_items' => 'Materiais Populares'
    );

    register_taxonomy('material', 'produto', array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'material'),
    ));
}
add_action('init', 'register_material_taxonomy');

// Atualizar função de filtro
function filter_products_by_selected_category($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('produto')) {
        // Filtro por categoria
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            // Converte string em array se necessário
            $categories = is_array($_GET['category']) ? $_GET['category'] : explode(',', $_GET['category']);
            
            $query->set('category__in', array_map('intval', $categories));
        }

        // Filtro por material
        if (isset($_GET['material']) && !empty($_GET['material'])) {
            $tax_query[] = array(
                'taxonomy' => 'material',
                'field' => 'term_id',
                'terms' => (array)$_GET['material'],
                'operator' => 'IN'
            );
            $query->set('tax_query', $tax_query);
        }

        // Filtro por preço
        if (isset($_GET['min_price']) && isset($_GET['max_price'])) {
            $meta_query = array(
                array(
                    'key' => 'produto_preco',
                    'value' => array($_GET['min_price'], $_GET['max_price']),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN'
                )
            );
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'filter_products_by_selected_category');

// Ajustar número de posts por página para produtos e categorias
function set_products_per_page($query) {
    if (!is_admin() && ($query->is_main_query() && (is_post_type_archive('produto') || is_category()))) {
        $query->set('posts_per_page', 12);
        
        // Se estiver em uma categoria, garante que só mostra produtos
        if (is_category()) {
            $query->set('post_type', 'produto');
        }
    }
}
add_action('pre_get_posts', 'set_products_per_page');

// Personalizar o título do site
function custom_wp_title($title) {
    // Adicionar o nome do site no início
    $site_name = 'Vime Conceito';
    
    // Se for a página inicial, retorna apenas o nome do site
    if (empty($title)) {
        return $site_name;
    }
    
    // Para outras páginas, adiciona o nome do site no início
    return $site_name . ' | ' . $title;
}
add_filter('wp_title', 'custom_wp_title', 10, 1);

// Suporte para título no tema
function theme_slug_setup() {
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'theme_slug_setup');

// Personalizar o botão de envio do Contact Form 7
add_filter('wpcf7_form_elements', function($content) {
    $content = str_replace(
        '<input type="submit" value="Enviar Mensagem"',
        '<button type="submit" class="wpcf7-submit">Enviar Mensagem <img src="' . get_template_directory_uri() . '/img/Arrow rigth.svg" alt="">',
        $content
    );
    return $content;
});

// Adicionar campo nas configurações
function adicionar_campo_email_contato($settings) {
    add_settings_field(
        'email_formulario_contato',
        'Email para Formulário de Contato',
        'campo_email_contato_html',
        'general'
    );
    
    register_setting('general', 'email_formulario_contato');
}
add_action('admin_init', 'adicionar_campo_email_contato');

function campo_email_contato_html() {
    $value = get_option('email_formulario_contato');
    echo '<input type="email" id="email_formulario_contato" name="email_formulario_contato" value="' . esc_attr($value) . '" class="regular-text">';
}

// Processar o formulário
function processar_formulario_contato() {
    header('Content-Type: application/json'); // Garante que a resposta seja JSON

    $response = array(
        'status' => 'error',
        'message' => 'Erro ao processar formulário'
    );

    // Verificações de segurança
    if (!wp_verify_nonce($_POST['formulario_nonce'], 'formulario_contato_nonce')) {
        $response['message'] = 'Erro de segurança';
        echo json_encode($response);
        wp_die();
    }

    // 2. Proteção contra spam
    if (isset($_POST['honeypot']) && !empty($_POST['honeypot'])) {
        wp_die('Spam detectado', 'Erro', ['response' => 403]);
    }

    // 3. Rate limiting
    $ip = $_SERVER['REMOTE_ADDR'];
    $transient_key = 'form_submission_' . $ip;
    if (get_transient($transient_key)) {
        wp_safe_redirect(add_query_arg(
            array(
                'status' => 'error',
                'message' => 'Por favor, aguarde alguns segundos antes de enviar outra mensagem.'
            ),
            wp_get_referer() . '#contact'
        ));
        exit;
    }

    // 4. Validação dos campos
    $errors = array();
    
    // Nome
    $nome = sanitize_text_field($_POST['nome']);
    if (empty($nome) || strlen($nome) < 3) {
        $errors[] = 'Nome inválido';
    }

    // Email
    $email = sanitize_email($_POST['email']);
    if (!is_email($email)) {
        $errors[] = 'Email inválido';
    }

    // Telefone - validação mais flexível
    $telefone = sanitize_text_field($_POST['telefone']);
    $telefoneNumeros = preg_replace('/\D/', '', $telefone);
    if (strlen($telefoneNumeros) < 10 || strlen($telefoneNumeros) > 11) {
        $errors[] = 'Telefone inválido';
    }

    // Empresa (opcional)
    $empresa = sanitize_text_field($_POST['empresa']);

    // Mensagem
    $mensagem = sanitize_textarea_field($_POST['mensagem']);
    if (empty($mensagem) || strlen($mensagem) < 10) {
        $errors[] = 'Mensagem muito curta';
    }

    // 5. Se houver erros, retorna
    if (!empty($errors)) {
        $response['message'] = implode(', ', $errors);
        echo json_encode($response);
        wp_die();
    }

    // 6. Rate limiting - set (reduzindo para 30 segundos)
    set_transient($transient_key, true, 30); // 30 segundos de espera

    // 7. Proteção do email
    $para = get_option('email_formulario_contato');
    if (empty($para)) {
        $para = get_option('admin_email'); // Email padrão se nenhum for configurado
    }

    $assunto = 'Nova mensagem do formulário de contato';
    
    $corpo = "Nome: $nome\n";
    $corpo .= "Email: $email\n";
    $corpo .= "Telefone: $telefone\n";
    $corpo .= "Empresa: $empresa\n\n";
    $corpo .= "Mensagem:\n$mensagem";

    $headers = array(
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        'Reply-To: ' . $nome . ' <' . $email . '>',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: WordPress'
    );

    // 8. Log de tentativas
    error_log(sprintf(
        'Tentativa de contato - IP: %s, Nome: %s, Email: %s',
        $ip,
        $nome,
        $email
    ));

    // Enviar email
    $enviado = wp_mail($para, $assunto, $corpo, $headers);

    if ($enviado) {
        // Salva no banco se necessário
        wp_insert_post(array(
            'post_title' => 'Contato de ' . $nome,
            'post_content' => wp_kses_post($corpo),
            'post_type' => 'contatos',
            'post_status' => 'private',
            'meta_input' => array(
                'email' => $email,
                'telefone' => $telefone,
                'empresa' => $empresa,
                'ip' => $ip,
                'data' => current_time('mysql')
            )
        ));

        $response['status'] = 'success';
        $response['message'] = 'Mensagem enviada com sucesso!';
    }

    echo json_encode($response);
    wp_die(); // Importante para terminar a execução corretamente
}

// Registrar endpoints AJAX
add_action('wp_ajax_processar_formulario_contato', 'processar_formulario_contato');
add_action('wp_ajax_nopriv_processar_formulario_contato', 'processar_formulario_contato');

// Registrar scripts do formulário
function register_form_scripts() {
    wp_enqueue_script(
        'form-validation',
        get_template_directory_uri() . '/js/form-validation.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Adiciona variáveis para o JavaScript
    wp_localize_script(
        'form-validation',
        'formAjax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php')
        )
    );
}
add_action('wp_enqueue_scripts', 'register_form_scripts');

// Adicione esta função ao functions.php
function display_acf_image($field_name, $size = 'full') {
    $image = get_field($field_name);
    if ($image) {
        if (is_numeric($image)) {
            echo wp_get_attachment_image($image, $size);
        } else if (is_array($image)) {
            echo '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '">';
        } else {
            echo '<img src="' . esc_url($image) . '" alt="">';
        }
    }
}


