<?php

// Registra o Custom Post Type 'produto'
function registrar_cpt_produto() {
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
        'rewrite'            => array('slug' => 'produtos'),
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'custom-fields'
        ),
        'taxonomies'         => array('category'),
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-cart',
        'show_in_menu'       => false, // Mantém oculto pois já temos menu personalizado
        'show_in_rest'       => true
    );

    register_post_type('produto', $args);

    // Flush rewrite rules apenas se necessário
    if (get_option('produto_needs_rewrite_flush')) {
        flush_rewrite_rules();
        delete_option('produto_needs_rewrite_flush');
    }
}
add_action('init', 'registrar_cpt_produto');

// Adiciona meta box para informações do produto
function adicionar_meta_boxes() {
    add_meta_box(
        'produto_meta_box',
        'Informações do Produto',
        'produto_meta_box_callback',
        'produto',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'adicionar_meta_boxes');

// Callback da meta box
function produto_meta_box_callback($post) {
    wp_nonce_field('produto_meta_box_nonce', 'produto_meta_box_nonce');
    
    $preco = get_post_meta($post->ID, 'produto_preco', true);
    $galeria = get_post_meta($post->ID, 'produto_galeria', true);
    ?>
    <p>
        <label for="produto_preco">Preço:</label>
        <input type="text" id="produto_preco" name="produto_preco" value="<?php echo esc_attr($preco); ?>">
    </p>
    <div class="galeria-produto">
        <label>Galeria de Imagens:</label>
        <div id="galeria-container">
            <?php 
            if (!empty($galeria) && is_array($galeria)) {
                foreach ($galeria as $image_id) {
                    $image_url = wp_get_attachment_url($image_id);
                    if ($image_url) {
                        ?>
                        <div class="imagem-galeria">
                            <img src="<?php echo esc_url($image_url); ?>" alt="Imagem da galeria">
                            <input type="hidden" name="produto_galeria[]" value="<?php echo esc_attr($image_id); ?>">
                            <button type="button" class="remover-imagem">Remover</button>
                        </div>
                        <?php
                    }
                }
            }
            ?>
        </div>
        <button type="button" id="adicionar-imagem" class="button">Adicionar Imagem</button>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#adicionar-imagem').click(function(e) {
            e.preventDefault();
            var frame = wp.media({
                title: 'Selecionar Imagens',
                button: {
                    text: 'Usar estas imagens'
                },
                multiple: true
            });

            frame.on('select', function() {
                var attachments = frame.state().get('selection').map(function(attachment) {
                    attachment = attachment.toJSON();
                    return '<div class="imagem-galeria">' +
                           '<img src="' + attachment.url + '" alt="Imagem da galeria">' +
                           '<input type="hidden" name="produto_galeria[]" value="' + attachment.id + '">' +
                           '<button type="button" class="remover-imagem">Remover</button>' +
                           '</div>';
                });
                $('#galeria-container').append(attachments.join(''));
            });

            frame.open();
        });

        $(document).on('click', '.remover-imagem', function() {
            $(this).parent('.imagem-galeria').remove();
        });
    });
    </script>
    <?php
}

// Salva os meta dados do produto
function save_produto_meta($post_id) {
    if (!isset($_POST['produto_meta_box_nonce']) || 
        !wp_verify_nonce($_POST['produto_meta_box_nonce'], 'produto_meta_box_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['produto_preco'])) {
        update_post_meta($post_id, 'produto_preco', sanitize_text_field($_POST['produto_preco']));
    }

    if (isset($_POST['produto_galeria']) && is_array($_POST['produto_galeria'])) {
        $galeria = array_map('absint', $_POST['produto_galeria']);
        $galeria = array_filter($galeria);
        update_post_meta($post_id, 'produto_galeria', $galeria);
    }
}
add_action('save_post_produto', 'save_produto_meta');

// Adiciona o menu personalizado de produtos
function vime_add_menu_produtos() {
    add_menu_page(
        'Produtos',
        'Produtos',
        'manage_options',
        'vime-produtos',
        'vime_produtos_page',
        'dashicons-cart',
        20
    );

    add_submenu_page(
        'vime-produtos',
        'Todos os Produtos',
        'Todos os Produtos',
        'manage_options',
        'vime-produtos',
        'vime_produtos_page'
    );

    add_submenu_page(
        'vime-produtos',
        'Adicionar Novo',
        'Adicionar Novo',
        'manage_options',
        'vime-adicionar-produto',
        'vime_adicionar_produto_page'
    );
}
add_action('admin_menu', 'vime_add_menu_produtos');

// Página de listagem de produtos
function vime_produtos_page() {
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $search = isset($_GET['produto_search']) ? sanitize_text_field($_GET['produto_search']) : '';
    ?>
    <div class="wrap">
        <div class="header-container">
            <div class="title-section">
                <h1 class="wp-heading-inline">Produtos</h1>
                <a href="<?php echo admin_url('admin.php?page=vime-adicionar-produto'); ?>" class="page-title-action">
                    Adicionar Novo
                </a>
            </div>

            <form method="get" class="search-form">
                <input type="hidden" name="page" value="vime-produtos">
                <div class="search-box">
                    <input type="search" 
                           id="produto-search-input"
                           name="produto_search" 
                           value="<?php echo isset($_GET['produto_search']) ? esc_attr($_GET['produto_search']) : ''; ?>" 
                           placeholder="Buscar produtos..." 
                           class="search-input">
                    <button type="submit" class="search-submit">
                        <span class="dashicons dashicons-search"></span>
                    </button>
                </div>
            </form>
        </div>

        <div class="vime-form-section">
            <?php
            $args = array(
                'post_type' => 'produto',
                'posts_per_page' => 12,
                'paged' => $paged,
                'orderby' => 'date',
                'order' => 'DESC'
            );

            if (!empty($search)) {
                add_filter('posts_where', function($where) use ($search) {
                    global $wpdb;
                    
                    $like = '%' . $wpdb->esc_like($search) . '%';
                    
                    $where .= $wpdb->prepare("
                        AND (
                            {$wpdb->posts}.post_title LIKE %s 
                            OR {$wpdb->posts}.post_content LIKE %s
                            OR EXISTS (
                                SELECT 1 FROM {$wpdb->postmeta} 
                                WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
                                AND (
                                    ({$wpdb->postmeta}.meta_key = 'produto_preco' AND {$wpdb->postmeta}.meta_value LIKE %s)
                                )
                            )
                        )",
                        $like,
                        $like,
                        $like
                    );
                    
                    return $where;
                });
            }
            
            $produtos = new WP_Query($args);
            
            if ($produtos->have_posts()) : ?>
                <div class="table-responsive">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th width="60">Imagem</th>
                                <th>Título</th>
                                <th width="120">Preço</th>
                                <th width="150" style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($produtos->have_posts()) : $produtos->the_post(); 
                                $preco = get_post_meta(get_the_ID(), 'produto_preco', true);
                            ?>
                                <tr>
                                    <td width="60">
                                        <?php 
                                        if (has_post_thumbnail()) {
                                            echo get_the_post_thumbnail(get_the_ID(), array(50, 50), array('style' => 'border-radius: 4px;'));
                                        }
                                        ?>
                                    </td>
                                    <td><strong><?php the_title(); ?></strong></td>
                                    <td>R$ <?php echo esc_html($preco); ?></td>
                                    <td class="actions">
                                        <div class="actions-wrapper">
                                            <a href="<?php echo admin_url('admin.php?page=vime-adicionar-produto&post=' . get_the_ID()); ?>" 
                                               class="button-action edit" title="Editar">
                                                <span class="dashicons dashicons-edit"></span>
                                            </a>
                                            <a href="<?php echo get_permalink(); ?>" 
                                               class="button-action view" title="Visualizar" target="_blank">
                                                <span class="dashicons dashicons-visibility"></span>
                                            </a>
                                            <a href="<?php echo get_delete_post_link(); ?>" 
                                               class="button-action delete" 
                                               onclick="return confirm('Tem certeza que deseja excluir este produto?')" 
                                               title="Excluir">
                                                <span class="dashicons dashicons-trash"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tablenav bottom">
                    <div class="tablenav-pages">
                        <?php
                        $total_pages = $produtos->max_num_pages;
                        
                        if ($total_pages > 1) {
                            $page_links = paginate_links(array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $paged,
                                'type' => 'array'
                            ));

                            if ($page_links) {
                                echo '<div class="pagination-links">';
                                foreach ($page_links as $link) {
                                    echo $link;
                                }
                                echo '</div>';
                            }
                        }
                        ?>
                        <span class="displaying-num">
                            <?php 
                            $total_items = $produtos->found_posts;
                            printf(
                                _n('%s item', '%s itens', $total_items),
                                number_format_i18n($total_items)
                            ); 
                            ?>
                        </span>
                    </div>
                </div>

                <style>
                /* Estilo do cabeçalho */
                body.wp-admin.toplevel_page_vime-produtos div.wrap > div.header-container {
                    display: flex !important;
                    justify-content: space-between !important;
                    align-items: center !important;
                    margin-bottom: 2rem !important;
                }

                body.wp-admin.toplevel_page_vime-produtos .title-section {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    flex: 0 0 auto;
                }
                

                body.wp-admin.toplevel_page_vime-produtos .page-title-action {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #010167;
                    width: 180px;
                    height: 40px;
                    color: white;
                    padding: 0.4rem 1.2rem;
                    text-decoration: none;
                    font-size: 16px;
                    border-radius: 4px;
                    text-transform: uppercase;
                    font-weight: 600;
                    border: none;
                    transition: all 0.3s ease;
                    margin-top: 10px;
                }

                .wp-heading-inline {
                    margin: 0 !important;
                    font-size: 1.8rem;
                    color: #0A0E18;
                    text-transform: uppercase;
                }

                /* Estilo do formulário de busca */
                body.wp-admin.toplevel_page_vime-produtos .search-form {
                    width: 100%;
                    max-width: 300px;
                }

                body.wp-admin.toplevel_page_vime-produtos .search-box {
                    display: flex;
                    width: 100%;
                    position: relative;
                }

                .search-input {
                    width: 100%;
                    padding: 0.8rem 3.2rem 0.8rem 1.2rem;
                    border: 1px solid #E3E6EC;
                    border-radius: 0.8rem;
                    font-size: 1.2rem;
                    background: #f8f9fa;
                }

                .search-input::-webkit-search-cancel-button {
                    display: none;
                }

                .search-submit {
                    position: absolute;
                    right: 0.8rem;
                    top: 50%;
                    transform: translateY(-50%);
                    width: 2.4rem;
                    height: 2.4rem;
                    background: none;
                    border: none;
                    color: #666;
                    cursor: pointer;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.3s ease;
                    z-index: 1;
                    pointer-events: all;
                }

                .search-submit .dashicons {
                    font-size: 1.2rem;
                    width: 1.2rem;
                    height: 1.2rem;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                }

                .search-submit:hover {
                    color: #C5A87B;
                }

                @media screen and (max-width: 782px) {
                    body.wp-admin.toplevel_page_vime-produtos .header-container {
                        flex-direction: column;
                        gap: 1rem;
                    }

                    .search-form {
                        flex: 0 0 100%;
                    }
                }

                /* Estilo da tabela */
                .table-responsive {
                    margin-bottom: 1rem;
                }

                @media screen and (max-width: 768px) {
                    .table-responsive {
                        overflow-x: auto;
                        -webkit-overflow-scrolling: touch;
                        width: 100%;
                    }

                    .wp-list-table {
                        min-width: 580px; /* Largura mínima para garantir layout */
                    }
                }

                .wp-list-table {
                    border: none;
                    border-collapse: collapse;
                    width: 100%;
                    margin-top: 1rem;
                    table-layout: fixed;
                }

                /* Ajuste das larguras das colunas */
                .wp-list-table th:first-child,
                .wp-list-table td:first-child {
                    width: 60px;
                    padding: 8px 4px;
                }

                .wp-list-table th:nth-child(2),
                .wp-list-table td:nth-child(2) {
                    width: auto;
                    padding-left: 16px;
                }

                .wp-list-table th:nth-child(3),
                .wp-list-table td:nth-child(3) {
                    width: 120px;
                    white-space: nowrap;
                }

                .wp-list-table th:last-child,
                .wp-list-table td:last-child {
                    width: 150px;
                }

                /* Botões de ação */
                .actions {
                    text-align: center;
                }

                .actions-wrapper {
                    display: inline-flex;
                    gap: 6px;
                }

                .button-action {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 40px;
                    height: 40px;
                    border-radius: 4px;
                    border: none;
                    cursor: pointer;
                    transition: all 0.2s ease;
                }

                .button-action .dashicons {
                    font-size: 16px;
                    width: 16px;
                    height: 16px;
                }

                .button-action.edit {
                    background-color: #0A0E18;
                    color: white;
                }

                .button-action.edit:hover {
                    background-color: #C5A87B;
                    transform: translateY(-2px);
                }

                .button-action.view {
                    background-color: #8095CA;
                    color: white;
                }

                .button-action.view:hover {
                    background-color: #6477a9;
                    transform: translateY(-2px);
                }

                .button-action.delete {
                    background-color: #dc3545;
                    color: white;
                }

                .button-action.delete:hover {
                    background-color: #c82333;
                    transform: translateY(-2px);
                }

                /* Container principal */
                .vime-form-section {
                    background: white;
                    padding: 1.5rem;
                    border-radius: 6px;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
                }

                .tablenav {
                    margin: 1.5rem 0 0;
                    height: auto;
                    display: flex;
                    justify-content: flex-end;
                }

                .tablenav-pages {
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                }

                .pagination-links {
                    display: flex;
                    gap: 5px;
                }

                .pagination-links a,
                .pagination-links span {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 30px;
                    height: 30px;
                    padding: 0 5px;
                    background: #fff;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    text-decoration: none;
                    color: #0A0E18;
                    transition: all 0.2s ease;
                }

                .pagination-links span.current {
                    background: #0A0E18;
                    border-color: #0A0E18;
                    color: white;
                }

                .pagination-links a:hover {
                    background: #C5A87B;
                    border-color: #C5A87B;
                    color: white;
                }

                .displaying-num {
                    color: #666;
                    font-size: 13px;
                }
                </style>

            <?php else : ?>
                <p>Nenhum produto encontrado.</p>
            <?php 
            endif;
            wp_reset_postdata();
            ?>
        </div>
    </div>
    <?php
}

// Página de adicionar/editar produto
function vime_adicionar_produto_page() {
    if (isset($_POST['vime_submit_produto'])) {
        check_admin_referer('vime_produto_nonce');
        
        $post_data = array(
            'post_title' => sanitize_text_field($_POST['titulo']),
            'post_content' => wp_kses_post($_POST['descricao']),
            'post_type' => 'produto',
            'post_status' => 'publish'
        );

        // Verifica se está editando ou criando novo
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        
        if ($post_id > 0) {
            // Editando produto existente
            $post_data['ID'] = $post_id;
            $post_id = wp_update_post($post_data);
        } else {
            // Criando novo produto
            $post_id = wp_insert_post($post_data);
        }

        if (!is_wp_error($post_id)) {
            // Salva os meta dados
            update_post_meta($post_id, 'produto_preco', sanitize_text_field($_POST['preco']));
            
            // Salva as categorias
            if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
                $categorias = array_map('intval', $_POST['categorias']);
                wp_set_object_terms($post_id, $categorias, 'category');
            }

            // Salva as imagens
            if (!empty($_POST['produto_imagens'])) {
                $imagens = array_slice(array_map('intval', $_POST['produto_imagens']), 0, 5);
                update_post_meta($post_id, 'produto_galeria', $imagens);
                
                if (!has_post_thumbnail($post_id) && !empty($imagens[0])) {
                    set_post_thumbnail($post_id, $imagens[0]);
                }
            }

            // Em vez do redirecionamento automático, mostra mensagem de sucesso
            ?>
            <div class="notice-custom">
                <div class="notice-content">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <div class="notice-text">
                        <h3>Produto <?php echo $post_id > 0 ? 'atualizado' : 'cadastrado'; ?> com sucesso!</h3>
                        <p>O que você deseja fazer agora?</p>
                    </div>
                </div>
                <div class="notice-actions">
                    <a href="<?php echo admin_url('admin.php?page=vime-produtos'); ?>" class="button-action view">
                        <span class="dashicons dashicons-list-view"></span>
                        Ver todos os produtos
                    </a>
                    <?php if ($post_id == 0) : ?>
                    <a href="<?php echo admin_url('admin.php?page=vime-adicionar-produto'); ?>" class="button-action edit">
                        <span class="dashicons dashicons-plus"></span>
                        Cadastrar novo produto
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo get_permalink($post_id); ?>" class="button-action preview" target="_blank">
                        <span class="dashicons dashicons-visibility"></span>
                        Visualizar produto
                    </a>
                </div>
            </div>

            <style>
            .notice-custom {
                background: white;
                border-radius: 8px;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                margin: 20px 20px 0 0;
                padding: 20px;
                overflow: hidden;
                box-sizing: border-box;
            }

            .notice-content {
                display: flex;
                align-items: center;
                gap: 15px;
                margin-bottom: 20px;
            }

            .notice-content .dashicons-yes-alt {
                font-size: 30px;
                width: 30px;
                height: 30px;
                color: #4CAF50;
            }

            .notice-text h3 {
                margin: 0 0 5px 0;
                color: #0A0E18;
                font-size: 18px;
            }

            .notice-text p {
                margin: 0;
                color: #666;
                font-size: 14px;
            }

            .notice-actions {
                display: flex;
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }

            .notice-actions .button-action {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 8px 16px;
                border-radius: 6px;
                text-decoration: none;
                transition: all 0.3s ease;
                font-size: 14px;
                height: 42px;
                width: 100%;
                box-sizing: border-box;
            }

            .notice-actions .button-action .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
            }

            .notice-actions .view {
                background: #0A0E18;
                color: white;
            }

            .notice-actions .view:hover {
                background: #C5A87B;
                transform: translateY(-2px);
            }

            .notice-actions .edit {
                background: #8095CA;
                color: white;
            }

            .notice-actions .edit:hover {
                background: #6477a9;
                transform: translateY(-2px);
            }

            .notice-actions .preview {
                background: #4CAF50;
                color: white;
            }

            .notice-actions .preview:hover {
                background: #3d8b40;
                transform: translateY(-2px);
            }

            @media screen and (min-width: 601px) {
                .notice-actions {
                    flex-direction: row;
                    flex-wrap: wrap;
                    gap: 10px;
                }

                .notice-actions .button-action {
                    flex: 1;
                    min-width: 0; /* Permite que o botão encolha se necessário */
                }
            }

            @media screen and (max-width: 600px) {
                .notice-custom {
                    margin: 15px 15px 0 0;
                    padding: 15px;
                }

                .notice-content {
                    flex-direction: column;
                    text-align: center;
                }

                .notice-text {
                    text-align: center;
                }
            }
            </style>
            <?php
        }
    }

    // Carrega dados do produto se estiver editando
    $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
    $is_editing = $post_id > 0;
    $produto = $is_editing ? get_post($post_id) : null;
    $categorias = get_categories(array('hide_empty' => false));
    ?>
    <div class="wrap">
        <h1><?php echo $is_editing ? 'Editar Produto' : 'Adicionar Novo Produto'; ?></h1>
        
        <div class="vime-form-section">
            <form method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('vime_produto_nonce'); ?>
                <div class="form-group">
                    <label for="titulo">Título do Produto <span class="required">*</span></label>
                    <input type="text" 
                           id="titulo" 
                           name="titulo" 
                           value="<?php echo $is_editing ? esc_attr($produto->post_title) : ''; ?>" 
                           placeholder="Digite o nome do produto" 
                           required>
                </div>

                <div class="form-group">
                    <label for="descricao">Características do Produto <span class="required">*</span></label>
                    <textarea id="descricao" 
                              name="descricao" 
                              rows="5" 
                              placeholder="Liste as características do produto, uma por linha"
                              required><?php echo $is_editing ? esc_textarea($produto->post_content) : ''; ?></textarea>
                    <p class="description">Digite cada característica em uma nova linha</p>
                </div>

                <div class="form-group">
                    <label for="preco">Preço <span class="required">*</span></label>
                    <input type="text" 
                           id="preco" 
                           name="preco" 
                           value="<?php echo $is_editing ? esc_attr(get_post_meta($post_id, 'produto_preco', true)) : ''; ?>"
                           placeholder="Ex: 199.90"
                           required>
                </div>

                <div class="form-group">
                    <label for="categorias">Categorias</label>
                    <div class="categorias-container">
                        <?php foreach ($categorias as $cat) : 
                            $is_selected = $is_editing && has_category($cat->term_id, $post_id);
                        ?>
                            <label class="categoria-item">
                                <input type="checkbox" name="categorias[]" 
                                       value="<?php echo $cat->term_id; ?>"
                                       <?php echo $is_selected ? 'checked' : ''; ?>>
                                <span><?php echo $cat->name; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Imagens do Produto <span class="required">*</span></label>
                    <div id="imagens-container" class="imagens-grid">
                        <?php 
                        if ($is_editing && !empty($galeria)) {
                            foreach ($galeria as $image_id) {
                                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                if ($image_url) {
                                    ?>
                                    <div class="imagem-item">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="">
                                        <input type="hidden" name="produto_imagens[]" value="<?php echo $image_id; ?>">
                                        <button type="button" class="remover-imagem">×</button>
                                    </div>
                                    <?php
                                }
                            }
                        }
                        ?>
                        <button type="button" id="adicionar-imagem" class="adicionar-imagem">
                            <span>+</span>
                            <span>Adicionar Imagem</span>
                        </button>
                    </div>
                    <input type="hidden" id="tem-imagens" name="tem_imagens" required>
                    <p class="description">Arraste as imagens para reordenar. A primeira imagem será a imagem principal do produto.</p>
                </div>

                <input type="hidden" name="vime_submit_produto" value="1">
                <button type="submit" class="btn-primary">
                    <?php echo $is_editing ? 'Atualizar' : 'Publicar'; ?>
                    <img src="<?php echo get_template_directory_uri(); ?>/img/Arrow rigth.svg" alt="">
                </button>
            </form>
        </div>
    </div>

    <style>
    .vime-form-section {
        background: white;
        padding: 2rem;
        border-radius: 0.8rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--navy-Blue, #0A0E18);
    }
    .form-group input[type="text"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #E3E6EC;
        border-radius: 0.6rem;
        font-size: 1rem;
    }
    .imagens-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .imagem-item {
        position: relative;
        border: 1px solid #E3E6EC;
        border-radius: 0.6rem;
        overflow: hidden;
    }
    .imagem-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    .remover-imagem {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .adicionar-imagem {
        height: 150px;
        border: 2px dashed #E3E6EC;
        border-radius: 0.6rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        background: none;
        color: #8095CA;
    }
    .adicionar-imagem:hover {
        border-color: var(--soft-gold, #C5A87B);
        color: var(--soft-gold, #C5A87B);
    }
    .categorias-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 0.5rem;
    }
    .categoria-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f5f5f5;
        border-radius: 0.6rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .categoria-item:hover {
        background: #E3E6EC;
    }
    .categoria-item input[type="checkbox"] {
        display: none;
    }
    .categoria-item input[type="checkbox"]:checked + span {
        color: var(--soft-gold, #C5A87B);
        font-weight: 500;
    }
    .btn-primary {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        background-color: #0A0E18;
        color: #ffffff;
        padding: 0.6rem 2rem;
        border-radius: 8px;
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background-color: var(--soft-gold, #C5A87B);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .btn-primary img {
        width: 16px;
        height: 16px;
        filter: brightness(0) invert(1);
    }
    .required {
        color: #dc3545;
        margin-left: 4px;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Função para atualizar o campo hidden com as tags selecionadas
        function atualizarTagsSelecionadas() {
            var tags = [];
            $('.tag-item.selected').each(function() {
                tags.push($(this).data('tag-name'));
            });
            $('#tags-selecionadas').val(tags.join(','));
        }

        // Click em tags existentes
        $('.tags-existentes').on('click', '.tag-item', function() {
            $(this).toggleClass('selected');
            atualizarTagsSelecionadas();
        });

        // Adicionar novas tags
        $('#nova-tag').on('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                var valor = $(this).val().trim();
                
                if (valor) {
                    // Verifica se a tag já existe
                    var tagExiste = false;
                    $('.tag-item').each(function() {
                        if ($(this).data('tag-name').toLowerCase() === valor.toLowerCase()) {
                            $(this).addClass('selected');
                            tagExiste = true;
                            return false;
                        }
                    });

                    // Se não existe, cria nova tag
                    if (!tagExiste) {
                        var novaTag = $('<span>', {
                            'class': 'tag-item selected',
                            'data-tag-name': valor,
                            text: valor
                        });
                        $('.tags-existentes').append(novaTag);
                    }

                    $(this).val('');
                    atualizarTagsSelecionadas();
                }
            }
        });

        // Inicializa o campo hidden com as tags já selecionadas
        atualizarTagsSelecionadas();
    });
    </script>
    <?php
}

// Adicione esta função no início do arquivo
function vime_admin_enqueue_scripts($hook) {
    if ('admin_page_vime-adicionar-produto' === $hook || 'produtos_page_vime-adicionar-produto' === $hook) {
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        
        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('#adicionar-imagem').on('click', function(e) {
                    e.preventDefault();
                    
                    var imagensRestantes = 5 - $('.imagem-item').length;
                    if (imagensRestantes <= 0) {
                        alert('Máximo de 5 imagens permitido');
                        return;
                    }

                    var frame = wp.media({
                        title: 'Selecionar Imagens',
                        button: {
                            text: 'Usar estas imagens'
                        },
                        multiple: true,
                        library: {
                            type: 'image'
                        }
                    });

                    frame.on('select', function() {
                        var selection = frame.state().get('selection');
                        var attachments = [];
                        
                        selection.each(function(attachment) {
                            attachments.push(attachment.toJSON());
                        });

                        attachments = attachments.slice(0, imagensRestantes);

                        attachments.forEach(function(attachment) {
                            var novaImagem = $('<div class="imagem-item">' +
                                '<img src="' + attachment.url + '" alt="">' +
                                '<input type="hidden" name="produto_imagens[]" value="' + attachment.id + '">' +
                                '<button type="button" class="remover-imagem">×</button>' +
                                '</div>');
                            
                            novaImagem.insertBefore('#adicionar-imagem');
                        });

                        if ($('.imagem-item').length >= 5) {
                            $('#adicionar-imagem').hide();
                        }
                    });

                    frame.open();
                });

                // Remover imagem
                $(document).on('click', '.remover-imagem', function() {
                    $(this).parent('.imagem-item').remove();
                    $('#adicionar-imagem').show();
                });

                // Tornar as imagens ordenáveis
                $('.imagens-grid').sortable({
                    items: '.imagem-item',
                    cursor: 'move',
                    opacity: 0.7
                });

                // Gerenciamento de tags
                $('.tag-item').on('click', function() {
                    $(this).toggleClass('selected');
                    atualizarTagsSelecionadas();
                });

                $('#nova-tag').on('keyup', function(e) {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        var valor = $(this).val().replace(/,/g, '').trim();
                        if (valor) {
                            $('.tags-existentes').append(
                                '<span class="tag-item selected" data-tag-name="' + valor + '">' + 
                                valor + '</span>'
                            );
                            $(this).val('');
                            atualizarTagsSelecionadas();
                        }
                    }
                });

                function atualizarTagsSelecionadas() {
                    var tags = [];
                    $('.tag-item.selected').each(function() {
                        tags.push($(this).data('tag-name'));
                    });
                    $('#tags-selecionadas').val(tags.join(','));
                }
            });
            </script>
            <?php
        });
    }
}
add_action('admin_enqueue_scripts', 'vime_admin_enqueue_scripts');

// Adicione esta função para ser chamada na ativação do tema/plugin
function produto_rewrite_flush() {
    registrar_cpt_produto();
    add_option('produto_needs_rewrite_flush', true);
}
