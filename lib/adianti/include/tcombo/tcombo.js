function tcombo_enable_field(form_name, field) {
    try{ $('form[name='+form_name+'] [name='+field+']').attr('disabled', false); } catch (e) { }
    try{ $('form[name='+form_name+'] [name='+field+']').removeClass('tcombo_disabled').addClass('tcombo'); } catch (e) { }    
}

function tcombo_disable_field(form_name, field) {
    try{ $('form[name='+form_name+'] [name='+field+']').attr('disabled', true); } catch (e) { }
    try{ $('form[name='+form_name+'] [name='+field+']').removeClass('tcombo').addClass('tcombo_disabled'); } catch (e) { }    
}

function tcombo_add_option(form_name, field, key, value) {
    $(function() {
        $('<option value="'+key+'">'+value+'</option>').appendTo('form[name="'+form_name+'"] select[name="'+field+'"]');
    });
}

function tcombo_clear(form_name, field) {
    $(function() {
        $('form[name='+form_name+'] [name='+field+']').html("");
    });
}