<?php

return [
    // Fake Gateway master switch (just for reference; we’re not branching on this now)
    'fake_mode'    => env('PAYMENT_FAKE_MODE', true),

    // If true => all payments succeed.
    // If false => 70% chance success (see controller).
    'auto_success' => env('PAYMENT_AUTO_SUCCESS', true),

    // Simulated gateway latency (milliseconds)
    'delay_ms'     => env('PAYMENT_DELAY_MS', 600),
];
