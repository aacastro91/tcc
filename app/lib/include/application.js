loading = true;

function showLoading()
{
  if (loading)
    __adianti_block_ui('Carregando');
}

function frm_number_only_exc() {
  // allowed: numeric keys, numeric numpad keys, backspace, del and delete keys
  if (event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40 || event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || (event.keyCode < 106 && event.keyCode > 95)) {
    return true;
  } else {
    return false;
  }
}

Adianti.onBeforeLoad = function ()
{
  loading = true;

  setTimeout(function () {
    showLoading()
  }, 400);
};

Adianti.onAfterLoad = function ()
{
  loading = false;

  __adianti_unblock_ui();

  $("input.frm_number_only").keydown(function (event) {

    if (frm_number_only_exc()) {
    } else {
      if (event.keyCode < 48 || event.keyCode > 57) {
        event.preventDefault();
      }
    }
  })
};