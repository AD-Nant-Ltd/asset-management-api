<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_subtypes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_type_id')
                ->constrained('asset_types');

            $table->string('asset_subtype', 100);
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(['asset_type_id', 'asset_subtype']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_subtypes');
    }
};