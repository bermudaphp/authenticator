<?php

namespace Bermuda\Authenticator\User;

interface UserProviderInterface
{
    /**
     * @param string|string[] $identity
     */
    public function retrive(string|array $identity):? UserInterface ;
    public function retriveById(string|array $id):? UserInterface ;
}