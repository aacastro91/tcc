function tform_send_data(form_name, field, value) {
    try{
        $('form[name='+form_name+'] [name='+field+']').val( value );
        
        if ($('form[name='+form_name+'] [name='+field+']').attr('exitaction'))
        {
            eval($('form[name='+form_name+'] [name='+field+']').attr('exitaction'));
        }
        if ($('form[name='+form_name+'] [name='+field+']').attr('changeaction'))
        {
            eval($('form[name='+form_name+'] [name='+field+']').attr('changeaction'));
        }
    } catch (e) { }
}

function tform_send_data_by_id(form_name, field, value) {
    try{
        $('form[name='+form_name+'] [id='+field+']').val( value );
        
        if ($('form[name='+form_name+'] [id='+field+']').attr('exitaction'))
        {
            eval($('form[name='+form_name+'] [id='+field+']').attr('exitaction'));
        }
        if ($('form[name='+form_name+'] [id='+field+']').attr('changeaction'))
        {
            eval($('form[name='+form_name+'] [id='+field+']').attr('changeaction'));
        }
    } catch (e) { }
}

function tform_send_data_aggregate(form_name, field, value) {
    try {
        if ($('form[name='+form_name+'] [name='+field+']').val() == '')
        {
            tform_send_data(form_name, field, value);
        }
        else
        {
            current_value = $('form[name='+form_name+'] [name='+field+']').val();
            $('form[name='+form_name+'] [name='+field+']').val( current_value + ', '+ value );
        }
    } catch (e) { }
}