<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 6)->default(1.000000);
            $table->decimal('amount_in_base_currency', 15, 2);
            $table->date('payment_date');

            // Payment method/channel
            $table->enum('method', ['cash', 'check', 'bank_transfer', 'credit_card', 'paypal', 'mobile_money', 'other'])->default('other');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            // PayChangu integration fields
            $table->string('gateway')->default('manual')->comment('manual, paychangu, etc.');
            $table->string('status')->default('completed')->comment('pending, processing, completed, failed, refunded');
            $table->string('tx_ref')->nullable()->unique()->comment('PayChangu transaction reference');
            $table->string('gateway_reference')->nullable()->comment('Gateway internal reference');
            $table->string('channel')->nullable()->comment('Card, Mobile Money, Bank Transfer');
            $table->json('gateway_response')->nullable()->comment('Full webhook/API response from payment gateway');
            $table->json('customer_details')->nullable()->comment('Customer info from gateway');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
