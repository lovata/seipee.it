addEventListener('render', function() {

    // Auto Collapsed List
    $('ul.bullet-list li.active:first').each(function() {
        $(this).parents('ul.collapse').each(function() {
            $(this).addClass('show').prevAll('.collapse-caret:first').removeClass('collapsed');
        });
    });

    // Popovers
    $('[data-bs-toggle="popover"]').each(function() {
        var $el = $(this);
        if ($el.data('content-target')) {
            $el
                .popover({ html: true, content: $($el.data('content-target')).get(0) })
                .on('shown.bs.popover', function() {
                    $('input:first', $($el.data('content-target'))).focus();
                })
            ;
        }
        else {
            $el.popover();
        }
    });

    // AGGIUNTA CLASSE STICKY ALL'HEADER DOPO LO SCROLL
    window.onload = function(){
        $(window).on("scroll", function() {
            if($(window).scrollTop() > 46) {
                $(".header-main").addClass("sticky");
            } else {
                $(".header-main").removeClass("sticky");
            }
        });
    };

    //MENU
    $('nav ul li').on('mouseenter', function() {
        $(this).children('ul').stop().slideDown();
    });
    
    document.querySelectorAll('nav li').forEach(li => {
        if (li.querySelector(':scope > ul')) {
          li.classList.add('has-children');
        }
      });
    
    // AGGIUNGI VOCI STATICHE NEL MENU PRODOTTI

    const menu = document.querySelector("nav > ul > li:nth-child(2) > ul");

      if (menu) {
        // Primo LI statico
        const firstLi = document.createElement("li");
        firstLi.classList.add("menu-static", "menu-static-1");
        firstLi.innerHTML = "<span>MOTORI ASINCRONI</span>";

        // Quarto LI statico
        const fourthLi = document.createElement("li");
        fourthLi.classList.add("menu-static", "menu-static-4");
        fourthLi.innerHTML = "<span>MOTORI SINCRONI</span>";

        // Inserisci al primo posto
        menu.insertBefore(firstLi, menu.children[0]);

        // Inserisci al quarto posto (dopo che il primo è già stato inserito)
        if (menu.children.length >= 9) {
          menu.insertBefore(fourthLi, menu.children[8]);
        } else {
          menu.appendChild(fourthLi); // fallback in fondo
        }
      }

    //END AGGIUNGI VOCI STATICHE NEL MENU PRODOTTI



    // Nascondi il sottomenu quando il mouse esce dall'elemento <li>
    $('nav ul li').on('mouseleave', function() {
        $(this).children('ul').stop().slideUp();
    });   

    // MENU MOBILE
    $('.open-menu-button').on('click', function(){
        $('.inside-mobile-menu-container').addClass('visible');
    })

    $('.menu-close-button').on('click', function(){
        $('.inside-mobile-menu-container').removeClass('visible');
    })

    $('.menu-mobile-container ul li a').on('click', function(){
        $('.inside-mobile-menu-container').removeClass('visible');
    })
    
    $(".menu-mobile-container ul li").each(function() {
        if ($(this).find('ul').length > 0) {
            $(this).addClass("expandable").prepend("<span class='expand-icon'>+</span>");
        }
    });
    
    // Rimuovi tutti i "+" aggiunti e aggiungi uno solo al posto giusto
    $(".menu-mobile-container ul li").each(function() {
        var $expandIcon = $(this).find('.expand-icon');
        if ($expandIcon.length > 1) {
            $expandIcon.not(':first').remove();
        }
    });
    
    // Rimuovi tutti gli eventi di clic precedenti sugli span con classe expand-icon
    $(".menu-mobile-container .expand-icon").off("click").click(function(e) {
        e.stopPropagation(); // Evita che il click si propaghi agli elementi superiori
    
        var $ul = $(this).parent().children("ul");
        $ul.slideToggle(); // Mostra/nasconde il ul con effetto slide
    
        // Cambia il segno "+" o "-" al clic
        if ($(this).text() === "+") {
            $(this).text("-");
        } else {
            $(this).text("+");
        }
    });

    // When the user scrolls down 800px from the top of the document, show the button
    window.onscroll = function() {
        scrollFunction();
    };
    
    function scrollFunction() {
        const mybutton = document.getElementById("btn-back-to-top");
        const callDiv = document.getElementById("call");

        if (!mybutton || !callDiv) return; // se non esistono, esce

        if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
            mybutton.style.opacity = "1";
            callDiv.classList.add("moved-up");
        } else {
            mybutton.style.opacity = "0";
            callDiv.classList.remove("moved-up");
        }
    }

    // esegui scrollFunction su scroll
    window.addEventListener("scroll", scrollFunction);

    // riprova ad assegnare gli elementi dopo ogni richiesta ajax di october cms
    document.addEventListener('ajaxUpdate', function () {
        scrollFunction(); // ricalcola lo stato dopo un update ajax
    });



    // When the user clicks on the button, scroll to the top of the document
    mybutton.addEventListener("click", backToTop);

    function backToTop() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }

    // end Button scroll to top

});

// ANIMAZIONI
// funzione di animazione  per tutti gli elementi, applicare la classe animate al div che si desidera animare dopo lo scroll di 150 pixel
	function reveal() {
	  var reveals = document.querySelectorAll(".animate");

	  for (var i = 0; i < reveals.length; i++) {
	    var windowHeight = window.innerHeight;
	    var elementTop = reveals[i].getBoundingClientRect().top;
	    var elementVisible = 150;

	    if (elementTop < windowHeight - elementVisible) {
	      reveals[i].classList.add("active");
	    } else {
	      reveals[i].classList.remove("active");
	    }
	  }
	}
	window.addEventListener("scroll", reveal);
// Funzione di animazione per tutti gli elementi, applicare la classe animatenow al div che si desidera animare senza attendere lo scroll
    function revealNow() {
      var owlItems = document.querySelectorAll('.owl-item');

      owlItems.forEach(function (owlItem) {
        if (owlItem.classList.contains('active')) {
          var activeElements = owlItem.querySelectorAll('.animatenow');
          activeElements.forEach(function (activeElement) {
            setTimeout(function () {
              activeElement.classList.add('active');
            }, 500);
          });
        }
      });

      // Rimuovi la classe "active" da tutti gli elementi con classe .animatenow alla fine della funzione
      var activeElements = document.querySelectorAll('.animatenow.active');
      activeElements.forEach(function (element) {
        element.classList.remove('active');
      });
    }

    // Esegui la funzione inizialmente
    revealNow();

    // Esegui la funzione continuamente con un intervallo di 500 millisecondi
    setInterval(revealNow, 500);

//END ANIMAZIONI


window.addEventListener('pageshow', function (event) {
    // Nasconde la barra di caricamento
    oc.progressBar.hide();
    // Reinizializza i plugin solo se serve (es: ritorno dal bfcache)
    if (event.persisted) {
        if (typeof $.mall !== 'undefined') {
            $.mall.init();
        }
    }
});


document.addEventListener("DOMContentLoaded", function () {
  const logoImg = document.querySelector("img#logo");
  const headerMain = document.querySelector(".header-main");

  if (!logoImg || !headerMain) return;

  const updateLogo = (mutationsList) => {
    for (const mutation of mutationsList) {
      if (mutation.type === "attributes" && mutation.attributeName === "class") {
        const isSticky = headerMain.classList.contains("sticky");
        logoImg.src = isSticky ? "/storage/app/media/logo.webp" : "/storage/app/media/logo_nero.webp";
      }
    }
  };

  const observer = new MutationObserver(updateLogo);

  observer.observe(headerMain, {
    attributes: true,
    attributeFilter: ["class"]
  });

  // Check iniziale
  logoImg.src = headerMain.classList.contains("sticky") ? "/storage/app/media/logo.webp" : "/storage/app/media/logo_nero.webp";
});

// Cerchio home page

document.addEventListener("DOMContentLoaded", () => {
  const icons = document.querySelectorAll(".icon");
  const titleEl = document.getElementById("central-title");
  const textEl = document.getElementById("central-text");

  // Imposta configurazioni diverse per desktop e mobile
  const config = window.innerWidth > 768 
    ? { centerX: 250, centerY: 250, radius: 245, offset: 30 } // Desktop
    : { centerX: 180, centerY: 180, radius: 148, offset: 45 }; // Mobile

  icons.forEach((icon, i) => {
    const angle = (i / icons.length) * (2 * Math.PI);
    const x = config.centerX + config.radius * Math.cos(angle) - config.offset;
    const y = config.centerY + config.radius * Math.sin(angle) - config.offset;
    icon.style.left = `${x}px`;
    icon.style.top = `${y}px`;

    icon.addEventListener("mouseenter", () => {
      const newTitle = icon.getAttribute("data-title");
      const newText = icon.getAttribute("data-text");

      titleEl.classList.add("fade-out");
      textEl.classList.add("fade-out");

      setTimeout(() => {
        titleEl.innerHTML = newTitle;
        textEl.innerHTML = newText;

        titleEl.classList.remove("fade-out");
        textEl.classList.remove("fade-out");
      }, 200);
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const disabledLinks = [
    "https://seipee.tecnotrade.dev/prodotti",
    "https://seipee.tecnotrade.dev/en/products",
    "https://seipee.tecnotrade.dev/es/productos",
    "https://seipee.tecnotrade.dev/de/produkte",
    "https://seipee.tecnotrade.dev/area-tecnica",
    "https://seipee.tecnotrade.dev/en/technical-area",
    "https://seipee.tecnotrade.dev/fr/area-tecnica",
    "https://seipee.tecnotrade.dev/es/area-tecnica",
    "https://seipee.tecnotrade.dev/de/technischen-bereich"
  ];

  document.querySelectorAll(".desktop-menu a").forEach(link => {
    const href = link.getAttribute("href");

    if (disabledLinks.includes(href)) {
      // Aggiunge la classe
      link.classList.add("expand-icon");

      // Disabilita il clic
      link.addEventListener("click", (e) => {
        e.preventDefault();
      });
    }
  });
});

// Bandierine
document.addEventListener("DOMContentLoaded", function () {
  const selects = document.querySelectorAll(".language-switcher");

  function applyFlagToWrapper(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    if (!opt) return;
    const url = opt.dataset.flag || "";
    const wrapper = selectEl.closest(".language-select");
    if (wrapper) {
      wrapper.style.setProperty("--flag-url", `url(${url})`);
    }
  }

  selects.forEach(selectEl => {
    applyFlagToWrapper(selectEl);
    selectEl.addEventListener("change", function () {
      applyFlagToWrapper(this);
      window.location.href = this.value;
    });
  });

  window.addEventListener("resize", () => {
    selects.forEach(applyFlagToWrapper);
  });
});
// END Bandierine

