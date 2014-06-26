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

    area.focus()
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

  .on('click', '.hvl-pedit-tags u, .hvl-pedit-tags a', function (e) {
    var el = $(e.currentTarget)
    var to = el.parents('.hvl-pedit-tags:first').find('.hvl-pedit-tags-to')

    function update() {
      to.find('[name="tags[]"]').remove()
      to.find('a').each(function () {
        $('<input type="hidden" name="tags[]">').val($(this).text()).appendTo(to)
      })
    }

    if (el.is('.hvl-pedit-tags-new')) {
      $('<input class="hvl-input">').prependTo(to).focus()
        .blur(function () {
          var tag = $.trim($(this).val())
          if (tag === '') {
            $(this).remove()
          } else {
            var url = el.parents('form:first').attr('action')
              .replace(/\/[^\/]*$/, '/tags/' + encodeURIComponent(tag))

            $('<a class="hvl-pedit-tags-custom">')
              .attr('href', url)
              .text(tag)
              .replaceAll(this)

            update()
          }
        })
        .keypress(function (e) {
          switch (e.keyCode) {
          case 27:  $(this).val('')
          case 13:  return this.blur()
          }
        })
    } else if (el.parent().is('.hvl-pedit-tags-to')) {
      el.prependTo(to.nextAll('.hvl-pedit-tags-from'))
      update()
    } else {
      el.prependTo(to)
      update()
    }

    return false
  })

  .on('keydown', '.hvl-pedit-poll-opt:last-child input', function (e) {
    var cur = $(e.target).parents('.hvl-pedit-poll-opt:first')

    cur.clone()
      .find('input').val('').attr('name', function (i, s) {
        return s.replace(/(\d\D+)(\d+)/, function (m, l, r) {
          return l + (+r + 1)
        })
      }).end()
      .find('.hvl-pedit-poll-opt-num').text(function (i, s) {
        return s.replace(/\d+/, function (m) { return +m + 1 })
      }).end()
      .find('[name*="[id]"]').remove().end()
      .insertAfter(cur)
  })

  .on('click', '.hvl-pedit-poll-add', function (e) {
    var cur = $(e.currentTarget).parents('p:first').prevAll('.hvl-pedit-poll:first')

    cur.clone()
      .find('[name]').attr('name', function (i, s) {
        return s.replace(/\d+/, function (m) { return +m + 1 })
      }).end()
      .find('.hvl-pedit-poll-opt ~ .hvl-pedit-poll-opt').remove().end()
      .find('[name*="[id]"]').remove().end()
      .insertAfter(cur)
      .find('.hvl-input').val('')
        .first().focus()
  })

  /***
    Other Pages
   ***/

  .on('click', '.hvl-markup-help', function (e) {
    var url = $(e.currentTarget).attr('href')
    var el = $('[data-hvl-markup-text="' + url + '"]')
    $('.hvl-markup-text').remove()

    el.length || $.ajax({
      url: url,
      dataType: 'text',
      success: function (html) {
        $('<aside class="hvl-markup-text">').html(html).appendTo('body').show()
          .attr('data-hvl-markup-text', url)
      },
    })

    return false
  })
  .on('click', '.hvl-markup-text', function (e) {
    if ($(e.target).is('h3:first-child')) {
      $(this).toggleClass('hvl-markup-text-opposite')
    } else if (e.target === e.currentTarget || e.target.tagName == 'PRE') {
      $(e.currentTarget).remove()
    }
  })

  .on('click', '.hvl-comment-reply-btn, .hvl-comment-edit-btn', function (e) {
    var comment = $(e.currentTarget).parents('[data-hvl-post-id]:first')
    var parentID = parseInt(comment.attr('data-hvl-post-id'))
    var form = $('.hvl-ncomment:last')
    var editing =e.currentTarget.className.indexOf('hvl-comment-edit-btn') != -1

    if (!isNaN(parentID) && form.length) {
      var dest = comment.find('> .hvl-comment-children')
      dest.length || (dest = $('<div class="hvl-comment-children">').appendTo(comment))
      dest.find('> .hvl-ncomment').remove()

      form = form.clone()
        .append('<input type="hidden" name="parent" value="' + parentID + '">')
        .find('.hvl-ncomment-preview').remove().end()
        .prependTo(dest)
        .find('textarea').val('').focus().end()

      if (editing) {
        $.ajax({
          url: form.attr('action').replace(/\/[^\/]*$/, '/source/' + parentID + '?dl=1'),
          dataType: 'text',
          success: function (text) {
            form.find('textarea').val(function (i, s) { return s || text }).focus()
          },
        })

        form.submit(function () {
          form
            .attr('action', function (i, s) { return s.replace(/\/[^\/]*$/, '/edit') })
            .find('[name=parent]').attr('name', 'id').end()
        })
      }
    }
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