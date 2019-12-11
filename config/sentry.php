<?php

return array(
    'dsn' => 'https://2841516f4ef04d8bbd82e9233a0fb6a2@sentry.io/1444639',

    // capture release as git sha
    'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),
    //'release' => 'laravel@1.2.0'
);