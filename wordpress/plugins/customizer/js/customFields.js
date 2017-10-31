document.addEventListener('DOMContentLoaded', function()
{
    var url = "/wp-admin/admin-ajax.php";
    
    function buildUrl(data)
    {
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
    
    function bindEvent() {
        var add_button = document.querySelectorAll('.field-add-button'), 
            del_button = document.querySelectorAll('.field-del-button'), 
            upload_button = document.querySelectorAll('.upload-btn'), 
            upload_clear_button = document.querySelectorAll('.upload-clear-btn'),
            read_only_element = document.querySelectorAll('.readonly'),
            check_boxes = document.querySelectorAll('.chk')
            ;

        if (add_button) {
            add_button.forEach(function(add) {
                add.addEventListener('click', addFieldGroup, false);
            });
        }
        if (del_button) {
            del_button.forEach(function(del) {
                del.addEventListener('click', delFieldGroup, false);
            });
        }
        if (upload_button) {
            upload_button.forEach(function(up) {
                up.addEventListener('click', uploadByMediaBox, false);
            });
        }
        if (upload_clear_button) {
            upload_clear_button.forEach(function(clr) {
                clr.addEventListener('click', clearSelectedImage, false);
            });
        }
        if (read_only_element) {
            read_only_element.forEach(function(read_only) {
                read_only.addEventListener('keydown', readOnly, false);
            });
        }
        if (check_boxes) {
            check_boxes.forEach(function(chk) {
                chk.addEventListener('change', changeCheckBoxValue, false);
            });
        }

    }

    function disableSort() 
    {
        // ここだけ JQuery使っていて恥ずかしい
        if (jQuery('.meta-box-sortables').sortable) {
            jQuery('.meta-box-sortables').sortable({
                disabled: true
            });
        }
    }

    // ====================
    //     Field の追加
    // ====================
    function addFieldGroup(e) 
    {
        var target = e.currentTarget,
            args = target.attributes,
            post_type     = args["data-post-type"]['nodeValue'],
            field_group   = args["data-field-group"]['nodeValue'],
            next_serial   = args["data-next-serial"]['nodeValue'],
            append_target = args["data-append-target"]['nodeValue']
        ;
        // 1回クリックしたら隠す
        target.classList.add('hidden');
        renderNextFieldGroup(post_type, field_group, next_serial, target, append_target);
    }
    
    function renderNextFieldGroup(post_type, field_group, next_serial, target, append_target)
    {
        var action = 'siBuildEmptyGroup';
        var element = 'div';
        if (location.pathname.indexOf('edit-tags.php') !== -1 || 
          location.pathname.indexOf('term.php') !== -1) {
          action = 'siBuildEmptyGroupForTerm';
          element = 'tbody';
        }
        var data = {
            action:      action,
            post_type:   post_type,
            field_group: field_group,
            next_serial: next_serial,
            group_id:    append_target
        };
        var request = new XMLHttpRequest();
        request.onreadystatechange = function (event) {
            if (request.readyState === 4) {
                if (request.status === 200) {
                    // 追加したいHTML NODE情報を作成
                    var $element = document.createElement(element);
                    $element.innerHTML = request.responseText.trim();
                    $element = $element.firstChild;
                    
                    // 追加対象
                    var $click_target = document.querySelector('#' + append_target);
                    var $append_target = $click_target.parentNode;
                    $append_target.insertBefore($element, $click_target.nextSibling);
                    
                    // イベントを改めて付与
                    bindEvent();
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
        
        var can_remove = true,
            del_button = e.currentTarget,
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
        }
        
        // 削除対象が見えない追加ボタンを持っていなかったら
        // その前の追加ボタンを見えるようにする
        if (!$delete_target.querySelector('.field-add-button.hidden')) {
            if (bf_add_button) {
                bf_add_button.classList.remove('hidden');
            } else {
                can_remove = false;
                alert("最後の要素は削除できません。");
            }
        }
        
        // 自分の次の要素があったら
        // その要素の"data-bf-group-id"を更新する
        var $next_element = $delete_target.nextElementSibling;
        if ($next_element) {
            if ($next_element.querySelector('.' + group_key)) {
                var next_del = $next_element.querySelector('.field-del-button');
                next_del.setAttribute('data-bf-group-id', bf_group_id);
            }
        }
        
        if (can_remove) {
            $delete_target.remove();
        }
    }

    // ====================
    //       Media関連
    // ====================
    // カスタムしたmedia modalの作成
    function createCustomMedia()
    {
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
        customMedia.on('select', function() {
            var attachment = customMedia.state().get('selection').first().toJSON(),
                parent_url_input = document.querySelector('#' + args["data-url-input"]['nodeValue']),
                url_input = parent_url_input.querySelector('input'),
                url_name = parent_url_input.querySelector('p'),
                url_img = parent_url_input.querySelector('img'),
                split = attachment.url.split('.'),
                ext = split[split.length - 1].toLowerCase(),
                img_extensions = ['png', 'jpg', 'jpeg', 'gif', 'ico']
            ;
            if (img_extensions.some(function(v){ return v === ext })) {
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

    function baseName(str)
    {
        var base = new String(str).substring(str.lastIndexOf('/') + 1);
        if(base.lastIndexOf('.') != -1)
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
    
    bindEvent();
    disableSort();
}, false);
