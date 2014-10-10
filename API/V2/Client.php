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
      * {@inheritdoc}
      */
    protected function httpRequest(
        $verb,
        $path,
        $query = array(),
        $params = NULL,
        $headers = array(),
        $allow_redirects = TRUE
    ) {
        // Only add the hmac auth if we have all auth data set.
        if (strlen($this->privateKey) && strlen($this->user) && strlen($this->agent)) {
            $time = time();
            // Create a signed request using the current time.
            if (TRUE === openssl_private_encrypt(hash('sha256', $time), $hmac, $this->privateKey)) {
                // Prioritize headers that were already set, the added headers
                // will not overwrite them.
                $headers = array_merge(
                    array(
                        'hmac' => base64_encode($hmac),
                        'time' => $time,
                        'user' => $this->user,
                        'agent' => $this->agent,
                    ),
                    $headers
                );
            }
        }
        return parent::httpRequest($verb, $path, $query, $params, $headers, $allow_redirects);
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

    /**
     * Send a batch operation to the API.
     *
     * @param array $batch
     *   An array of calls.
     *
     * @return array
     *   An array of results.
     */
    public function batch($batch)
    {
        return $this->post('batch', array('batch' => $batch));
    }

    /**
     * Retrieves a list of products available for a group.
     *
     * @param string $group_uuid
     *   UUID of the group to retrieve the products for.
     * @param array $filters
     *   (Optional) Filters to be applied to the query. Possible values are:
     *   - status: The product status. May be an array of statuses or a single
     *     status. Possible values are 1 (active) and 0 (disabled).
     *   - type: The product type. May be an array of types or a single type.
     *   - title: A portion of the product type. Must be a string and will be used
     *     as a "contains" filter.
     * @param array $fields
     *   (Optional) Fields to be returned for each product. If left empty, all
     *   fields will be retured.
     * @param integer|string $page
     *   (Optional) Numeric page number or '*' to fetch all pages.
     *   Default to 0. If using '*', it is recommended to set a high $pagesize
     *   to reduce the number of requests needed to retrieve the entire list.
     * @param integer $pagesize
     *   (Optional) Limit the number of results returned per page. If not set,
     *   then we default to 10.
     *   NOTE: This does not limit the overall return set when using the '*'
     *   page parameter.
     *
     * @return array
     *   Array of product objects.
     */
    public function getGroupProducts(
        $group_uuid,
        array $filters = array(),
        array $fields = array(),
        $page = 0,
        $pagesize = 10
    ) {
        return $this->index(
            'group/' . $group_uuid . '/products',
            array('filters' => $filters),
            $fields,
            $page,
            $pagesize
        );
    }
}
