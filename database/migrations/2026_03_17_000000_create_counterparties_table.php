<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counterparties', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->index(['user_id', 'name']);

            $table->string('name');
            $table->string('inn', 20)->nullable();
            $table->string('contract_number', 100)->nullable();
            $table->date('contract_date')->nullable();
            $table->decimal('contract_price', 12, 2)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counterparties');
    }
};

