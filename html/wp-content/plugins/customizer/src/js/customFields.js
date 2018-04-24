document.addEventListener('DOMContentLoaded', function () {
  var url = customizer.ajax_url;
  var delete_names = [];

  function buildUrl(data) {
    var first_time = true;
    var url = "";
    for (var key in data) {
      if (first_time) {
        url += "?";
      } else {
        url += "&";
      }
      url += key + "=" + data[key];
      first_time = false;
    }

    return url;
  }

  function initialize() {
    var form = document.querySelector('#auto-form');
    form = form ? form : document.querySelector('form#post');
    form = form ? form : document.querySelector('form#edittag');
    if (form) {
      form.addEventListener('submit', function () {
        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'delete_names';
        hidden.value = delete_names;
        form.appendChild(hidden);
        return true;
      }, false);
    }
  }

  function bindEvent() {
    var add_button = document.querySelectorAll('.field-add-button'),
      del_button = document.querySelectorAll('.field-del-button'),
      read_only_element = document.querySelectorAll('.readonly'),
      check_boxes = document.querySelectorAll('.chk')
    ;

    if (add_button) {
      add_button.forEach(function (add) {
        add.addEventListener('click', addFieldGroup, false);
      });
    }
    if (del_button) {
      del_button.forEach(function (del) {
        del.addEventListener('click', delFieldGroup, false);
      });
    }
    if (read_only_element) {
      read_only_element.forEach(function (read_only) {
        read_only.addEventListener('keydown', readOnly, false);
      });
    }
    if (check_boxes) {
      check_boxes.forEach(function (chk) {
        chk.addEventListener('change', changeCheckBoxValue, false);
      });
    }

    // アップロードボタンのバインド
    if (typeof uploadBindEvent !== "undefined") {
      uploadBindEvent();
    }

  }

  // ====================
  //     Field の追加
  // ====================
  function addFieldGroup(e) {
    var target = e.currentTarget,
      args = target.attributes,
      template = args['data-template']['nodeValue'],
      path = args['data-config-path']['nodeValue'],
      sequence = args['data-current-sequence']['nodeValue'],
      append_target = args['data-append-target']['nodeValue'],
      group_key = args['data-group-key']['nodeValue']
    ;
    // 1回クリックしたら隠す
    target.classList.add('hidden');
    renderNextFieldGroup(path, sequence, target, append_target, group_key, template);
  }

  function renderNextFieldGroup(path, sequence, target, append_target, group_key, template) {
    var action = 'get_form_group_html';
    var element = 'div';
    var data = {
      action: action,
      template: template,
      path: path,
      sequence: sequence,
      group_id: append_target,
      group_key: group_key
    };
    var request = new XMLHttpRequest();
    request.onreadystatechange = function (event) {
      if (request.readyState === 4) {
        if (request.status === 200) {
          // 追加したいHTML NODE情報を作成
          var $element = document.createElement(element);
          var response = JSON.parse(request.responseText.trim());
          if (response.success) {
            $element.innerHTML = response.html.trim();
            $element = $element.firstChild;

            // 追加対象
            var $click_target = document.querySelector('#' + append_target);
            var $append_target = $click_target.parentNode;
            $append_target.insertBefore($element, $click_target.nextSibling);

            // イベントを改めて付与
            bindEvent();
          } else {
            console.log(response.error);
          }
        } else {
          // 失敗したら追加ボタンを再表示
          target.classList.remove('hidden');
          console.log(request.statusText); // => Error Message
        }
      }
    };
    request.onerror = function (event) {
      console.log(event.type); // => "error"
    };

    request.open("GET", url + buildUrl(data), true);
    request.send();
  }

  // ====================
  //     Field の削除
  // ====================
  function delFieldGroup(e) {
    if (!confirm("本当に削除しますか？")) {
      return;
    }

    var del_button = e.currentTarget,
      args = del_button.attributes,
      group_key = args["data-group-key"]['nodeValue'],
      del_target = args["data-delete-target"]['nodeValue'],
      bf_group_id = args["data-bf-group-id"]['nodeValue'],
      bf_group = null,
      bf_add_button = null,
      // 削除対象
      $delete_target = document.querySelector('#' + del_target)
    ;

    if (bf_group_id !== "") {
      bf_group = document.querySelector('#' + bf_group_id);
      bf_add_button = bf_group.querySelector('.field-add-button');
    } else {
      alert("最後の要素は削除できません。");
      return false;
    }

    // 削除対象が見えない追加ボタンを持っていなかったら
    // その前の追加ボタンを見えるようにする
    if (bf_add_button && !$delete_target.querySelector('.field-add-button.hidden')) {
      bf_add_button.classList.remove('hidden');
    }

    // 自分の次の要素があったら
    // その要素の"data-bf-group-id"を更新する
    var $next_element = $delete_target.nextElementSibling;
    if ($next_element) {
      if ($next_element.classList.contains(group_key)) {
        var next_del = $next_element.querySelector('.field-del-button');
        next_del.setAttribute('data-bf-group-id', bf_group_id);
      }
    }

    // 削除した情報を保存する
    $delete_target.querySelectorAll('.input').forEach(function (del_input) {
      var name = del_input.attributes['name']['nodeValue'];
      name = name.replace('[]', '');
      if (delete_names.indexOf(name) === -1) {
        delete_names.push(name);
      }
    });

    $delete_target.remove();
    return true;
  }

  // ====================
  //     CHECKBOX 関連
  // ====================
  function changeCheckBoxValue(e) {
    var args = e.currentTarget.attributes,
      real_value = document.querySelector('#' + args["data-real-value-input"]['nodeValue'])
    ;
    real_value.value = e.currentTarget.checked ? 'on' : 'off';
  }

  // ====================
  //         共通
  // ====================
  function readOnly(e) {
    e.preventDefault();
  }

  initialize();
  bindEvent();
}, false);
