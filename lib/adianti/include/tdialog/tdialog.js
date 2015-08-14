function tdialog_start(id, callback)
{
    $(document).ready(function() {
        $( id ).modal({backdrop:true, keyboard:true});
        if (typeof callback != 'undefined')
        {
            $( id ).on("hidden.bs.modal", callback );
        }
    });
}