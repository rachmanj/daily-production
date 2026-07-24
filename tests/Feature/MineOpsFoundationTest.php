<?php

use App\Enums\EntryStatus;
use Database\Seeders\DatabaseSeeder;

test('entry status enum has indonesian labels', function () {
    expect(EntryStatus::Draft->label())->toBe('Draf')
        ->and(EntryStatus::Submitted->label())->toBe('Disubmit')
        ->and(EntryStatus::Approved->label())->toBe('Disetujui');
});

test('login page is accessible', function () {
    $response = $this->get('/login');

    $response->assertOk();
});

test('admin can login and access dashboard', function () {
    $this->seed(DatabaseSeeder::class);

    $response = $this->post('/login', [
        'email' => 'admin@mineops.test',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $this->get('/dashboard')->assertOk();
});
