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
        Schema::table('products', function (Blueprint $table) {
            // add fulltext on products (name, description)
            DB::statement('ALTER TABLE products ADD FULLTEXT fulltext_idx_product_name_description (name, description)');

            // add fulltext on product_variants sku (and attributes converted to text)
            // We add an indexed computed column for attributes JSON -> text (if needed)
            // Simpler: add FULLTEXT on sku only
            DB::statement('ALTER TABLE product_variants ADD FULLTEXT fulltext_idx_variant_sku (sku)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // MySQL drop fulltext indexes by name
            DB::statement('ALTER TABLE products DROP INDEX fulltext_idx_product_name_description');
            DB::statement('ALTER TABLE product_variants DROP INDEX fulltext_idx_variant_sku');
        });
    }
};
