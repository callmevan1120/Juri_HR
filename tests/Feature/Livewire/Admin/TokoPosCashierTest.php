<?php

use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test('admin.toko-pos-cashier')
        ->assertStatus(200);
});
