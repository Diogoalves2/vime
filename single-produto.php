<?php
// Adicione este debug no início do arquivo
error_log('single-produto.php está sendo carregado');

get_header(); 

// Obtém o ID do produto atual
$product_id = get_the_ID();
$product = get_post($product_id);

// Obtém os dados do produto
$product_features = get_post_meta($product_id, 'produto_caracteristicas', true);
$product_gallery = get_post_meta($product_id, 'produto_galeria', true);
$product_price = get_post_meta($product_id, 'produto_preco', true);
$categories = get_the_terms($product_id, 'category');
$category_name = !empty($categories) ? $categories[0]->name : '';

// Debug
error_log('=== Debug Galeria ===');
error_log('Product ID: ' . $product_id);
error_log('Product Gallery: ' . print_r($product_gallery, true));
error_log('Is Array? ' . (is_array($product_gallery) ? 'Sim' : 'Não'));
error_log('Count: ' . (is_array($product_gallery) ? count($product_gallery) : '0'));
error_log('==================');

// Debug no início do arquivo
error_log('Valor de $product_gallery antes do loop: ' . print_r($product_gallery, true));
?>

<main>
    <section class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="<?php echo home_url(); ?>">Home</a></li>
                <li><a href="<?php echo home_url('/produtos'); ?>">Produtos</a></li>
                <?php if (!empty($category_name)): ?>
                    <li><a href="<?php echo get_term_link(get_term_by('name', $category_name, 'category')); ?>"><?php echo esc_html($category_name); ?></a></li>
                <?php endif; ?>
                <li><a href="#"><?php echo get_the_title(); ?></a></li>
            </ul>
        </div>
    </section>

    <section class="product-content">
        <div class="container">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <div class="area-left">
                    <!-- Imagem Principal -->
                    <div class="img-product-principal">
                        <?php 
                        if (has_post_thumbnail()) {
                            echo wp_get_attachment_image(get_post_thumbnail_id(), 'produto-square-large', false, array(
                                'class' => 'main-product-image'
                            ));
                        }
                        ?>
                    </div>

                    <!-- Miniaturas -->
                    <div class="img-product-gallery">
                        <?php 
                        if (!empty($product_gallery) && is_array($product_gallery)) {
                            foreach ($product_gallery as $image_id) {
                                $thumb_url = wp_get_attachment_image_url($image_id, 'produto-thumbnail');
                                $large_url = wp_get_attachment_image_url($image_id, 'produto-square-large');
                                
                                if ($thumb_url && $large_url) {
                                    // Adicionando debug para verificar os valores
                                    error_log('Image ID: ' . $image_id);
                                    error_log('Large URL: ' . $large_url);
                                    
                                    echo '<a href="#" class="gallery-item" data-large="' . esc_url($large_url) . '">';
                                    echo wp_get_attachment_image($image_id, 'produto-thumbnail');
                                    echo '</a>';
                                }
                            }
                        }
                        ?>
                    </div>
                    
                    <!-- Adicione esta estrutura do popup após a imagem principal -->
                    <div class="popup">
                        <button class="close">
                            <img src="<?php echo get_template_directory_uri(); ?>/img/close.svg" alt="Fechar">
                        </button>
                        <div class="popup-content">
                            <img src="" alt="Imagem ampliada">
                        </div>
                    </div>
                </div>
                <div class="area-right">
                    <div class="info-product">
                        <h1><?php the_title(); ?></h1>
                        <?php if (!empty($product_price)): ?>
                            <div class="product-price">
                                <p>R$ <?php echo number_format(floatval($product_price), 2, ',', '.'); ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="description">
                          <h3>Características</h3>
                            <?php the_content(); ?>
                        </div>
                        <!-- Botão WhatsApp -->
                        <div class="btn-whatsapp">
                            <?php
                            $whatsapp_number = get_field('whatsapp_number', 'option') ?: '558399999999';
                            $product_title = get_the_title();
                            $product_url = get_permalink();
                            $whatsapp_message = "Olá! Gostaria de saber mais sobre o produto: {$product_title}. Link: {$product_url}";
                            $whatsapp_link = "https://wa.me/{$whatsapp_number}?text=" . urlencode($whatsapp_message);
                            ?>
                            <a href="<?php echo esc_url($whatsapp_link); ?>" target="_blank" class="btn-whatsapp">
                                <img src="<?php echo get_template_directory_uri(); ?>/img/whatsapp-icon.svg" alt="WhatsApp">
                                Solicitar orçamento
                            </a>
                        </div>

                        <!-- Compartilhar nas redes sociais -->
                        <div class="social-share">
                            <h4>Compartilhe nas redes sociais</h4>
                            <div class="social-buttons">
                                <?php
                                $share_url = urlencode(get_permalink());
                                $share_title = urlencode(get_the_title());
                                $image_url = '';
                                if (!empty($product_gallery) && is_array($product_gallery)) {
                                    $image_url = $product_gallery[0];
                                }
                                ?>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" 
                                   target="_blank" 
                                   class="facebook">
                                    <img src="<?php echo get_template_directory_uri(); ?>/img/Facebook-icon.svg" alt="Facebook">
                                    <span>Facebook</span>
                                </a>
                                
                                <a href="https://www.instagram.com/share?url=<?php echo $share_url; ?>" 
                                   target="_blank" 
                                   class="instagram">
                                    <img src="<?php echo get_template_directory_uri(); ?>/img/instagram-icon.svg" alt="Instagram">
                                    <span>Instagram</span>
                                </a>
                                
                                <a href="https://pinterest.com/pin/create/button/?url=<?php echo $share_url; ?>&media=<?php echo $image_url; ?>&description=<?php echo $share_title; ?>" 
                                   target="_blank" 
                                   class="pinterest">
                                    <img src="<?php echo get_template_directory_uri(); ?>/img/pinterest-icon.svg" alt="Pinterest">
                                    <span>Pinterest</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; endif; ?>
        </div>
    </section>

    <section class="related-products">
        <div class="container">
            <h2>produtos que você pode gostar</h2>
            <div class="products">
                <ul>
                    <?php
                    // Query para produtos relacionados
                    $args = array(
                        'post_type' => 'produto',
                        'posts_per_page' => 4,
                        'post__not_in' => array($product_id),
                        'orderby' => 'rand'
                    );
                    
                    $related_products = new WP_Query($args);
                    
                    if ($related_products->have_posts()) :
                        while ($related_products->have_posts()) : $related_products->the_post();
                            $related_price = get_post_meta(get_the_ID(), 'produto_preco', true);
                    ?>
                        <li>
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('produto-thumbnail'); ?>
                                <?php endif; ?>
                                <h3><?php the_title(); ?></h3>
                                <?php if (!empty($related_price)): ?>
                                    <span class="price">R$ <?php echo number_format(floatval($related_price), 2, ',', '.'); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                </ul>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
