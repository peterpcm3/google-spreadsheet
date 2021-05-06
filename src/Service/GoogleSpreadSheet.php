<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use \Google_Client;

class GoogleSpreadSheet
{
    /**
     * @var string GOOGLE_AUTH_TOKEN_PATH
     */
    const GOOGLE_AUTH_TOKEN_PATH = '/tmp/app/googletoken.json';

    /**
     * @var Google_Client $googleClient
     */
    private $googleClient;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var string|null $spreadSheetId
     */
    private $spreadSheetId = null;

    /**
     * GoogleSpreadSheet constructor
     *
     * @param string $googleTokenPath
     * @param Google_Client $googleClient
     * @param \App\Service\LoggerInterface $logger
     */
    public function __construct(string $googleTokenPath, Google_Client $googleClient, LoggerInterface $logger)
    {
        $this->googleClient = $googleClient;
        $this->authoriseGoogleClient($googleTokenPath);

        $this->logger = $logger;
    }

    /**
     * Write data to spreadsheet
     *
     * @param array $data
     *
     * @return boolean
     */
    public function writeData(array $data)
    {
        if(!$this->spreadSheetId && !$this->createSpreadSheet()) {
            return false;
        }

        $sheetService = new \Google_Service_Sheets($this->googleClient);

        try {
            $range = 'A1:B1';
            $body = new \Google_Service_Sheets_ValueRange([
                'values' => [array_values($data)]
            ]);
            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];
            $result = $sheetService->spreadsheets_values->append($this->spreadSheetId, $range, $body, $params);
        } catch(Exception $e) {
            $this->logger->error('Can\'t write in spreadsheet' . $e->getMessage());
            throw new \Exception('Error on writing in a spreadsheet');
        }

        return true;
    }

    /**
     * Create spreadsheet if doesn't exists for current session
     *
     * @return boolean
     */
    private function createSpreadSheet()
    {
        try {
            $sheetService = new \Google_Service_Sheets($this->googleClient);

            $spreadSheetService = new \Google_Service_Sheets_Spreadsheet([
                'properties' => [
                    'title' => 'Xml Data #' . time()
                ]
            ]);

            $spreadsheet = $sheetService->spreadsheets->create($spreadSheetService, [
                'fields' => 'spreadsheetId'
            ]);

            $this->spreadSheetId = $spreadsheet->spreadsheetId;
        }
        catch (\Exception $e) {
            $this->logger->error('Can\'t create spreadsheet' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Set access token for google client
     *
     * @param string $googleTokenPath
     */
    private function authoriseGoogleClient(string $googleTokenPath)
    {
        $this->googleClient = new \Google_Client();

        $this->googleClient->setApplicationName('Google Sheets API');
        $this->googleClient->setScopes(\Google_Service_Sheets::SPREADSHEETS);
        $this->googleClient->setAuthConfig($googleTokenPath);
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = self::GOOGLE_AUTH_TOKEN_PATH;
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->googleClient->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($this->googleClient->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($this->googleClient->getRefreshToken()) {
                $this->googleClient->fetchAccessTokenWithRefreshToken($this->googleClient->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $this->googleClient->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $this->googleClient->fetchAccessTokenWithAuthCode($authCode);
                $this->googleClient->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($this->googleClient->getAccessToken()));
        }
    }
}
