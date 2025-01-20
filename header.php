<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/plugins.css">
  <link rel="stylesheet" href="<?php echo get_template_directory_uri() ?>/css/main.css">
  
  <title><?php wp_title('|', true, 'right'); ?></title>
  <meta name="title" content="Vime Conceito — Encontre móveis de alta qualidade como cadeiras, espreguiçadeiras e poltronas. Feitos com tricô, corda náutica e alumínio, perfeitos para ambientes externos e internos." />
  <meta name="description" content="Descubra móveis sofisticados com design único. Materiais de alta qualidade como alumínio e tricô náutico. Explore nossa coleção!" />

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="vimeconceito.com.br" />
  <meta property="og:title" content="Vime Conceito — Encontre móveis de alta qualidade como cadeiras, espreguiçadeiras e poltronas. Feitos com tricô, corda náutica e alumínio, perfeitos para ambientes externos e internos." />
  <meta property="og:description" content="Descubra móveis sofisticados com design único. Materiais de alta qualidade como alumínio e tricô náutico. Explore nossa coleção!" />
  <meta property="og:image" content="<?php echo get_template_directory_uri(); ?>/share-og.jpg" />

  
  <!-- Favicon -->
  <link rel="icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico" type="image/x-icon">
  <link rel="shortcut icon" href="<?php echo get_template_directory_uri(); ?>/favicon.ico" type="image/x-icon">

  <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>

  <header class="header">
    <div class="container">
     <div class="content-header">

      <a href="<?php echo home_url(); ?>"><img src="<?php echo get_template_directory_uri() ?>/img/Logo Vime.svg" alt="Vime Conceito"></a>
      
      <form role="search" method="get" class="search-form" action="<?php echo home_url('/'); ?>">
        <input type="search" 
               class="search-field" 
               placeholder="Buscar produto..." 
               value="<?php echo get_search_query(); ?>" 
               name="s"
               autocomplete="off"
        >
        <input type="hidden" name="post_type" value="produto">
        <button type="submit" class="search-submit">
          buscar
          <img src="<?php echo get_template_directory_uri() ?>/img/search-icon.svg" alt="icone-busca">
        </button>
      </form>
      
      <div class="nav-contact">
        <a href="#about">Sobre a Empresa</a>
        <a href="#faq">faq</a>
        <a class="btn-contact" href="#">Contato</a>
      </div>
      <button class="menu-mobile">
        <span></span>
        <span></span>
        <span></span>
      </button>
     </div>
    </div>
    <div class="nav-menu">
      <div class="container">
        <nav>
          <?php wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_class' => 'nav-menu',
                'menu_id' => 'nav-menu'
            )); ?>
         </nav>
      </div>
    </div>

    <nav class="mobile-menu">
        <form role="search" method="get" class="search-form" action="<?php echo home_url('/'); ?>">
            <input type="search" 
                   class="search-field" 
                   placeholder="Buscar produto..." 
                   value="<?php echo get_search_query(); ?>" 
                   name="s"
                   autocomplete="off"
            >
            <input type="hidden" name="post_type" value="produto">
            <button type="submit" class="search-submit">
                buscar
                <img src="<?php echo get_template_directory_uri() ?>/img/search-icon.svg" alt="icone-busca">
            </button>
        </form>
        <ul>
            <?php
            // Buscar todas as categorias existentes que têm produtos
            $categories = get_categories(array(
                'hide_empty' => false, // Mostrar todas as categorias, mesmo vazias
                'taxonomy' => 'category'
            ));

            foreach ($categories as $category) {
                echo '<li><a href="' . get_category_link($category->term_id) . '">' . 
                         strtoupper($category->name) . '</a></li>';
            }
            ?>
        </ul>
        <div class="nav-contact">
            <a href="/#about">Sobre a Empresa</a>
            <a href="/#faq">FAQ</a>
            <a class="btn-contact" href="#">Contato</a>
        </div>
    </nav>

  </header>