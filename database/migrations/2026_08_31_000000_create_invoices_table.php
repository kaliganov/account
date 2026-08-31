<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counterparty_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('period', 7);
            $table->date('issued_on');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('filename');
            $table->timestamps();

            $table->index(['user_id', 'period']);
            $table->index(['user_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
