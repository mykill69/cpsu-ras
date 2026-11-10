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
        Schema::create('file_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')
                  ->constrained('files')
                  ->onDelete('cascade')
                  ->comment('The parent file this version belongs to');
            $table->string('path')
                  ->comment('Original file path');
            $table->string('encrypted_path')
                  ->nullable()
                  ->comment('Encrypted file path if encryption is enabled');
            $table->bigInteger('size')
                  ->comment('File size in bytes');
            $table->string('checksum_sha256')
                  ->comment('SHA-256 hash for file integrity verification');
            $table->integer('version_number')
                  ->comment('Sequential version number');
            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->comment('User who uploaded this version');
            $table->text('change_notes')
                  ->nullable()
                  ->comment('Notes about changes in this version');
            $table->timestamps();

            // Indexes for performance
            $table->index(['file_id', 'version_number']);
            $table->unique(['file_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_versions');
    }
};