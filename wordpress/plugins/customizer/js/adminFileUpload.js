document.addEventListener('DOMContentLoaded', function () {
  function disableSort() {
    if (jQuery('.meta-box-sortables').sortable) {
      jQuery('.meta-box-sortables').sortable({
        disabled: true
      });
    }
  }

  disableSort();
}, false);

function uploadBindEvent() {
  var upload_button = document.querySelectorAll('.upload-btn'),
    upload_clear_button = document.querySelectorAll('.upload-clear-btn')
  ;

  if (upload_button && uploadByMediaBox) {
    upload_button.forEach(function (up) {
      up.addEventListener('click', uploadByMediaBox, false);
    });
  }
  if (upload_clear_button && clearSelectedImage) {
    upload_clear_button.forEach(function (clr) {
      clr.addEventListener('click', clearSelectedImage, false);
    });
  }
}


// ====================
//       Media関連
// ====================
// カスタムしたmedia modalの作成
function createCustomMedia() {
  return wp.media({
    title: 'ファイルアップロード',
    library: {type: ''},
    frame: 'select',
    button: {text: '選択'},
    multiple: false
  });
}

function uploadByMediaBox(e) {
  e.preventDefault();
  var args = e.currentTarget.attributes;
  var customMedia = createCustomMedia();
  clearSelectedImage(e);
  customMedia.on('select', function () {
    var attachment = customMedia.state().get('selection').first().toJSON(),
      parent_url_input = document.querySelector('#' + args["data-url-input"]['nodeValue']),
      url_input = parent_url_input.querySelector('input'),
      url_name = parent_url_input.querySelector('p'),
      url_img = parent_url_input.querySelector('img'),
      split = attachment.url.split('.'),
      ext = split[split.length - 1].toLowerCase(),
      img_extensions = ['png', 'jpg', 'jpeg', 'gif', 'ico']
    ;
    if (img_extensions.some(function (v) {
        return v === ext
      })) {
      if (!url_img) {
        url_img = document.createElement('img');
        parent_url_input.insertBefore(url_img, parent_url_input.firstChild);
      }
      url_img.src = attachment.url;
    } else {
      if (!url_name) {
        url_name = document.createElement('p');
        parent_url_input.insertBefore(url_name, parent_url_input.firstChild);
      }
      url_name.textContent = baseName(attachment.url);
    }

    url_input.value = attachment.url;
  });

  customMedia.open();
}

function baseName(str) {
  var base = new String(str).substring(str.lastIndexOf('/') + 1);
  if (base.lastIndexOf('.') != -1)
    base = base.substring(0, base.lastIndexOf('.'));
  return base;
}

function clearSelectedImage(e) {
  e.preventDefault();
  var args = e.currentTarget.attributes,
    parent_url_input = document.querySelector('#' + args["data-url-input"]['nodeValue']),
    url_input = parent_url_input.querySelector('input'),
    url_img = parent_url_input.querySelector('img'),
    url_name = parent_url_input.querySelector('p')
  ;
  url_input.value = "";
  if (url_img) {
    url_img.remove();
  } else if (url_name) {
    url_name.remove();
  }
}