<?php

class CustomizerSpreadSheet
{
    static private $APPLICATION_NAME = 'Spread_Sheet_Editor';
    static private $SCOPES = [
        Google_Service_Sheets::DRIVE,
        Google_Service_Sheets::DRIVE_FILE,
        Google_Service_Sheets::SPREADSHEETS,
    ];
    static private $client = null;
    
    function __construct()
    {
//        self::$client = self::getClient();
    }

    static function iAmApi()
    {
        header('content-type: application/json; charset=utf-8');
        header("X-Content-Type-Options: nosniff");
    }

    /* *******************************
     *       Google API Auth
     * *******************************/
    static function getRedirectUri()
    {
        return admin_url('admin-ajax.php') . '?action=set_google_access_token'; 
    }
    
    
    /**
     * 認証情報の取得
     * @return array|bool|mixed
     */
    static function getCredentialPath()
    {
        $setting = CustomizerFormSettings::get('google_spread_sheet');
        $setting = CustomizerConfig::getFieldSetting($setting, 'common');
        $credential_path = CustomizerConfig::getInputSetting($setting, 'credentials');
        $credential_path = CustomizerDatabase::getOption('google_spread_sheet_common_credentials', $credential_path[SI_DEFAULT], true);

        return $credential_path;
    }

    /**
     * クライアント情報の取得
     * @return array|bool|mixed
     */
    static function getClientSecretPath()
    {
        $setting = CustomizerFormSettings::get('google_spread_sheet');
        $setting = CustomizerConfig::getFieldSetting($setting, 'common');
        $secret_path = CustomizerConfig::getInputSetting($setting, 'secrets');
        $secret_path = CustomizerDatabase::getOption('google_spread_sheet_common_secrets', $secret_path[SI_DEFAULT], true);

        return self::expandHomeDirectory($secret_path);
    }

    /**
     * 最低限の準備がなかったら即死
     */
    static private function minimumCheck()
    {
        $client_secret = self::getClientSecretPath();
        if (!CustomizerUtils::isFile($client_secret)) {
            echo json_encode([
                'success' => false,
                'error' => '<br>OAuth クライアントのJSONが配置されていません <br> => ' . $client_secret
            ]);
            die();
        }
    }

    /**
     * Expands the home directory alias '~' to the full path.
     * @param string $path the path to expand.
     * @return string the expanded path.
     */
    static function expandHomeDirectory($path) {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }

    /**
     * Google Clientの取得
     * @return bool|Google_Client|null
     */
    static function getClient()
    {
        $credential_path = self::getCredentialPath();
        
        $client = new Google_Client();
        $client->setApplicationName(self::$APPLICATION_NAME);
        $client->setScopes(self::$SCOPES);
        $client->setAuthConfig(self::getClientSecretPath());
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        if (CustomizerUtils::isFile($credential_path)) {
            $access_token = json_decode(file_get_contents($credential_path), true);
            $client->setAccessToken($access_token);
            if (!self::refreshAccessToken($client)) {
                $client = false;
            }
        } else {
            $client = false;
        }

        return $client;
    }

    /**
     * 認証情報の作成ボタンを押すと呼び出される
     */
    static function getAuthUrl()
    {
        self::iAmApi();
        
        $credentials_path = self::getCredentialPath();
        $client_secret = self::getClientSecretPath();
        
        self::minimumCheck();
        
        $client = new Google_Client();
        $client->setApplicationName(self::$APPLICATION_NAME);
        $client->setScopes(self::$SCOPES);
        $client->setAuthConfig($client_secret);
        $client->setRedirectUri(self::getRedirectUri());
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        $access_token = null;
        if (CustomizerUtils::isFile($credentials_path)) {
            $access_token = json_decode(file_get_contents($credentials_path), true);
        }

        if (empty($access_token)) {
            $result = self::buildResultAuthUrl($client);
        } else {
            $client->setAccessToken($access_token);
            if ($client->isAccessTokenExpired()) {
                if (self::refreshAccessToken($client)) {
                    $result = [
                        'success' => false,
                        'error' => '<br>AccessTokenの期限が切れていたから更新しておきました'
                    ];    
                } else {
                    $result = self::buildResultAuthUrl($client);
                }
            } else {
                $result = [
                    'success' => false,
                    'error' => '<br>すでにAccessTokenはセットされています'
                ];
            }
        }
        
        echo json_encode($result);
        die();
    }

    static private function buildResultAuthUrl(Google_Client $client)
    {
        $auth_url = $client->createAuthUrl();
        session_start();
        $_SESSION['auth_success_url'] = $_POST['success_url'];
        return [
            'success' => true,
            'auth_url' => $auth_url
        ];
    }

    /**
     * AccessTokenの更新
     * @param Google_Client $client
     * @return bool
     */
    static function refreshAccessToken(Google_Client $client)
    {
        if ($client->isAccessTokenExpired()) {
            $refresh_token = $client->getRefreshToken();
            if (empty($refresh_token)) {
                return false;
            }
            // 期限が切れていたら更新
            $client->fetchAccessTokenWithRefreshToken();
            file_put_contents(self::getCredentialPath(), json_encode($client->getAccessToken()));
        }
        
        return true;
    }

    /**
     * AccessTokenをファイルに保存する
     * Googleからリダイレクトで呼び出される
     * @param bool $code
     * @return array|bool
     */
    static function createAccessToken($code = false)
    {
        $client = new Google_Client();
        $client->setApplicationName(self::$APPLICATION_NAME);
        $client->setScopes(self::$SCOPES);
        $client->setAuthConfig(self::getClientSecretPath());
        $client->setRedirectUri(self::getRedirectUri());
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        $auth_code = CustomizerUtils::get($_GET, 'code', $code);
        if ($auth_code === false) {
            return false;
        }

        // CODEをAccessTokenに変換
        $credentials_path = self::getCredentialPath();
        $access_token = $client->fetchAccessTokenWithAuthCode($auth_code);

        // AccessTokenの保存
        if(!file_exists(dirname($credentials_path))) {
            mkdir(dirname($credentials_path), 0700, true);
        }
        file_put_contents($credentials_path, json_encode($access_token));
        
        if (isset($_GET['code'])) {
            session_start();
            wp_redirect($_SESSION['auth_success_url']);
            die();    
        }
        
        return $access_token;
    }
    
    /* *******************************
     *       Spread Sheet Control
     * *******************************/

    /**
     * @return Google_Service_Sheets
     */
    static private function getSpreadSheetService()
    {
        $client = self::getClient();
        if (!($client instanceof Google_Client)) {
            echo json_encode([
                'success' => false,
                'error' => '<br>認証情報が不正です。<br>client_secret.jsonは配置されていますか？<br>配置されているなら「認証情報の作成」ボタンを押してください。'
            ]);
            die();
        }

        /**
         * @var $client Google_Client
         */
        return new Google_Service_Sheets($client);
    }

    /**
     * Sheetの作成
     */
    static function createSpreadSheet()
    {
        $responce = [];
        
        self::iAmApi();
        self::minimumCheck();

        // Parameter取得
        $option_group = CustomizerAjax::requireParam($_POST, 'option_group');
        $save_sheet_id_name = CustomizerAjax::requireParam($_POST, 'sheet_id_name');
        $save_sheet_url_name = CustomizerAjax::requireParam($_POST, 'sheet_url_name');
        $sheet_name = CustomizerAjax::requireParam($_POST, 'sheet_name');
        
        // Service作成
        $service = self::getSpreadSheetService();
        // Spread Sheet新規作成
        $requestBody = new Google_Service_Sheets_Spreadsheet();
        $created_spread_sheet = $service->spreadsheets->create($requestBody);
        // Sheet情報を保存
        $spread_sheet_id = $created_spread_sheet->getSpreadsheetId();
        $spread_sheet_url = $created_spread_sheet->getSpreadsheetUrl();
        list($key_A, $sequence_A) = explode('-', $save_sheet_id_name);
        list($key_B, $sequence_B) = explode('-', $save_sheet_url_name);
        CustomizerDatabase::addOption($key_A, $spread_sheet_id, $sequence_A, 'no', true);
        CustomizerDatabase::addOption($key_B, $spread_sheet_url, $sequence_B, 'no', true);
        
        // TODO Sheet名を変更
        
        // Sheetにヘッダ列を追加
        $fields = [];
        $settings = CustomizerConfig::getFormSetting($option_group);
        if (empty($settings)) {
            CustomizerAjax::dieAjax("Form設定「{$option_group}」は存在しません");
        }
        $settings = $settings[$option_group];
        foreach ($settings[SI_CUSTOM_FIELDS] as $setting) {
            foreach ($setting[SI_FIELDS] as $field) {
                $fields[] = $field[SI_NAME];
            }
        }
        $request = new Google_Service_Sheets_ValueRange();
        $request->setValues(['values' => $fields]);
        $service->spreadsheets_values->append(
            $spread_sheet_id, 'シート1', $request, [
                'valueInputOption' => 'USER_ENTERED'
            ]
        );
        
        // 返り値
        $responce['success'] = true;
        $responce['sheet_id'] = $spread_sheet_id;
        $responce['sheet_url'] = $spread_sheet_url;
        echo json_encode($responce);
        die();
    }

    /**
     * @param $record
     * @param $spread_sheet_id
     * @param string $sheet_name
     */
    static function addRecord($record, $spread_sheet_id, $sheet_name = 'Sheet1')
    {
        self::minimumCheck();
        $service = self::getSpreadSheetService();

        $request = new Google_Service_Sheets_ValueRange();
        $append_values = [];
        foreach ($record as $column => $values) {
            $one_value = [];
            foreach ($values as $sequence => $value) {
                $one_value[] = $value;
            }
            $append_values[] = implode(',', $one_value);
        }

        $request->setValues(['values' => $append_values]);
        $service->spreadsheets_values->append(
            $spread_sheet_id, $sheet_name, $request, [
                'valueInputOption' => 'USER_ENTERED'
            ]
        );
    }
}
