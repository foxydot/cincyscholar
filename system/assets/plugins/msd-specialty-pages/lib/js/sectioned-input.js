(function($){
    $.fn.collapsible = function(options) {
                // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            header: "h3",
        }, options );
        $(this).addClass("ui-accordion ui-widget ui-helper-reset");
        var headers = $(this).find(settings.header);
        headers.addClass("accordion-header ui-accordion-header ui-helper-reset ui-state-default ui-accordion-icons ui-corner-all");
        headers.append('<span class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-e">');
        headers.each(function(){
            $(this).next().hide();
        });
        headers.click(function() {
            var header = $(this);
            var panel = $(this).next();
            var isOpen = panel.is(":visible");
            if(isOpen)  {
                panel.slideUp("fast", function() {
                    panel.hide();
                    header.removeClass("ui-state-active")
                        .addClass("ui-state-default")
                        .children("span").removeClass("ui-icon-triangle-1-s")
                            .addClass("ui-icon-triangle-1-e");
              });
            }
            else {
                panel.slideDown("fast", function() {
                    panel.show();
                    header.removeClass("ui-state-default")
                        .addClass("ui-state-active")
                        .children("span").removeClass("ui-icon-triangle-1-e")
                            .addClass("ui-icon-triangle-1-s");
              });
            }
        });
        return true;
    }; 
}(jQuery));

jQuery(function($){
    $( "#wpa_loop-sections" )
      .collapsible({
        header: ".section_handle"
      });
      
    $( "#wpa_loop-sections" )
      .sortable({
        axis: "y",
        handle: ".section_handle",
        stop: function( event, ui ) {
          // IE doesn't register the blur when sorting
          // so trigger focusout handlers to remove .ui-state-focus
          ui.item.children( ".section_handle" ).triggerHandler( "focusout" );
 
          // Refresh accordion to handle new order
          $( this ).accordion( "refresh" );
        },
        change: function(){
            $("#warning").show();
        }
      });
    
    $("#postdivrich").after($("#_page_sectioned_metabox"));
    $(".colorpicker").spectrum({
        preferredFormat: "rgb",
        showAlpha: true,
        showInput: true,
        allowEmpty: true,
    });
    
    $('.ui-toggle-btn').each(function(){
        var toggled = $(this).next('.switchable');
        if($(this).find('input[type=checkbox]').is(':checked')){
            toggled.slideDown(500);
        } else {
            toggled.slideUp(500);
        }
    });
    $('.cols-2').each(function(){
        var layout = $(this).parents('.wpa_group-sections').find($('select.layout')).val();
        if(layout === 'two-col'){
            $(this).show();
        }
    });
    $('.cols-3').each(function(){
        var layout = $(this).parents('.wpa_group-sections').find($('select.layout')).val();
        if(layout === 'three-col'){
            $(this).show();
        }
    });
    $('.cols-4').each(function(){
        var layout = $(this).parents('.wpa_group-sections').find($('select.layout')).val();
        if(layout === 'four-col'){
            $(this).show();
        }
    });
    $('.range-value').html(function(){
        var section = $(this).parents('.cell');
        var range = section.find('.input-range').val();
        $(this).html(range + '/12 columns');
    });
    $('select.layout').change(function(){
        var layout = $(this).val();
        var section = $(this).parents('.wpa_group-sections');
        section.find($('.cols-2, .cols-3, .cols-4')).hide();
        if(layout === 'two-col'){
            section.find($('.cols-2')).show();
        }
        if(layout === 'three-col'){
            section.find($('.cols-3')).show();
        }
        if(layout === 'four-col'){
            section.find($('.cols-4')).show();
        }
    });
    $('.input-range').change(function(){
        var range = $(this).val();
        var section = $(this).parents('.cell');
        section.find($('.range-value')).html(range + '/12 columns');
    });
    $('.ui-toggle-btn').click(function(){
        var toggled = $(this).next('.switchable');
        if($(this).find('input[type=checkbox]').is(':checked')){
            toggled.slideDown(500);
        } else {
            toggled.slideUp(500);
        }
    });
    if($.wpalchemy){
    $.wpalchemy.on('wpa_copy',function(){
        $( "#wpa_loop-sections .wpa_group:nth-last-child(2)" )
          .collapsible({
            header: ".section_handle"
        });
        
        $('#wpa_loop-sections .wpa_group:nth-last-child(2) .ui-toggle-btn').each(function(){
            var toggled = $(this).next('.switchable');
            if($(this).find('input[type=checkbox]').is(':checked')){
                toggled.slideDown(500);
            } else {
                toggled.slideUp(500);
            }
        });
    });
    }
});