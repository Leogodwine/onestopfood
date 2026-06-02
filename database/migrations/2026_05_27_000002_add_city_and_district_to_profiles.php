<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->string('city')->nullable()->after('street_address');
            $table->string('district')->nullable()->after('city');
        });

        Schema::table('traveler_profiles', function (Blueprint $table) {
            $table->string('city')->nullable()->after('street_address');
            $table->string('district')->nullable()->after('city');
        });

        $this->migrateExistingValues('chef_profiles');
        $this->migrateExistingValues('traveler_profiles');
    }

    public function down(): void
    {
        Schema::table('chef_profiles', function (Blueprint $table) {
            $table->dropColumn(['city', 'district']);
        });

        Schema::table('traveler_profiles', function (Blueprint $table) {
            $table->dropColumn(['city', 'district']);
        });
    }

    private function migrateExistingValues(string $table): void
    {
        $cities = config('tanzania.cities', []);

        DB::table($table)
            ->whereNotNull('city_district')
            ->where('city_district', '!=', '')
            ->orderBy('id')
            ->chunk(100, function ($rows) use ($table, $cities) {
                foreach ($rows as $row) {
                    $value = $row->city_district;
                    $city = null;
                    $district = null;

                    if (array_key_exists($value, $cities)) {
                        $city = $value;
                    } else {
                        foreach ($cities as $region => $districts) {
                            if (in_array($value, $districts, true)) {
                                $city = $region;
                                $district = $value;
                                break;
                            }
                        }

                        if ($city === null) {
                            $city = $value;
                        }
                    }

                    DB::table($table)->where('id', $row->id)->update([
                        'city' => $city,
                        'district' => $district,
                    ]);
                }
            });
    }
};
