<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_letters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('number');
            $table->string('status', 32)->default('issued');
            $table->date('issued_at')->nullable();
            $table->string('destination')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('issued_by')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'number'], 'delivery_letters_company_number_unique');
            $table->index(['company_id', 'issued_at'], 'delivery_letters_company_issued_index');
            $table->index(['company_id', 'invoice_id'], 'delivery_letters_company_invoice_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_letters');
    }
};
