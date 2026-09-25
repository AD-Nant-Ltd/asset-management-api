<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')
                ->constrained('assets');

            $table->foreignId('assigned_to_id')
                ->constrained('staff');

            $table->foreignId('assigned_by_id')
                ->constrained('users');

            $table->date('assigned_date');

            $table->date('returned_date')->nullable();

            $table->foreignId('returned_by_id')
                ->nullable()
                ->constrained('users');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};