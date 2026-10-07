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
        Schema::table('users', function (Blueprint $table) {
            // Add the person_id column after the id column
            // We make it nullable because existing users won't have a person_id
            $table->foreignId('person_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('people')
                  ->onDelete('set null');
            
            // Add an index for better query performance
            $table->index('person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // First remove the foreign key constraint
            $table->dropForeign(['person_id']);
            // Then remove the column and its index
            $table->dropColumn('person_id');
        });
    }
};