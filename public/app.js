$(document.body)

  /***
    Article Composition Page
   ***/

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

  .on('click', '.hvl-pedit-expand', function (e) {
    var form = $(this).parents('form:first')
      .toggleClass('hvl-pedit-expanded')
    var expanded = form.hasClass('hvl-pedit-expanded')
    var btn = $(e.currentTarget)
      .toggleClass('hvl-btn-down', expanded)
    var area = form.find('[name=text]')

    if (expanded) {
      area.insertBefore(form.find('.hvl-split-left'))
    } else {
      area.prependTo(form.find('.hvl-split-right'))
    }

    Sqaline.resizeAll(form)
  })

  .on('click', '.hvl-ncomment-preview-btn', function (e) {
    var form = $(e.currentTarget).parents('form:first')
    var preview = form.find('.hvl-ncomment-preview')
    preview.length || (preview =
      $('<div class="hvl-ncomment-preview">').insertBefore(form.find('[name=text]')))

    $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: form.serialize() + '&preview=1',
      success: function (html) {
        preview.html(html)
      },
      error: function (xhr) {
        var resp = xhr.responseJSON
        if (resp) {
          var list = $('<ul class="hvl-errors">')
          resp.error && resp.error.message && (resp = [resp.error.message])
          $.each(resp, function (i, s) { $('<li>').text(s).appendTo(list) })
          preview.empty().append(list)
        } else {
          alert(xhr.responseText)
        }
      },
    })

    return false
  })

  /***
    Splitter
   ***/

  .on('mousedown', '.hvl-splitter', function (e) {
    var splitting = {
      dragged: false,
      self: $(this),
      el: $(this).parent(),
      left: $(this).prev(),
      right: $(this).next(),
    }

    $.extend(splitting, {
      leftMost: splitting.el.offset().left,
      rightMost: splitting.el.offset().left + splitting.el.width(),
      width: splitting.el.width(),
    })

    $(document)
      .on('mouseup.hvl-sp', function (e) {
        $(document).off('.hvl-sp')

        if (splitting && !splitting.dragged) {
          splitting.left.add(splitting.right).css('width', '49.5%')
        }

        splitting = null
      })
      .on('mousemove.hvl-sp', function (e) {
        if (splitting) {
          splitting.dragged = true
          var pos = e.pageX - splitting.leftMost
          var percents = Math.min(90, Math.max(10, pos / splitting.width * 100))
          splitting.left.css('width', percents - 0.5 + '%')
          splitting.right.css('width', 100 - percents - 0.5 + '%')
        }
      })
  })

$(function () {
  $('.hvl-split-right').each(function () {
    $('<div class="hvl-splitter">')
      .css('height', Math.max( $(this).height(), $(this).prev().height() ))
      .insertBefore(this)
  })
})