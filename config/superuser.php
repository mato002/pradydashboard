<?php

return [

    /**
     * Built-in Super Admin account (full permissions via super_admin role).
     * Credentials are fixed in code; provisioned on application boot.
     */
    'email' => 'superuser@pradytecai.com',

    'password' => 'password',

    'name' => 'Super User',

    /** Skip role-elevation re-auth for this account only. */
    'bypass_elevation' => true,

];
