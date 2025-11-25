<?php

namespace SmartDato\Dpd\Clients;

class LoginServiceClient extends BaseSoapClient
{
    /**
     * Authenticate with DPD and get an authentication token.
     *
     * @param  string  $delisId  The DPD customer ID
     * @param  string  $password  The DPD password
     * @param  string  $messageLanguage  The message language (default: de_DE)
     * @return string The authentication token
     */
    public function getAuth(string $delisId, string $password, string $messageLanguage = 'de_DE'): string
    {
        $params = [
            'delisId' => $delisId,
            'password' => $password,
            'messageLanguage' => $messageLanguage,
        ];

        $response = $this->call('getAuth', [$params]);

        // Extract the authToken from the response
        return $response->return->authToken ?? throw new \RuntimeException('No authToken in response');
    }
}
