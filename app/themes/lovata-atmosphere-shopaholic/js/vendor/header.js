// Header JavaScript functionality from Puro theme

addEventListener('render', function() {
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

    // MENU DESKTOP - hover effects
    $('nav ul li').on('mouseenter', function() {
        $(this).children('ul').stop().slideDown();
    });

    document.querySelectorAll('nav li').forEach(li => {
        if (li.querySelector(':scope > ul')) {
            li.classList.add('has-children');
        }
    });

    // Nascondi il sottomenu quando il mouse esce dall'elemento <li>
    $('nav ul li').on('mouseleave', function() {
        $(this).children('ul').stop().slideUp();
    });

    // MENU MOBILE
    $('.open-menu-button').on('click', function(){
        $('.inside-mobile-menu-container').addClass('visible');
    });

    $('.menu-close-button').on('click', function(){
        $('.inside-mobile-menu-container').removeClass('visible');
    });

    $('.menu-mobile-container ul li a').on('click', function(){
        $('.inside-mobile-menu-container').removeClass('visible');
    });

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
        e.stopPropagation();
        var $ul = $(this).parent().children("ul");
        $ul.slideToggle();
        if ($(this).text() === "+") {
            $(this).text("-");
        } else {
            $(this).text("+");
        }
    });

    // Bandierine - Language switcher
    function initLanguageSwitcher() {
        const selects = document.querySelectorAll(".language-switcher");

        if (selects.length === 0) return;

        function applyFlagToWrapper(selectEl) {
            const opt = selectEl.options[selectEl.selectedIndex];
            if (!opt) return;
            const url = opt.dataset.flag || "";
            const wrapper = selectEl.closest(".language-select");
            if (wrapper && url) {
                wrapper.style.setProperty("--flag-url", `url(${url})`);
            }
        }

        selects.forEach(selectEl => {
            // Remove existing listeners to avoid duplicates
            const newSelectEl = selectEl.cloneNode(true);
            selectEl.parentNode.replaceChild(newSelectEl, selectEl);

            applyFlagToWrapper(newSelectEl);
            newSelectEl.addEventListener("change", function () {
                applyFlagToWrapper(this);
                window.location.href = this.value;
            });
        });

        window.addEventListener("resize", () => {
            selects.forEach(applyFlagToWrapper);
        });
    }

    // Initialize immediately
    initLanguageSwitcher();

});

// Logo switcher when sticky - вынесено из addEventListener('render') как в Puro
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

