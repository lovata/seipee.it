/*
 * Template Selector plugin
 * 
 * Data attributes:
 * - data-control="campaign-templateselector" - enables the plugin on an element
 * - data-option="value" - an option with a value
 *
 * JavaScript API:
 * $('a#someElement').templateSelector({ option: 'value' })
 *
 * Dependences: 
 * - Some other plugin (filename.js)
 */

+function ($) { "use strict";

    // TEMPLATE SELECTOR CLASS DEFINITION
    // ============================

    var TemplateSelector = function(element, options) {
        this.options   = options
        this.$el       = $(element)

        // Init
        this.init()
    }

    TemplateSelector.DEFAULTS = {
        dataLocker: null
    }

    TemplateSelector.prototype.init = function() {
        var self = this
        this.$dataLocker  = $(this.options.dataLocker, this.$el)

        this.$el.on('click', '>ul>li a', function(){
            self.selectTemplate(this)
        })
    }

    TemplateSelector.prototype.selectTemplate = function(el) {
        var $item = $(el).closest('li')

        $item.addClass('selected')
            .siblings().removeClass('selected')

        this.$dataLocker.val($item.data('value'))
    }

    // TEMPLATE SELECTOR PLUGIN DEFINITION
    // ============================

    var old = $.fn.templateSelector

    $.fn.templateSelector = function (option) {
        var args = Array.prototype.slice.call(arguments, 1), result
        this.each(function () {
            var $this   = $(this)
            var data    = $this.data('oc.campaignTemplateSelector')
            var options = $.extend({}, TemplateSelector.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('oc.campaignTemplateSelector', (data = new TemplateSelector(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })
        
        return result ? result : this
    }

    $.fn.templateSelector.Constructor = TemplateSelector

    // TEMPLATE SELECTOR NO CONFLICT
    // =================

    $.fn.templateSelector.noConflict = function () {
        $.fn.templateSelector = old
        return this
    }

    // TEMPLATE SELECTOR DATA-API
    // ===============

    $(document).render(function() {
        $('[data-control="campaign-templateselector"]').templateSelector()
    });

}(window.jQuery);