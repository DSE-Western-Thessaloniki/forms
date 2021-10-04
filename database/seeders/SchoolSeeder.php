<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolCategory;
use App\Models\User;
use Database\Factories\SchoolFactory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('username', 'admin0')->first();

        $gymnasio = SchoolCategory::where('name', 'ΓΥΜΝΑΣΙΑ')->first();
        $gel = SchoolCategory::where('name', 'ΓΕΛ')->first();
        $epal = SchoolCategory::where('name', 'ΕΠΑΛ')->first();
        $smeae = SchoolCategory::where('name', 'ΣΜΕΑΕ')->first();

        if (!($user && $gymnasio && $gel && $epal && $smeae)) {
            throw new \Exception('You have to run UserSeeder and SchoolCategorySeeder first!');
        }

        // Φτιάξε 10 Γυμνάσια
        $schools = School::factory()
            ->count(10)
            ->state(new Sequence(fn ($sequence) => [
                'username' => '99'.$sequence->index,
                'active' => $sequence->index % 2,
            ]))
            ->category(SchoolFactory::gymnasio)
            ->for($user)
            ->create();


        foreach ($schools as $school) {
            $school->categories()->attach($gymnasio);
            if ($school->username % 2 === 0) {
                $school->categories()->attach($smeae);
            }
        }

        // Φτιάξε 10 ΓΕΛ
        $schools = School::factory()
            ->count(10)
            ->state(new Sequence(fn ($sequence) => [
                'username' => '88'.$sequence->index,
                'active' => $sequence->index % 2,
            ]))
            ->category(SchoolFactory::gymnasio)
            ->for($user)
            ->create();

        foreach ($schools as $school) {
            $school->categories()->attach($gel);
            if ($school->username % 2 === 0) {
                $school->categories()->attach($smeae);
            }
        }

        // Φτιάξε 10 ΕΠΑΛ
        $schools = School::factory()
            ->count(10)
            ->state(new Sequence(fn ($sequence) => [
                'username' => '77'.$sequence->index,
                'active' => $sequence->index % 2,
            ]))
            ->category(SchoolFactory::epal)
            ->for($user)
            ->create();


        foreach ($schools as $school) {
            $school->categories()->attach($epal);
            if ($school->username % 2 === 0) {
                $school->categories()->attach($smeae);
            }
        }
    }
}
