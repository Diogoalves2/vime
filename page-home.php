<?php
// Template name: Home
get_header(); ?>

  <main>
    <section class="hero">
      <?php 
      $imagem_hero = get_field('imagem_hero');
      if (!empty($imagem_hero)) {
        $imagem_url = wp_get_attachment_image_src($imagem_hero, 'full');
        if ($imagem_url) {
          $imagem_src = $imagem_url[0];
        }
      }
      ?>
      <img src="<?php echo esc_url($imagem_src ?? get_template_directory_uri() . '/img/img-hero.webp'); ?>" 
           alt="<?php echo esc_attr(get_field('titulo')); ?>">
      <div class="overlay"></div>
      <div class="container">
          <h1><?php echo get_field('titulo'); ?></h1>
          <p><?php echo get_field('apoio'); ?></p>
          <a href="<?php echo get_field('link_do_botao'); ?>"><?php echo get_field('botao_hero'); ?></a>
      </div>
    </section>

    <section class="brands">
      <div class="container">
        <img class="logo-brand" src="<?php echo get_template_directory_uri() ?>/img/grupo_vp_logo_branca.webp" alt="Marcas">
        <img class="logo-brands" src="<?php echo get_template_directory_uri() ?>/img/brands-vp.webp" alt="">
      </div>
    </section>

    <section class="collections">
      <div class="container">
        <div class="header-collections">
          <h2>Nossas Coleções</h2>
          <p>O que há de mais sofisticado e moderno para
            ambientes elegantes</p>
        </div>
        <div class="content-collections">
          <ul>
            <?php
            // Query para produtos da categoria Coleções
            $args = array(
                'post_type' => 'produto',
                'posts_per_page' => 6,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'category',
                        'field'    => 'name', // Alterado para buscar pelo nome exato
                        'terms'    => 'Coleções' // Nome exato da categoria com acentuação
                    ),
                )
            );
            
            // Debug da query
            error_log('Query Coleções: ' . print_r($args, true));
            
            $colecoes = new WP_Query($args);
            
            if ($colecoes->have_posts()) :
                while ($colecoes->have_posts()) : $colecoes->the_post();
            ?>
                <li>
                  <a href="<?php the_permalink(); ?>">
                    <?php 
                    if (has_post_thumbnail()) {
                        the_post_thumbnail('produto-square-large');
                    }
                    ?>
                  </a>
                  <div class="content-card">
                    <h3><?php the_title(); ?></h3>
                    <button onclick="window.location.href='<?php the_permalink(); ?>'">Ver coleção</button>
                  </div>
                </li>
            <?php
                endwhile;
                wp_reset_postdata();
            else:
                // Debug se não encontrar posts
                error_log('Nenhum produto encontrado na categoria Coleções');
            endif;
            ?>
          </ul>
        </div>
      </div>
    </section>

    <section class="promo">
      <div class="container">
        <?php 
        // Debug para ver o que está vindo do campo
        error_log('Banner 01: ' . print_r(get_field('banner_01', false), true));
        error_log('Banner 2: ' . print_r(get_field('banner_2', false), true));

        // Pegar as imagens - tentar diferentes métodos
        $banner_1 = get_field('banner_01');
        $banner_2 = get_field('banner_2');

        // Se for array (formato padrão do ACF para imagens)
        if(is_array($banner_1)) {
            $banner_1_url = $banner_1['url'];
        } 
        // Se for ID da imagem
        else if(is_numeric($banner_1)) {
            $banner_1_url = wp_get_attachment_url($banner_1);
        }
        // Se já for URL
        else {
            $banner_1_url = $banner_1;
        }

        // Mesmo tratamento para banner 2
        if(is_array($banner_2)) {
            $banner_2_url = $banner_2['url'];
        } 
        else if(is_numeric($banner_2)) {
            $banner_2_url = wp_get_attachment_url($banner_2);
        }
        else {
            $banner_2_url = $banner_2;
        }
        ?>
        
        <?php if($banner_1_url): ?>
          <a href="#">
            <img src="<?php echo esc_url($banner_1_url); ?>" alt="Promo">
          </a>
        <?php endif; ?>

        <?php if($banner_2_url): ?>
          <a href="#">
            <img src="<?php echo esc_url($banner_2_url); ?>" alt="Promo">
          </a>
        <?php endif; ?>
      </div>
    </section>

    <section class="more-visions">
      <div class="container">
        <div class="header-visions">
          <h2>Os mais desejados</h2>
          <p>O que há de mais sofisticado e moderno para
            ambientes elegantes</p>
        </div>
        <div class="content-visions">
          <ul>
            <?php
            $args = array(
                'post_type' => 'produto',
                'posts_per_page' => 12,
                'meta_key' => 'produto_views',
                'orderby' => 'meta_value_num',
                'order' => 'DESC'
            );
            
            $popular_products = new WP_Query($args);
            
            if ($popular_products->have_posts()) :
                while ($popular_products->have_posts()) : $popular_products->the_post();
                    $product_price = get_post_meta(get_the_ID(), 'produto_preco', true);
            ?>
                <li class="product-item">
                    <a href="<?php the_permalink(); ?>" class="product-link">
                         <?php 
                         if (has_post_thumbnail()) {
                             the_post_thumbnail('produto-thumbnail');
                         }
                         ?>
                         <div class="content-card-visions">
                             <h3><?php the_title(); ?></h3>
                             <?php if (!empty($product_price)): ?>
                                 <span class="price">R$ <?php echo number_format(floatval($product_price), 2, ',', '.'); ?></span>
                             <?php endif; ?>
                         </div>
                    </a>
                </li>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
          </ul>
          <a href="<?php echo home_url('/produtos'); ?>" class="btn-primary">Ver todos</a>
        </div>
      </div>
    </section>

    <section class="card-spansors">
      <div class="container">
        <div class="card-spansors-one">
          <div class="content-card">
            <h4><?php echo get_field('titulo_banner_01'); ?></h4>
            <span><?php echo get_field('texto_de_apoio_01'); ?></span>
            <a href="<?php echo get_field('link_banner_01'); ?>" class="btn-secondary">Ver Peças</a>
          </div>
          <?php display_acf_image('imagem_banner_01'); ?>
        </div>
        <div class="card-spansors-two">
          <div class="content-card">
            <h4><?php echo get_field('titulo_banner_02'); ?></h4>
            <span><?php echo get_field('texto_de_apoio_02'); ?></span>
            <a href="<?php echo get_field('link_banner_02'); ?>" class="btn-secondary">Ver Peças</a>
          </div>
          <?php display_acf_image('imagem_banner_2'); ?>
        </div>
        <div class="card-spansors-thre">
          <div class="content-card">
            <h4><?php echo get_field('titulo_banner_3'); ?></h4>
            <span><?php echo get_field('texto_de_apoio_3'); ?></span>
            <a href="<?php echo get_field('link_banner_3'); ?>" class="btn-secondary">Ver Peças</a>
          </div>
          <?php display_acf_image('imagem_banner_3'); ?>
        </div>
      </div>
    </section>

    <section id="about" class="about">
      <div class="container">
        <div class="area-left">
          <div class="header-about">
            <h2>Sobre a nossa Empresa</h2>
            <p class="text-about">Conheça um pouco mais sobre a nossa empresa</p>
          </div>
          <p>A Vime Conceito atua no mercado Paraibano desde 2013, no segmento de
            fabricação, revenda e prestação de serviços no segmento de móveis
           externos. Especificamente, voltado a Condomínios, Restaurantes e Hotéis.
          </p><br>
          <p>Disponibilizamos serviços de manutenção e reforma de móveis de área
            externa com foco na qualidade, preço baixo e satisfação dos nossos cliente.
          </p><br>
          <p>Somos especialista em soluções completas para Condomínios, Hotéis, Bares
            e Restaurantes, na revenda, manutenção e reforma de móveis voltados
            para áreas externas, com vasta experiência no mercado.
          </p>
        </div>
        <div class="area-right">
          <img src="<?php echo get_template_directory_uri() ?>/img/img-about.webp" alt="">
        </div>
    </section>

    <section id="faq" class="faq">
      <div class="container">
          <div class="header-faq">
            <h2>Dúvidas Frequentes</h2>
            <p>Tire suas principais dúvidas sobre nossos produtos</p>
          </div>
          <div class="faq-accordion">
            <?php 
            $faq_items = get_field('faqhome');
            
            if($faq_items): 
              foreach($faq_items as $item):
                $pergunta = $item['pergunta'];
                $resposta = $item['resposta'];
            ?>
              <div class="faq-item">
                <button class="faq-question"><?php echo esc_html($pergunta); ?></button>
                <div class="faq-answer">
                  <p><?php echo wp_kses_post($resposta); ?></p>
                </div>
              </div>
            <?php 
              endforeach;
            endif;
            ?>
          </div>
      </div>
    </section>

    <section id="contact" class="contact">
      <div class="container">
        <div class="header-contact">
          <h2>Entre em contato conosco</h2>
          <p>Tem alguma dúvida, sugestão ou elogio? Fale conosco! Sua opinião é muito importante para continuarmos oferecendo o melhor para você.</p>
        </div>
        <div class="infos">
          <ul>
            <?php 
            $contato_items = get_field('infos_contato');
            
            if($contato_items): 
              foreach($contato_items as $item):
                // Pegar o nome do campo e dados com verificação
                $nome = $item['nome'] ?? '';
                $dados_info = $item['dados_info'] ?? '';
                
                // Pegar o ícone do ACF
                $icone = '';
                if (!empty($item['icone'])) {
                  // Pegar a URL da imagem usando o ID
                  $icone_array = wp_get_attachment_image_src($item['icone'], 'full');
                  if ($icone_array) {
                    $icone = $icone_array[0];
                  }
                }
                
                // Ícone padrão caso nenhum seja fornecido
                if (empty($icone)) {
                  $icone = get_template_directory_uri() . '/img/default-icon.svg';
                }
            ?>
              <li>
                <img src="<?php echo esc_url($icone); ?>" 
                     alt="<?php echo esc_attr($nome); ?>"
                     class="info-icon"
                >
                <div class="infos-text">
                  <h3><?php echo esc_html($nome); ?></h3>
                  <span><?php echo esc_html($dados_info); ?></span>
                </div>
              </li>
            <?php 
              endforeach;
            endif;
            ?>
          </ul>
        </div>
        <div class="form-contact">
          <div class="area-left">
            <form method="POST" 
                  class="form-contato"
                  onsubmit="return validateForm(event)">
                
                <input type="hidden" name="action" value="processar_formulario_contato">
                <?php wp_nonce_field('formulario_contato_nonce', 'formulario_nonce'); ?>
                
                <div class="grupo-campos">
                    <input type="text" 
                           name="nome" 
                           placeholder="Nome" 
                           required 
                           minlength="3"
                           pattern="[A-Za-zÀ-ÿ\s]{3,}"
                           title="Digite um nome válido">
                           
                    <input type="email" 
                           name="email" 
                           placeholder="Email" 
                           required
                           title="Digite um email válido">
                </div>
                
                <div class="grupo-campos">
                    <input type="tel" 
                           name="telefone" 
                           placeholder="Telefone" 
                           required
                           title="Digite um telefone válido: (99)99999-9999"
                           oninput="maskTelefone(this)">
                           
                    <input type="text" 
                           name="empresa" 
                           placeholder="Empresa">
                </div>
                
                <textarea name="mensagem" 
                          placeholder="Digite sua mensagem..." 
                          required
                          minlength="10"></textarea>
                
                <button type="submit" id="submitBtn">
                    Enviar Mensagem
                    <img src="<?php echo get_template_directory_uri() ?>/img/Arrow rigth.svg" alt="">
                </button>

                <!-- Mensagens de erro/sucesso -->
                <?php if (isset($_GET['status'])): ?>
                    <div class="form-message <?php echo $_GET['status']; ?>">
                        <?php 
                        if (isset($_GET['errors'])) {
                            $errors = json_decode(urldecode($_GET['errors']));
                            foreach ($errors as $error) {
                                echo '<p>' . esc_html($error) . '</p>';
                            }
                        } else {
                            echo '<p>' . esc_html($_GET['message']) . '</p>'; 
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </form>
          </div>
          <div class="area-right">
            <h4>Quer entrar em contato diretamente?</h4>
            <span>Estamos a disposição para atendê-lo! Entre em contato conosco pelo WhatsApp.</span>
            <a href="<?php echo get_field('btn_whatsapp'); ?>" class="btn-primary">
              <img src="<?php echo get_template_directory_uri() ?>/img/whatsapp-icon.svg" alt="">
              Falar com vendedor
            </a>
            <div class="redes">
              <h5>Nossas nas redes </h5>
              <div class="icon-redes">
                <a href="<?php echo get_field('bnt_face'); ?>"><img src="<?php echo get_template_directory_uri() ?>/img/Facebook-icon.svg" alt="">Facebook</a>
                <a href="<?php echo get_field('btn_instagram'); ?>"><img src="<?php echo get_template_directory_uri() ?>/img/Instagram-icon.svg" alt="">Instagram</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

<?php get_footer(); ?>
  