<?php

use App\Livewire\Admin\BarcodeComponent;
use App\Models\Barcode;
use App\Models\User;
use Livewire\Livewire;

test('admin barcode cards expose real map links for checkpoint coordinates', function () {
    $admin = User::factory()->admin()->create();
    Barcode::factory()->create([
        'name' => 'Map Link Checkpoint',
        'latitude' => -6.2,
        'longitude' => 106.8,
    ]);

    $this->actingAs($admin);

    Livewire::test(BarcodeComponent::class)
        ->assertSee('Map Link Checkpoint')
        ->assertSee('https://www.google.com/maps/search/?api=1&query=-6.2,106.8', false);
});

test('admin barcode component can open delete confirmation and delete barcode', function () {
    $superadmin = User::factory()->admin(true)->create();
    $barcode = Barcode::factory()->create();

    $this->actingAs($superadmin);

    Livewire::test(BarcodeComponent::class)
        ->call('confirmDeletion', $barcode->id)
        ->assertSet('confirmingDeletion', true)
        ->assertSet('deleteName', $barcode->name)
        ->call('delete')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('barcodes', ['id' => $barcode->id]);
});
