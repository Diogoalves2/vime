<?php get_header(); ?>

<main>
    <section class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="<?php echo home_url(); ?>">Home</a></li>
                <li>Produtos</li>
            </ul>
        </div>
    </section>

    <section class="archive-products">
        <div class="container">
            <div class="title-archive">
                <h1><?php post_type_archive_title(); ?></h1>
                <div class="products-count">
                    <?php
                    global $wp_query;
                    $total_products = $wp_query->found_posts;
                    $showing = $wp_query->post_count;
                    echo '<span>Mostrando ' . $showing . ' produtos de ' . $total_products . '</span>';
                    ?>
                </div>
            </div>

            <div class="archive-wrapper">
                <!-- Sidebar com Filtros -->
                <aside class="archive-filters">
                    <!-- Categorias -->
                    <div class="filter-section">
                        <h3>Categorias</h3>
                        <div class="filter-group">
                            <?php
                            // Pega todas as categorias, incluindo vazias
                            $categories = get_categories(array(
                                'hide_empty' => false, // Mostra todas as categorias
                                'taxonomy' => 'category'
                            ));

                            if (!empty($categories)) {
                                foreach($categories as $category) {
                                    // Conta produtos nesta categoria
                                    $products_in_cat = get_posts(array(
                                        'post_type' => 'produto',
                                        'posts_per_page' => -1,
                                        'category' => $category->term_id
                                    ));

                                    $count = count($products_in_cat);
                                    $checked = isset($_GET['category']) && 
                                              in_array($category->term_id, (array)$_GET['category']) ? 'checked' : '';
                                    
                                    echo '<label class="filter-checkbox">';
                                    echo '<input type="checkbox" name="category[]" value="' . $category->term_id . '" ' . $checked . '>';
                                    echo '<span class="checkmark"></span>';
                                    echo esc_html($category->name);
                                    echo '<span class="count">' . $count . '</span>';
                                    echo '</label>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <!-- Preço -->
                    <div class="filter-section">
                        <h3>Faixa de Preço</h3>
                        <form method="GET" class="price-filter-form">
                            <?php
                            global $wpdb;
                            $price_range = $wpdb->get_row("
                                SELECT 
                                    MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price,
                                    MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price
                                FROM {$wpdb->postmeta}
                                WHERE meta_key = 'produto_preco'
                                AND meta_value != ''
                            ");
                            
                            $min_price = floor($price_range->min_price);
                            $max_price = ceil($price_range->max_price);
                            $current_min = isset($_GET['min_price']) ? $_GET['min_price'] : $min_price;
                            $current_max = isset($_GET['max_price']) ? $_GET['max_price'] : $max_price;
                            ?>
                            
                            <div class="price-range">
                                <input type="range" 
                                       id="price-range" 
                                       min="<?php echo $min_price; ?>" 
                                       max="<?php echo $max_price; ?>" 
                                       step="100"
                                       value="<?php echo $current_min; ?>"
                                >
                                <div class="price-values">
                                    <span>R$ <output id="min-price"><?php echo number_format($current_min, 2, ',', '.'); ?></output></span>
                                    <span>R$ <output id="max-price"><?php echo number_format($current_max, 2, ',', '.'); ?></output></span>
                                </div>
                            </div>

                            <input type="hidden" name="min_price" id="min-price-input" value="<?php echo $current_min; ?>">
                            <input type="hidden" name="max_price" id="max-price-input" value="<?php echo $current_max; ?>">
                            
                            <button type="submit" class="apply-filters">Aplicar Filtros</button>
                        </form>
                    </div>
                </aside>

                <!-- Lista de Produtos -->
                <div class="archive-main">
                    <div class="products">
                        <ul>
                            <?php while (have_posts()) : the_post(); 
                                $product_price = get_post_meta(get_the_ID(), 'produto_preco', true);
                            ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>" class="card-product">
                                        <div class="image">
                                            <?php the_post_thumbnail('produto-square-medium'); ?>
                                        </div>
                                        <div class="info">
                                            <h3><?php the_title(); ?></h3>
                                            <?php if (!empty($product_price)): ?>
                                                <p class="price">R$ <?php echo number_format(floatval($product_price), 2, ',', '.'); ?></p>
                                            <?php endif; ?>
                                            <?php if (has_excerpt()): ?>
                                                <p><?php echo get_the_excerpt(); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="pagination-wrapper">
                <?php
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>',
                    'next_text' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>',
                    'class'     => 'pagination'
                ));
                ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
