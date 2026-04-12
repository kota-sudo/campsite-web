<div class="bg-white rounded-xl border border-[#e0d8cc] shadow-sm p-6">

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @if ($method === 'PUT') @method('PUT') @endif

        <div>
            <x-input-label for="name" value="サイト名 *" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $campsite?->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="description" value="サイトの説明" />
            <textarea id="description" name="description" rows="4"
                      class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#2d5a1b] focus:ring-[#2d5a1b]">{{ old('description', $campsite?->description) }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="type" value="タイプ *" />
                <select id="type" name="type"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#2d5a1b]">
                    @foreach(['tent'=>'⛺ テントサイト', 'auto'=>'🚗 オートキャンプ', 'bungalow'=>'🏠 バンガロー', 'glamping'=>'✨ グランピング'] as $val => $label)
                        <option value="{{ $val }}" {{ old('type', $campsite?->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="capacity" value="最大収容人数 *" />
                <x-text-input id="capacity" name="capacity" type="number" min="1" max="50" class="mt-1 block w-full"
                              :value="old('capacity', $campsite?->capacity)" required />
            </div>
        </div>

        <div>
            <x-input-label for="price_per_night" value="基本料金（円/泊） *" />
            <x-text-input id="price_per_night" name="price_per_night" type="number" min="100" step="100" class="mt-1 block w-full"
                          :value="old('price_per_night', $campsite?->price_per_night)" required />
        </div>

        <div>
            <x-input-label for="weekend_multiplier" value="土日・祝日の料金倍率（例: 1.5 = 150%）" />
            <x-text-input id="weekend_multiplier" name="weekend_multiplier" type="number" min="1.0" max="5.0" step="0.1" class="mt-1 block w-full"
                          :value="old('weekend_multiplier', $campsite?->weekend_multiplier ?? 1.0)" />
            <p class="mt-1 text-xs text-gray-400">1.0 = 変動なし。1.5 なら基本料金の 1.5 倍が土日に自動適用されます。</p>
            <x-input-error :messages="$errors->get('weekend_multiplier')" class="mt-1" />
        </div>

        {{-- 住所・位置 --}}
        <div>
            <x-input-label for="address" value="住所（例: 長野県諏訪郡原村）" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full"
                          :value="old('address', $campsite?->address)" placeholder="任意" />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="latitude" value="緯度" />
                <x-text-input id="latitude" name="latitude" type="number" step="0.0000001" class="mt-1 block w-full"
                              :value="old('latitude', $campsite?->latitude)" placeholder="例: 35.9234" />
            </div>
            <div>
                <x-input-label for="longitude" value="経度" />
                <x-text-input id="longitude" name="longitude" type="number" step="0.0000001" class="mt-1 block w-full"
                              :value="old('longitude', $campsite?->longitude)" placeholder="例: 138.2015" />
            </div>
        </div>

        {{-- 設備 --}}
        <div>
            <x-input-label value="設備・サービス" />
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach ($amenities as $amenity)
                    <label class="flex items-center gap-1.5 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                               class="rounded border-gray-300 text-[#e07b39] focus:ring-[#e07b39]"
                               {{ in_array($amenity->id, old('amenity_ids', $campsite?->amenities->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                        {{ $amenity->name }}
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 画像アップロード --}}
        <div>
            <x-input-label for="images" value="写真（複数可、各5MBまで）" />
            <input type="file" id="images" name="images[]" multiple accept="image/*"
                   class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-lg px-3 py-2 cursor-pointer">
            @if ($campsite && $campsite->images->isNotEmpty())
                <div class="mt-3 flex gap-2 flex-wrap">
                    @foreach ($campsite->images as $img)
                        <div class="w-16 h-16 rounded-lg overflow-hidden border border-gray-200">
                            <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-1">新しい画像をアップロードすると追加されます</p>
            @endif
        </div>

        <div class="pt-2 flex gap-3">
            <button type="submit"
                    class="flex-1 h-11 bg-[#e07b39] hover:bg-[#c4621a] text-white font-bold rounded-lg text-sm transition-colors">
                {{ $campsite ? '更新する' : '登録する' }}
            </button>
            <a href="{{ route('host.campsites.index') }}"
               class="flex-1 h-11 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium inline-flex items-center justify-center transition-colors">
                キャンセル
            </a>
        </div>
    </form>
</div>
