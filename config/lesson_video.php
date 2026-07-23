<?php

return [
    'source_disk' => env(
        'LESSON_VIDEO_SOURCE_DISK',
        'lesson_source'
    ),

    'hls_disk' => env(
        'LESSON_VIDEO_HLS_DISK',
        'lesson_hls'
    ),

    'ffmpeg' => env(
        'LESSON_VIDEO_FFMPEG',
        '/usr/bin/ffmpeg'
    ),

    'ffprobe' => env(
        'LESSON_VIDEO_FFPROBE',
        '/usr/bin/ffprobe'
    ),

    /*
    |--------------------------------------------------------------------------
    | HLS segmentation
    |--------------------------------------------------------------------------
    */

    'segment_seconds' => (int) env(
        'LESSON_VIDEO_SEGMENT_SECONDS',
        10
    ),

    /*
    |--------------------------------------------------------------------------
    | Playback session
    |--------------------------------------------------------------------------
    */

    'session_idle_minutes' => (int) env(
        'LESSON_VIDEO_SESSION_IDLE_MINUTES',
        20
    ),

    'session_max_minutes' => (int) env(
        'LESSON_VIDEO_SESSION_MAX_MINUTES',
        120
    ),

    /*
    |--------------------------------------------------------------------------
    | Signed segment URL
    |--------------------------------------------------------------------------
    |
    | يجب أن تكون أطول قليلًا من زمن المقطع حتى يتحمل الاتصال البطيء وإعادة
    | المحاولة. المقطع 10 ثوانٍ والرابط 20 ثانية.
    |
    */

    'ticket_ttl_seconds' => (int) env(
        'LESSON_VIDEO_TICKET_TTL_SECONDS',
        20
    ),

    /*
    |--------------------------------------------------------------------------
    | Session protection
    |--------------------------------------------------------------------------
    */

    'single_session_per_lesson' => filter_var(
        env('LESSON_VIDEO_SINGLE_SESSION', true),
        FILTER_VALIDATE_BOOL
    ),

    'bind_user_agent' => filter_var(
        env('LESSON_VIDEO_BIND_USER_AGENT', true),
        FILTER_VALIDATE_BOOL
    ),

    /*
     * ربط IP يمكن أن يسبب توقف الفيديو عند تغيّر شبكة الهاتف أو VPN،
     * ولذلك هو معطل افتراضيًا.
     */
    'bind_ip' => filter_var(
        env('LESSON_VIDEO_BIND_IP', false),
        FILTER_VALIDATE_BOOL
    ),

    /*
    |--------------------------------------------------------------------------
    | Adaptive quality profiles
    |--------------------------------------------------------------------------
    */

    'profiles' => [
        '360p' => [
            'width' => 640,
            'height' => 360,
            'video_bitrate' => '800k',
            'maxrate' => '856k',
            'bufsize' => '1200k',
            'audio_bitrate' => '96k',
        ],

        '480p' => [
            'width' => 854,
            'height' => 480,
            'video_bitrate' => '1400k',
            'maxrate' => '1498k',
            'bufsize' => '2100k',
            'audio_bitrate' => '128k',
        ],

        '720p' => [
            'width' => 1280,
            'height' => 720,
            'video_bitrate' => '2800k',
            'maxrate' => '2996k',
            'bufsize' => '4200k',
            'audio_bitrate' => '128k',
        ],

        '1080p' => [
            'width' => 1920,
            'height' => 1080,
            'video_bitrate' => '5000k',
            'maxrate' => '5350k',
            'bufsize' => '7500k',
            'audio_bitrate' => '160k',
        ],
    ],
];
