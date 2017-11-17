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

    /* *******************************
     *       Google API Auth
     * *******************************/
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
     * スプレッドシートのAPIが利用できるのか
     * @param $sheet_id
     * @return array
     */
    static private function canUseSpreadSheet($sheet_id = null)
    {
        $result = [
            'success' => true,
            'message' => ''
        ];
        // Clientが取得できているか
        if (!(self::$client instanceof Google_Client)) {
            $result['success'] = false;
            $result['message'] = '認証情報が不正です。credential.jsonは配置されていますか？';
        }
        
        if (!is_null($sheet_id)) {
            // TODO SheetIDが有効かどうか
            
        }
        
        return $result;
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
        global $si_logger;
        $credential_path = self::getCredentialPath();
        
        $client = new Google_Client();
        $client->setApplicationName(self::$APPLICATION_NAME);
        $client->setScopes(self::$SCOPES);
        $client->setAuthConfig($credential_path);
//        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $credentialsPath = self::expandHomeDirectory($credential_path);
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            // Store the credentials to disk.
            if(!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0700, true);
            }
            file_put_contents($credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }
    
    /* *******************************
     *       Spread Sheet Control
     * *******************************/
    static function createSpreadSheet()
    {
    } 

}
