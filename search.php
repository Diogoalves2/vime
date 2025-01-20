<?php get_header(); ?>

<main>
    <section class="search-results">
        <div class="container">
            <h1>Resultados da busca</h1>
            <p>Você está procurando por: <?php echo get_search_query(); ?></p>

            <?php if (have_posts()) : ?>
                <ul>
                    <?php while (have_posts()) : the_post(); ?>
                        <li>
                            <a href="<?php the_permalink(); ?>">
                                <img src="<?php echo get_the_post_thumbnail_url(null, 'thumbnail');?>" alt="<?php the_title();?>">
                                <div class="info">
                                    <h2><?php the_title(); ?></h2>
                                    <p><?php the_excerpt(); ?></p>
                                </div>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else : ?>
                <p>Nenhum resultado encontrado.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>