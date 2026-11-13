<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pemetaan nilai lama ke nilai baru (satu arah)
        DB::table('intangible_assets')
            ->where('license_type', 'Berlangganan')
            ->update(['license_type' => 'Subscription']);

        DB::table('intangible_assets')
            ->where('license_type', 'Selamanya')
            ->update(['license_type' => 'Perpetual']);

        // Log jumlah data yang terpengaruh untuk audit
        $total = DB::table('intangible_assets')
            ->whereIn('license_type', ['Subscription', 'Perpetual'])
            ->count();
        \Log::info("Migration normalized {$total} legacy license_type values.");
    }

    public function down(): void
    {
        // ⚠️ KEPUTUSAN DESAIN: Down() sengaja TIDAK membalik data.
        //
        // Alasan:
        // Setelah migrasi, kita tidak bisa membedakan antara:
        //   (a) data lama yang sudah kita mapping ke 'Subscription', dan
        //   (b) data baru yang memang sengaja diisi 'Subscription' oleh user.
        // Jika down() dilakukan, data (b) juga akan ter-revert menjadi
        // 'Berlangganan', yang justru MERUSAK data yang seharusnya valid.
        //
        // Untuk data migration, down() yang aman adalah NO-OP.
        // Jika harus rollback, gunakan restore dari backup.
    }
};
