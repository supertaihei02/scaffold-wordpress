(function ($) {
  inlineEditPost.save = function (id) {
    var params, fields, page = $('.post_status_page').val() || '';

    if (typeof(id) === 'object') {
      id = this.getId(id);
    }

    $('table.widefat .spinner').addClass('is-active');

    params = {
      action: 'inline-save',
      post_type: typenow,
      post_ID: id,
      edit_date: 'true',
      post_status: page
    };

    fields = $('#edit-' + id).find(':input').serialize();
    params = fields + '&' + $.param(params);

    // make ajax request
    $.post(ajaxurl, params,
      function (r) {
        var $errorSpan = $('#edit-' + id + ' .inline-edit-save .error');

        $('table.widefat .spinner').removeClass('is-active');
        $('.ac_results').hide();

        if (r) {
          if (-1 !== r.indexOf('<tr')) {
            $(inlineEditPost.what + id).siblings('tr.hidden').addBack().remove();
            $('#edit-' + id).before(r).remove();
            $(inlineEditPost.what + id).hide().fadeIn(400, function () {
              // Move focus back to the Quick Edit link. $( this ) is the row being animated.
              $(this).find('.editinline').focus();
              wp.a11y.speak(inlineEditL10n.saved);
              // nakanishi 追加はこの１行のみ
              location.reload();
            });
          } else {
            r = r.replace(/<.[^<>]*?>/g, '');
            $errorSpan.html(r).show();
            wp.a11y.speak($errorSpan.text());
          }
        } else {
          $errorSpan.html(inlineEditL10n.error).show();
          wp.a11y.speak(inlineEditL10n.error);
        }
      },
      'html');
    // Prevent submitting the form when pressing Enter on a focused field.
    return false;
  }
})(jQuery);
