<?php
namespace Yoco;

use Yoco\Exceptions\ApiKeyException;
use Yoco\Exceptions\DeclinedException;
use Yoco\Exceptions\InternalException;

class YocoClient
{
    protected $secretKey;
    protected $publicKey;
    protected $baseUrl = 'https://online.yoco.com/v1/'\;

    public function __construct($secretKey, $publicKey = null)
    {
        if (empty($secretKey)) {
            throw new ApiKeyException('Secret key is required');
        }
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
    }

    public function charge($token, $amountInCents, $currency = 'ZAR')
    {
        $response = $this->request('charges/', [
            'token'    => $token,
            'amountInCents' => $amountInCents,
            'currency' => $currency,
        ]);

        if (isset($response['errorCode'])) {
            if ($response['errorCode'] === 'card_declined') {
                throw new DeclinedException($response['displayMessage'] ?? 'Card declined');
            }
            throw new InternalException($response['displayMessage'] ?? 'Payment failed');
        }

        return $response;
    }

    protected function request($endpoint, $data)
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}
