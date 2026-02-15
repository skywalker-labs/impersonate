<?php

return [

    /**
     * The session key used to store the original user id.
     */
    'session_key' => 'impersonated_by',

    /**
     * The session key used to stored the original user guard.
     */
    'session_guard' => 'impersonator_guard',

    /**
     * The session key used to stored what guard is impersonator using.
     */
    'session_guard_using' => 'impersonator_guard_using',

    /**
     * The default impersonator guard used.
     */
    'default_impersonator_guard' => 'web',

    /**
     * The URI to redirect after taking an impersonation.
     *
     * Only used in the built-in controller.
     * * Use 'back' to redirect to the previous page
     */
    'take_redirect_to' => '/',

    /**
     * The URI to redirect after leaving an impersonation.
     *
     * Only used in the built-in controller.
     * Use 'back' to redirect to the previous page
     */
    'leave_redirect_to' => '/',

    /**
     * Audit Logging
     */
    'logging' => false,
    'log_table' => 'impersonation_logs',

    /**
     * Injected UI Bar
     */
    'ui' => [
        'enabled' => true,
        'position' => 'bottom', // 'top' or 'bottom'
        'colors' => [
            'background' => '#1f2937',
            'text' => '#f3f4f6',
        ],
    ],

    /**
     * Impersonation Time-to-Live (TTL)
     *
     * The number of minutes an impersonation session should last.
     * Set to 0 or null to disable auto-expiry.
     */
    'ttl' => 0,

];
