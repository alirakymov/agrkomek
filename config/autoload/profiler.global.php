<?php

return [
    'profiler' => [
        'save.handler.stack' => [
            'savers' => [
                \Xhgui\Profiler\Profiler::SAVER_UPLOAD,
            ],
            // if saveAll=false, break the chain on successful save
            'saveAll' => false,
        ],
        'save.handler.upload' => [
            # - ToDo change it's to self profiler
            'url' => 'http://qore.profiler/run/import',
            # - The timeout option is in seconds and defaults to 3 if unspecified.
            'timeout' => 3,
            # - the token must match 'upload.token' config in XHGui
            'token' => 'token',
        ],
    ]
];
