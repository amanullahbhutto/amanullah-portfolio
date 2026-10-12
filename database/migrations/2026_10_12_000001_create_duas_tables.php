<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duas', function (Blueprint $table) {
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

        Schema::create('user_dua_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dua_id')->constrained('duas')->cascadeOnDelete();
            $table->unsignedBigInteger('total_completed')->default(0);
            $table->date('tracking_start_date');
            $table->timestamp('last_dua_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'dua_id']);
            $table->index(['user_id', 'tracking_start_date']);
        });

        Schema::create('user_daily_duas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dua_id')->constrained('duas')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'dua_id', 'date']);
            $table->index(['user_id', 'date']);
        });

        // Insert Default Master Duas
        $defaultDuas = [
            [
                'title' => 'Dua for Goodness in Both Worlds (ربنا آتنا)',
                'arabic_text' => 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ',
                'urdu_meaning' => 'اے ہمارے رب! ہمیں دنیا میں بھی بھلائی عطا فرما اور آخرت میں بھی بھلائی عطا فرما اور ہمیں آگ کے عذاب سے بچا۔',
                'daily_target' => 100,
                'sort_order' => 1,
                'is_active' => true,
                'description' => 'Comprehensive Quranic Dua for success in Dunya and Akhirah',
                'transliteration' => 'Rabbana atina fid-dunya hasanatan wa fil-akhirati hasanatan wa qina \'adhaban-nar',
                'reference' => 'Surah Al-Baqarah (2:201)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Ayat-ul-Kursi (آیۃ الکرسی)',
                'arabic_text' => 'اللَّهُ لَا إِلٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ ۚ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ ۚ لَّهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ',
                'urdu_meaning' => 'اللہ، اس کے سوا کوئی معبود نہیں، وہ زندہ ہے سب کا تھامنے والا، نہ اسے اونگھ آتی ہے نہ نیند، اسی کا ہے جو کچھ آسمانوں میں ہے اور جو کچھ زمین میں ہے۔',
                'daily_target' => 100,
                'sort_order' => 2,
                'is_active' => true,
                'description' => 'The greatest verse of the Quran for protection and blessings',
                'transliteration' => 'Allahu la ilaha illa huwal-hayyul-qayyum...',
                'reference' => 'Surah Al-Baqarah (2:255)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Dua for Knowledge & Wisdom (رب زدنی علما)',
                'arabic_text' => 'رَبِّ زِدْنِي عِلْمًا',
                'urdu_meaning' => 'اے میرے پروردگار! میرے علم میں اضافہ فرما۔',
                'daily_target' => 100,
                'sort_order' => 3,
                'is_active' => true,
                'description' => 'Supplication for beneficial knowledge, memory, and comprehension',
                'transliteration' => 'Rabbi zidni \'ilma',
                'reference' => 'Surah Taha (20:114)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Dua of Hazrat Yunus (A.S) / Relief in Distress',
                'arabic_text' => 'لَا إِلٰهَ إِلَّا أَنْتَ سُبْحَانَكَ إِنِّي كُنْتُ مِنَ الظَّالِمِينَ',
                'urdu_meaning' => 'تیرے سوا کوئی معبود نہیں، تو پاک ہے، بے شک میں ہی قصورواروں میں سے تھا۔',
                'daily_target' => 100,
                'sort_order' => 4,
                'is_active' => true,
                'description' => 'Powerful Dua for removal of grief, anxiety, and impossible difficulties',
                'transliteration' => 'La ilaha illa anta subhanaka inni kuntu minaz-zalimin',
                'reference' => 'Surah Al-Anbiya (21:87)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Sayyidul Istighfar (سید الاستغفار)',
                'arabic_text' => 'اللَّهُمَّ أَنْتَ رَبِّي لَا إِلٰهَ إِلَّا أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ، وَأَنَا عَلَىٰ عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ',
                'urdu_meaning' => 'اے اللہ! تو میرا رب ہے، تیرے سوا کوئی معبود نہیں، تو نے مجھے پیدا کیا اور میں تیرا بندہ ہوں اور میں تیرے عہد اور وعدے پر قائم ہوں اپنی طاقت کے مطابق۔',
                'daily_target' => 100,
                'sort_order' => 5,
                'is_active' => true,
                'description' => 'The supreme prayer for forgiveness',
                'transliteration' => 'Allahumma anta rabbi la ilaha illa anta...',
                'reference' => 'Sahih Al-Bukhari',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('duas')->insert($defaultDuas);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_daily_duas');
        Schema::dropIfExists('user_dua_progress');
        Schema::dropIfExists('duas');
    }
};

