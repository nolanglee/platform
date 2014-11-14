<?php

use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\Adapter;
use League\OAuth2\Server\Storage\AuthCodeInterface;

class OAuth2_Storage_AuthCode extends Adapter implements AuthCodeInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($code)
    {
        $query = DB::query(Database::SELECT, '
                SELECT oauth_auth_codes.* 
                  FROM oauth_auth_codes
                 WHERE auth_code = :authCode
                   AND expire_time >= :time')
                    ->param(':authCode', $code)
                    ->param(':time', time());


        $result = $this->select_one_result($query);

        if ($result) {
            $token = new AuthCodeEntity($this->server);
            $token->setId($result['auth_code']);
            $token->setRedirectUri($result['client_redirect_uri']);

            return $token;
        }

        return null;
    }

    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
        return $this->insert('oauth_auth_codes', [
                        'auth_code'         =>  $token,
                        'client_redirect_uri'  =>  $redirectUri,
                        'session_id'    =>  $sessionId,
                        'expire_time'   =>  $expireTime,
                    ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AuthCodeEntity $token)
    {
        $query = DB::query(Database::SELECT, '
                SELECT oauth_scopes.id, oauth_scopes.description 
                  FROM oauth_auth_code_scopes, oauth_scopes
                 WHERE oauth_auth_code_scopes.scope = oauth_scopes.id
                   AND auth_code >= :tokenId')
                    ->param(':tokenId', $token->getId());


        $result = $this->select_results($query);

        $response = [];

        if ($result and sizeof($result) > 0) {
            foreach ($result as $row) {
                $scope = (new ScopeEntity($this->server))->hydrate([
                    'id'            =>  $row['id'],
                    'description'   =>  $row['description'],
                ]);
                $response[] = $scope;
            }
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
        return $this->insert('oauth_auth_code_scopes', 
                        'auth_code' =>  $token->getId(),
                        'scope'     =>  $scope->getId(),
                    ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AuthCodeEntity $token)
    {   
        $this->_delete('oauth_auth_codes', ['auth_code'=>$token->getId()]);
    }
}
