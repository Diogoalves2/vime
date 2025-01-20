<?php 
/*
Template Name: Página Institucional
*/
get_header(); 
?>

<main>
    <section class="breadcrumb">
        <div class="container">
            <ul>
                <li><a href="<?php echo home_url(); ?>">Home</a></li>
                <li><?php the_title(); ?></li>
            </ul>
        </div>
    </section>

    <section class="page-institucional">
        <div class="container">
            <div class="title-page">
                <h1><?php the_title(); ?></h1>
            </div>
            <div class="content-page">
                <?php 
                if (have_posts()) : while (have_posts()) : the_post();
                    the_content();
                endwhile; endif; 
                ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?> 