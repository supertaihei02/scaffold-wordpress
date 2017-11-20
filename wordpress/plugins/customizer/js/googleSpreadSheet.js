document.addEventListener('DOMContentLoaded', function () {
  var url = customizer.ajax_url;

  function buildUrl(data, post = false) {
    var first_time = true;
    var url = "";
    for (var key in data) {
      if (first_time) {
        url += post ? '' : '?';
      } else {
        url += "&";
      }
      url += key + "=" + data[key];
      first_time = false;
    }

    return url;
  }

  function initialize() {
    var authButtons = document.querySelectorAll('.auth_google_client');
    var createButtons = document.querySelectorAll('.create_spread_sheet');
    if (authButtons) {
      if (authButtons) {
        authButtons.forEach(function (authButton) {
          authButton.addEventListener('click', auth, false);
        });
      }
    }

    if (createButtons) {
      if (createButtons) {
        createButtons.forEach(function (createButton) {
          createButton.addEventListener('click', createSheet, false);
        });
      }
    }
  }

  // ====================
  //  Google 認証情報の作成
  // ====================
  function auth(e) {
    var target = e.currentTarget,
      $inputSuccessUrl = document.querySelector('input[name=success_url]'),
      successUtl = $inputSuccessUrl.value
    ;
    callGoogleAuth(successUtl, target);
  }

  function callGoogleAuth(successUrl, target) {
    var action = 'auth_google_api';
    var data = {
      action: action,
      success_url: successUrl
    };

    var request = new XMLHttpRequest();
    request.onreadystatechange = function (event) {
      if (request.readyState === 4) {
        if (request.status === 200) {
          var response = JSON.parse(request.responseText.trim());
          if (response.success) {
            window.open(response.auth_url, '_self');
          } else {
            var err = document.querySelector('#err-'+target.id);
            err.innerHTML = response.error;
            err.classList.remove('hidden');
          }
        } else {
          console.log(request.statusText); // => Error Message
        }
      }
    };
    request.onerror = function (event) {
      console.log(event.type); // => "error"
    };

    request.open("POST", url + '?' + action, true);
    request.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
    request.send(buildUrl(data, true));
  }

  // ====================
  //  SpreadSheet の作成
  // ====================
  function createSheet(e) {
    var target = e.currentTarget,
      args = target.attributes,
      optionGroup = args['option_group']['nodeValue'],
      sheetName = args['sheet_name']['nodeValue'],
      sheetIdName = args['spread_sheet_id']['nodeValue'],
      sheetUrlName = args['spread_sheet_url']['nodeValue'],
      $inputTarget = document.querySelector('#' + sheetIdName),
      doExecute = false
    ;
    if ($inputTarget.value.trim().length > 0) {
      if (window.confirm('本当に実行しますか？(現在セットされているSheetIDは削除されます)')) {
        doExecute = true;
      }
    } else {
      doExecute = true;
    }

    if (doExecute) {
      callCreateSpreadSheet(optionGroup, sheetName, sheetIdName, sheetUrlName);
    }
  }

  function callCreateSpreadSheet(optionGroup, sheetLayerName, sheetIdName, sheetUrlName) {
    var action = 'create_google_spread_sheet';
    var data = {
      action: action,
      sheet_name: sheetLayerName,
      sheet_id_name: sheetIdName,
      sheet_url_name: sheetUrlName,
      option_group: optionGroup,
    };
    var request = new XMLHttpRequest();
    request.onreadystatechange = function (event) {
      if (request.readyState === 4) {
        if (request.status === 200) {
          var response = JSON.parse(request.responseText.trim());
          if (response.success) {
            var $inputTarget = document.querySelector('#' + sheetIdName);
            if ($inputTarget) {
              $inputTarget.value = response.sheet_id;
            }
          } else {
            console.log(response.error);
          }
        } else {
          console.log(request.statusText); // => Error Message
        }
      }
    };
    request.onerror = function (event) {
      console.log(event.type); // => "error"
    };

    request.open("POST", url + '?' + action, true);
    request.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
    request.send(buildUrl(data, true));
  }

  initialize();
}, false);
