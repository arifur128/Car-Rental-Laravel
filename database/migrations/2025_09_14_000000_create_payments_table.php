<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('order_id')->nullable(); // if you have orders table, you can convert to foreignId
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('status', 20)->default('pending'); // pending|success|failed|refunded
            $table->string('transaction_id', 64)->unique();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
