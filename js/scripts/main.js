// FAQ Accordion Functionality
document.addEventListener('DOMContentLoaded', function() {
  const faqItems = document.querySelectorAll('.faq-item');

  faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');
    const answer = item.querySelector('.faq-answer');
    
    question.addEventListener('click', () => {
      // Close all other items
      faqItems.forEach(otherItem => {
        if (otherItem !== item && otherItem.classList.contains('active')) {
          const otherAnswer = otherItem.querySelector('.faq-answer');
          otherAnswer.style.opacity = '0';
          otherAnswer.style.transform = 'translateY(-20px)';
          
          otherAnswer.style.maxHeight = '0';
          
          otherItem.classList.remove('active');
        }
      });

      // Toggle current item
      if (item.classList.contains('active')) {
        answer.style.opacity = '0';
        answer.style.transform = 'translateY(-20px)';
        
        answer.style.maxHeight = '0';
        
        item.classList.remove('active');
      } else {
        const height = answer.scrollHeight;
        answer.style.maxHeight = height + 'px';
        
        answer.style.opacity = '1';
        answer.style.transform = 'translateY(0)';
        
        item.classList.add('active');
      }
    });
  });
});

jQuery(document).ready(function($) {
    // Código do menu mobile
    $('.menu-mobile').on('click', function() {
        $(this).toggleClass('active');
        $('.mobile-menu').toggleClass('active');
        $('.overlay').toggleClass('active');
        $('body').toggleClass('menu-opened');
    });

    // Fechar menu ao clicar em qualquer link na navegação mobile ou no overlay
    $('.mobile-menu a, .overlay').on('click', function(e) {
        
        // Fecha o menu
        $('.menu-mobile').removeClass('active');
        $('.mobile-menu').removeClass('active');
        $('.overlay').removeClass('active');
        $('body').removeClass('menu-opened');

        // Se for um link com âncora
        if (this.hash) {
            e.preventDefault();
            const target = this.hash;
            const $targetElement = $(target);

            // Se estiver na mesma página e o elemento existir
            if ((window.location.pathname === '/' || window.location.pathname === '/index.php') && $targetElement.length) {
                $('html, body').animate({
                    scrollTop: $targetElement.offset().top - 100
                }, 800);
            } else {
                // Redireciona para a home com a âncora
                window.location.href = '/' + target;
            }
        }
    });
});

// Função para atualizar os filtros
function updateFilters() {
    const checkboxes = document.querySelectorAll('.filter-section input[type="checkbox"]');
    const currentUrl = new URL(window.location.href);
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const paramName = this.name;
            const paramValue = this.value;
            const params = currentUrl.searchParams;
            
            let values = params.getAll(paramName);
            
            if (this.checked) {
                if (!values.includes(paramValue)) {
                    values.push(paramValue);
                }
            } else {
                values = values.filter(v => v !== paramValue);
            }
            
            // Remover parâmetro existente
            params.delete(paramName);
            
            // Adicionar novos valores
            values.forEach(value => {
                params.append(paramName, value);
            });
            
            // Atualizar URL e recarregar página
            window.location.href = currentUrl.toString();
        });
    });
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    updateFilters();
});

// Controle do range de preço
function initPriceRange() {
    const rangeInputs = document.querySelectorAll('.price-range-input');
    const minPrice = document.querySelector('.min-price');
    const maxPrice = document.querySelector('.max-price');
    const minLabel = document.querySelector('.min-label');
    const maxLabel = document.querySelector('.max-label');

    function updatePriceInputs(e) {
        let minVal = parseInt(rangeInputs[0].value);
        let maxVal = parseInt(rangeInputs[1].value);

        if (maxVal - minVal < 10) {
            if (e.target === rangeInputs[0]) {
                rangeInputs[0].value = maxVal - 10;
            } else {
                rangeInputs[1].value = minVal + 10;
            }
        } else {
            minPrice.value = minVal;
            maxPrice.value = maxVal;
            minLabel.textContent = `R$ ${minVal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
            maxLabel.textContent = `R$ ${maxVal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
        }
    }

    rangeInputs.forEach(input => {
        input.addEventListener('input', updatePriceInputs);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initPriceRange();
});