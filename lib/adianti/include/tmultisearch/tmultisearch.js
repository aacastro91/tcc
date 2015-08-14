function tmultisearch_enable_field(form_name, field) {
    try { $('#s2id_'+$('form[name='+form_name+'] [name="'+field+'"]').attr('id')).select2("enable", true); } catch (e) { }    
}

function tmultisearch_disable_field(form_name, field) {
    try { $('#s2id_'+$('form[name='+form_name+'] [name="'+field+'"]').attr('id')).select2("enable", false); } catch (e) { }    
}