@extends('layouts.admin')
@section('title', 'Edit Kampanye')
@section('page-title', 'Edit Kampanye')
@section('page-subtitle', $campaign->title)

@section('content')
<form method="POST" action="{{ route('admin.campaigns.update', $campaign) }}" enctype="multipart/form-data"
    x-data="campaignEditor({{ json_encode($campaign->questions->map(fn($q) => [
        'id' => uniqid(),
        'text' => $q->question_text,
        'type' => $q->question_type,
        'options' => $q->options ?? [],
        'required' => $q->is_required,
    ])->values()->toArray()) }})">

    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-5">

            <div class="card p-6">
                <h3 class="section-title">Informasi Kampanye</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="label">Judul Kampanye <span class="text-red-400">*</span></label>
                        <input type="text" name="title" class="input" required value="{{ old('title', $campaign->title) }}">
                    </div>
                    <div>
                        <label class="label">Nama Sponsor</label>
                        <input type="text" name="sponsor_name" class="input" value="{{ old('sponsor_name', $campaign->sponsor_name) }}">
                    </div>
                    <div>
                        <label class="label">Advertiser</label>
                        <select name="advertiser_id" class="input">
                            <option value="">— Tidak ada —</option>
                            @foreach($advertisers as $adv)
                            <option value="{{ $adv->id }}" {{ old('advertiser_id', $campaign->advertiser_id) == $adv->id ? 'selected' : '' }}>
                                {{ $adv->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="input" value="{{ old('start_date', $campaign->start_date?->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label class="label">Tanggal Selesai</label>
                        <input type="date" name="end_date" class="input" value="{{ old('end_date', $campaign->end_date?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="section-title">Media Iklan</h3>
                <div class="space-y-4">
                    <div>
                        <label class="label">URL Video</label>
                        <input type="url" name="video_url" class="input" value="{{ old('video_url', $campaign->video_url) }}">
                    </div>
                    <div>
                        <label class="label">Minimum Tonton (detik)</label>
                        <input type="number" name="min_watch_duration" class="input" value="{{ old('min_watch_duration', $campaign->min_watch_duration) }}" min="0">
                    </div>
                    <div>
                        <label class="label">Banner Baru (kosongkan jika tidak diubah)</label>
                        @if($campaign->hasBanner())
                        <div class="mb-2">
                            <img src="{{ $campaign->banner_url }}" class="h-16 rounded-lg object-cover" alt="Current banner">
                        </div>
                        @endif
                        <input type="file" name="ad_banner" accept="image/*" class="input py-2">
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_active" name="is_active" value="1"
                            {{ old('is_active', $campaign->is_active) ? 'checked' : '' }} class="w-4 h-4 accent-indigo-500">
                        <label for="is_active" class="text-sm text-slate-300">Kampanye aktif</label>
                    </div>
                </div>
            </div>

            <!-- Question Builder (same as create) -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="section-title mb-0">Survey Questions</h3>
                    <button type="button" @click="addQuestion()" class="btn-primary text-xs py-1.5">+ Tambah</button>
                </div>

                <div class="space-y-4">
                    <template x-for="(q, index) in questions" :key="q.id">
                        <div class="bg-white/3 border border-white/10 rounded-xl p-4">
                            <div class="flex items-start gap-3 mb-3">
                                <span class="text-xs font-bold text-slate-400 mt-2.5 w-5 flex-shrink-0" x-text="index + 1 + '.'"></span>
                                <div class="flex-1">
                                    <input type="text" :name="'questions[' + index + '][text]'" x-model="q.text"
                                        class="input mb-2" placeholder="Teks pertanyaan..." required>
                                    <div class="flex gap-2 flex-wrap">
                                        <select :name="'questions[' + index + '][type]'" x-model="q.type" class="input w-auto text-sm py-1.5">
                                            <option value="single_choice">Pilihan Ganda (1)</option>
                                            <option value="multiple_choice">Pilihan Ganda (banyak)</option>
                                            <option value="text">Teks Bebas</option>
                                            <option value="rating">Rating 1-5</option>
                                        </select>
                                        <label class="flex items-center gap-1.5 text-xs text-slate-400">
                                            <input type="checkbox" :name="'questions[' + index + '][required]'" value="1"
                                                x-model="q.required" class="accent-indigo-500"> Wajib
                                        </label>
                                    </div>
                                </div>
                                <button type="button" @click="questions.splice(index, 1)" class="btn-danger py-1 px-2 text-xs">✕</button>
                            </div>

                            <div x-show="q.type === 'single_choice' || q.type === 'multiple_choice'" x-cloak class="ml-8">
                                <p class="text-xs text-slate-500 mb-2">Opsi Jawaban:</p>
                                <div class="space-y-1.5">
                                    <template x-for="(opt, oi) in q.options" :key="oi">
                                        <div class="flex gap-2">
                                            <input type="text" :name="'questions[' + index + '][options][' + oi + ']'"
                                                x-model="q.options[oi]" class="input py-1.5 text-sm" :placeholder="'Opsi ' + (oi+1)">
                                            <button type="button" @click="q.options.splice(oi, 1)" class="text-slate-500 hover:text-red-400 px-2 text-lg">×</button>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="q.options.push('')" class="mt-2 text-xs text-indigo-400">+ Tambah opsi</button>
                            </div>
                        </div>
                    </template>
                    <div x-show="questions.length === 0" class="text-center py-8 text-slate-500 text-sm border border-dashed border-white/10 rounded-xl">
                        Belum ada pertanyaan.
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card p-5 sticky top-4 space-y-3">
                <h3 class="section-title">Aksi</h3>
                <button type="submit" class="btn-primary w-full">💾 Simpan Perubahan</button>
                <a href="{{ route('admin.campaigns.index') }}" class="btn-secondary w-full text-center block text-sm">Batal</a>
                <hr class="border-white/10">
                <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Hapus kampanye ini beserta semua data survei?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full text-center text-xs text-red-400 hover:text-red-300 py-1">Hapus Kampanye</button>
                </form>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
function campaignEditor(existingQuestions) {
    return {
        questions: existingQuestions.map((q, i) => ({ ...q, id: i + 1 })),
        nextId: existingQuestions.length + 1,
        addQuestion() {
            this.questions.push({ id: this.nextId++, text: '', type: 'single_choice', options: ['', ''], required: true });
        }
    };
}
</script>
@endsection
