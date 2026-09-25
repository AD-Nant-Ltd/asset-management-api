<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_subtype_id')
                ->constrained('asset_subtypes');

            $table->foreignId('asset_status_id')
                ->constrained('asset_statuses');

            $table->foreignId('asset_condition_id')
                ->constrained('asset_conditions');

            $table->date('delivery_date')->nullable();

            $table->string('asset_tag', 100)->unique();
            $table->string('serial_num')->nullable()->unique();

            $table->date('purchase_date')->nullable();
            $table->date('retired_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('disposal_date')->nullable();

            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};