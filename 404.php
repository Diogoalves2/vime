<?php
get_header(); ?>

<main class="container">
    <section class="error-404">
        <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>
        <dotlottie-player src="https://lottie.host/3400f55c-ee0b-4fec-96ce-1a670a97d1fa/WG9pDYkEOe.lottie" background="transparent" speed="1" style="width: 200px; height: 200px" loop autoplay></dotlottie-player>
        <h1>Erro 404: Página não encontrada</h1>
        <p>Desculpe, mas a página que você está procurando não existe.</p>
        <p>Você pode tentar procurar novamente ou voltar para a página inicial.</p>
        <a href="<?php echo home_url(); ?>" class="btn btn-primary">Página Inicial</a>
    </section>
</main>

<?php get_footer(); ?>