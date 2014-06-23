$(document.body)
  .on('click', '.hvl-pedit-preview', function (e) {
    var form = $(this).parents('form:first')
    var hidden = $('<input type="hidden" name="preview" value="1">')
      .appendTo(form)

    setTimeout(function () {
      form.attr('target', ''); hidden.remove()
    }, 2000)

    form.attr('target', 'habravel_preview')
    form.submit()
    return false
  })