<?php

return [
    /*
     |------------------------------------------------------------------
     | Enable / disable the CT module globally.
     | Set CT_ENABLED=false in .env to return 404 for all /ct routes.
     |------------------------------------------------------------------
     */
    'enabled' => env('CT_ENABLED', true),

    /*
     |------------------------------------------------------------------
     | Cooldown settings (seconds)
     |------------------------------------------------------------------
     */
    'cooldown'           => env('CT_COOLDOWN', 30),
    'cooldown_escalated' => env('CT_COOLDOWN_ESCALATED', 120),
    'escalate_after'     => env('CT_ESCALATE_AFTER', 10),  // wrong attempts before escalation

    /*
     |------------------------------------------------------------------
     | Public URL shown on printed worksheets.
     |------------------------------------------------------------------
     */
    'public_url' => env('CT_PUBLIC_URL', null), // defaults to url('/ct') if null

    /*
     |------------------------------------------------------------------
     | School / teacher info for certificates and worksheets.
     |------------------------------------------------------------------
     */
    'school_name'  => env('CT_SCHOOL_NAME', 'SMA Negeri 1 Batur'),
    'teacher_name' => env('CT_TEACHER_NAME', 'Ade Ariawan, S.Kom'),

    /*
     |------------------------------------------------------------------
     | Maximum code-generation retries before giving up.
     |------------------------------------------------------------------
     */
    'max_gen_retries' => 50,
];
