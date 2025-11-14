document.addEventListener('DOMContentLoaded', function() {
    const mybutton = document.getElementById("btn-back-to-top");

    if (!mybutton) return;

    function scrollFunction() {
        if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
            mybutton.style.opacity = "1";
        } else {
            mybutton.style.opacity = "0";
        }
    }

    // When the user scrolls down, show the button
    window.addEventListener("scroll", scrollFunction);

    // Recalculate after AJAX updates
    document.addEventListener('ajaxUpdate', function () {
        scrollFunction();
    });

    // When the user clicks on the button, scroll to the top
    mybutton.addEventListener("click", function() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    });

    // Initial check
    scrollFunction();
});

