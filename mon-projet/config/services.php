'openai' => [
    'key'      => env('OPENAI_API_KEY'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'model'    => env('OPENAI_MODEL', 'gpt-4o-mini'),
],
'stripe' => [
    'secret'   => env('STRIPE_SECRET_KEY'),
    'public'   => env('STRIPE_PUBLISHABLE_KEY'),
    'webhook'  => env('STRIPE_WEBHOOK_SECRET'),
],