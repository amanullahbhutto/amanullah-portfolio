<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasbeehs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('arabic_text');
            $table->text('urdu_meaning')->nullable();
            $table->unsignedInteger('daily_target')->default(100);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->text('transliteration')->nullable();
            $table->string('reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('user_tasbeeh_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tasbeeh_id')->constrained('tasbeehs')->cascadeOnDelete();
            $table->unsignedBigInteger('total_completed')->default(0);
            $table->date('tracking_start_date');
            $table->timestamp('last_zikr_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tasbeeh_id']);
            $table->index(['user_id', 'tracking_start_date']);
        });

        // Insert Default Master Tasbeehs
        $defaultTasbeehs = [
            [
                'title' => 'Tasbeeh-e-Fatima / Daily Zikr',
                'arabic_text' => 'سُبْحَانَ اللهِ، وَالْحَمْدُ لِلّٰهِ، وَلَا إِلٰهَ إِلَّا اللهُ، وَاللهُ أَكْبَرُ',
                'urdu_meaning' => 'اللہ پاک ہے، تمام تعریفیں اللہ کے لیے ہیں، اللہ کے سوا کوئی معبود نہیں، اور اللہ سب سے بڑا ہے۔',
                'daily_target' => 100,
                'sort_order' => 1,
                'is_active' => true,
                'description' => 'Subhan Allah, Alhamdulillah, La ilaha illallah, Allahu Akbar',
                'reference' => 'Sahih Muslim',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Darood-e-Pak',
                'arabic_text' => 'اللَّهُمَّ صَلِّ عَلَىٰ مُحَمَّدٍ وَعَلَىٰ آلِ مُحَمَّدٍ',
                'urdu_meaning' => 'اے اللہ! رحمت نازل فرما حضرت محمد ﷺ پر اور ان کی آل پر۔',
                'daily_target' => 100,
                'sort_order' => 2,
                'is_active' => true,
                'description' => 'Salawat on Prophet Muhammad (PBUH)',
                'reference' => 'Sunan an-Nasa\'i',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Istighfar (Astaghfirullah)',
                'arabic_text' => 'أَسْتَغْفِرُ اللّٰهَ رَبِّي مِنْ كُلِّ ذَنْبٍ وَّأَتُوبُ إِلَيْهِ',
                'urdu_meaning' => 'میں اپنے رب اللہ سے اپنے تمام گناہوں کی معافی مانگتا ہوں اور اسی کی بارگاہ میں توبہ کرتا ہوں۔',
                'daily_target' => 100,
                'sort_order' => 3,
                'is_active' => true,
                'description' => 'Seeking forgiveness from Allah Almighty',
                'reference' => 'Al-Qur\'an (Surah Nuh)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Ayat-e-Kareema',
                'arabic_text' => 'لَا إِلٰهَ إِلَّا أَنْتَ سُبْحَانَكَ إِنِّي كُنْتُ مِنَ الظَّالِمِينَ',
                'urdu_meaning' => 'تیرے سوا کوئی معبود نہیں، تو پاک ہے، بے شک میں ہی ظالموں میں سے تھا۔',
                'daily_target' => 100,
                'sort_order' => 4,
                'is_active' => true,
                'description' => 'Dua of Hazrat Yunus (A.S) in distress',
                'reference' => 'Surah Al-Anbiya (21:87)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Third Kalima (Tamjeed)',
                'arabic_text' => 'سُبْحَانَ اللهِ وَالْحَمْدُ لِلّٰهِ وَلَا إِلٰهَ إِلَّا اللهُ وَاللهُ أَكْبَرُ وَلَا حَوْلَ وَلَا قُوَّةَ إِلَّا بِاللّٰهِ الْعَلِيِّ الْعَظِيمِ',
                'urdu_meaning' => 'اللہ پاک ہے اور سب تعریفیں اللہ ہی کے لیے ہیں اور اللہ کے سوا کوئی معبود نہیں اور اللہ سب سے بڑا ہے، اور گناہوں سے بچنے کی طاقت اور نیکی کرنے کی قوت نہیں مگر اللہ کی توفیق سے جو بہت بلند اور عظمت والا ہے۔',
                'daily_target' => 100,
                'sort_order' => 5,
                'is_active' => true,
                'description' => 'Kalima Tamjeed (Glory of Allah)',
                'reference' => 'Sunan Abi Dawud',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('tasbeehs')->insert($defaultTasbeehs);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tasbeeh_progress');
        Schema::dropIfExists('tasbeehs');
    }
};

