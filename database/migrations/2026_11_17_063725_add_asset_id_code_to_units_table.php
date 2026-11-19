<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('asset_id_code', 10)->nullable()->after('name');
        });

        // Mapping Kode ID Aset
        $mapping = [
            'Teknik Informatika' => 'TI',
            'Teknik Elektro' => 'TE',
            'Teknik Mesin' => 'TM',
            // Selain prodi di atas, masuk ke UM
        ];

        $units = DB::table('units')->get();
        foreach ($units as $unit) {
            $code = $mapping[$unit->name] ?? 'UM';
            DB::table('units')->where('id', $unit->id)->update(['asset_id_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('asset_id_code');
        });
    }
};
