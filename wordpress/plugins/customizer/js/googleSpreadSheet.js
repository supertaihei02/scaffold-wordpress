document.addEventListener('DOMContentLoaded', function()
{
    var url = customizer.ajax_url;
    
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

    function initialize() {
        var createButtons = document.querySelectorAll('.create_spread_sheet');
        if (createButtons) {
            if (createButtons) {
              createButtons.forEach(function(createButton) {
                createButton.addEventListener('click', createSheet, false);
              });
            }
        }
    }

    // ====================
    //  SpreadSheet の作成
    // ====================
    function createSheet(e) 
    {
        var target = e.currentTarget,
            args = target.attributes,
            sheetKey = args['sheet_key']['nodeValue'],
            sheetName = args['sheet_name']['nodeValue'],
            inputSheetId = args['spread_sheet_id']['nodeValue'],
            $inputTarget = document.querySelector('#' + inputSheetId),
            doExecute = false
        ;
        if ($inputTarget.textContent.trim().length > 0) {
            if (window.confirm('本当に実行しますか？(現在セットされているSheetIDは削除されます)')){
                doExecute = true;
            }
        } else {
            doExecute = true;
        }
        
        if (doExecute) {
            postToServer(sheetKey, sheetName, inputSheetId);
        }
    }
    
    function postToServer(sheetKey, sheetName, inputSheetId)
    {
        var action = 'create_google_spread_sheet';
        var data = {
            action:       action,
            sheet_key:    sheetKey,
            sheet_name:   sheetName,
            input_sheet_id:   inputSheetId
        };
        var request = new XMLHttpRequest();
        request.onreadystatechange = function (event) {
            if (request.readyState === 4) {
                if (request.status === 200) {
                    var response = JSON.parse(request.responseText.trim());
                    if (response.success) {
                        var $inputTarget = document.querySelector('#' + response.input_sheet_id);
                        if ($inputTarget) {
                            $inputTarget.textContent = response.sheet_id;
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
        
        request.open("GET", url + buildUrl(data), true);
        request.send();
    }
    
    initialize();
}, false);
