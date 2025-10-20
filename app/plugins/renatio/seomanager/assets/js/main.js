function updateCountdown(input, comment) {
    let counterEl = comment.find('span');
    let limit = input.data('limit');

    let remaining = limit - input.val().length;
    counterEl.text(remaining);

    counterEl.addClass('fw-bold')

    counterEl.toggleClass('text-danger', remaining < 0);
    counterEl.toggleClass('text-success', remaining > 0);
}

function makeCountable(el) {
    let input = el.find(':input');
    let limit = input.data('limit');

    let comment = el.find('.form-text');

    comment.html(comment.html().replace(':limit', limit));

    updateCountdown(input, comment);
}

$(document).on('keyup change', '.countable', function () {
    makeCountable($(this));
});

$(document).ready(function () {
    $('.countable').each(function () {
        makeCountable($(this));
    });
});

$(document).on('ajaxSuccess', function () {
    $('.countable').each(function () {
        makeCountable($(this));
    });
});
