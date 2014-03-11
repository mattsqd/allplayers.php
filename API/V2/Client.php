<?php

namespace AllPlayers\API\V2;

use AllPlayers\Component\HttpClient;

class Client extends HttpClient
{
    /**
     * The agent to connect with.
     *
     * @var string
     */
    protected $agent;

    /**
     * The user attempting to authenticate.
     *
     * @var integer
     */
    protected $user;

    /**
     * The private key content.
     *
     * @var string
     */
    protected $privateKey;

    /**
     * Set authentication settings for this client.
     *
     * @param string $agent
     *   The the name of the key to use for HMAC.
     *   This will be assigned to you by AllPlayers.
     * @param string $private_key
     *   The private key contents.
     * @param string $user_uuid
     *   The uuid of the user to connect as, or a special user (anonymous, etc)
     */
    public function setCredentials($agent, $private_key, $user_uuid = 'anonymous')
    {
        $this->agent = $agent;
        $this->user = $user_uuid;
        $this->privateKey = $private_key;
    }

    /**
     * Send the data to the api server with an HMAC.
     *
     * @param string $path
     *   The path of the url to call.
     * @param array $data
     *   The data to include.
     *
     * @return array|stdClass
     *   Array or object from decodeResponse().
     */
    public function post($path, $data)
    {
        $data = base64_encode(json_encode($data));
        $hmac = null;
        openssl_private_encrypt(hash('sha256', $data), $hmac, $this->privateKey);
        $post_data = array(
            'data' => $data,
            'hmac' => base64_encode($hmac),
            'user' => $this->user,
            'agent' => $this->agent,
        );

        return $this->httpRequest('POST', $path, array(), $post_data);
    }

    /**
     * Create a hashed version of the session id.
     *
     * @return string
     *   A hashed token.
     */
    public static function tokenizeSession()
    {
        return hash('sha256', session_id());
    }
}
