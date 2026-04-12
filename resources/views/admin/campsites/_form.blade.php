<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg text-sm">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if ($method === 'PUT') @method('PUT') @endif

        <div>
            <x-input-label for="name" value="サイト名" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $campsite?->name)" required />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="description" value="説明" />
            <textarea id="description" name="description" rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm">{{ old('description', $campsite?->description) }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-1" />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="type" value="タイプ" />
                <select id="type" name="type"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm">
                    @foreach(['tent' => 'テントサイト', 'auto' => 'オートキャンプ', 'bungalow' => 'バンガロー', 'glamping' => 'グランピング'] as $val => $label)
                        <option value="{{ $val }}" {{ old('type', $campsite?->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="capacity" value="最大収容人数" />
                <x-text-input id="capacity" name="capacity" type="number" min="1" max="50" class="mt-1 block w-full"
                              :value="old('capacity', $campsite?->capacity)" required />
                <x-input-error :messages="$errors->get('capacity')" class="mt-1" />
            </div>
        </div>

        <div>
            <x-input-label for="price_per_night" value="1泊料金（円）" />
            <x-text-input id="price_per_night" name="price_per_night" type="number" min="100" step="100" class="mt-1 block w-full"
                          :value="old('price_per_night', $campsite?->price_per_night)" required />
            <x-input-error :messages="$errors->get('price_per_night')" class="mt-1" />
        </div>

        <div>
            <x-input-label value="設備・サービス" />
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach ($amenities as $amenity)
                    <label class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                               class="rounded border-gray-300 dark:border-gray-600 text-[#2d5a1b]"
                               {{ in_array($amenity->id, old('amenity_ids', $campsite?->amenities->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                        {{ $amenity->name }}
                    </label>
                @endforeach
            </div>
        </div>

        {{-- 位置情報 --}}
        <div>
            <x-input-label for="address" value="住所（例: 長野県諏訪郡原村）" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full"
                          :value="old('address', $campsite?->address)" placeholder="任意" />
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="latitude" value="緯度（例: 35.9234）" />
                <x-text-input id="latitude" name="latitude" type="number" step="0.0000001" class="mt-1 block w-full"
                              :value="old('latitude', $campsite?->latitude)" placeholder="任意" />
                <x-input-error :messages="$errors->get('latitude')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="longitude" value="経度（例: 138.2015）" />
                <x-text-input id="longitude" name="longitude" type="number" step="0.0000001" class="mt-1 block w-full"
                              :value="old('longitude', $campsite?->longitude)" placeholder="任意" />
                <x-input-error :messages="$errors->get('longitude')" class="mt-1" />
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="is_active" name="is_active" value="1"
                   class="rounded border-gray-300 dark:border-gray-600 text-[#2d5a1b]"
                   {{ old('is_active', $campsite?->is_active ?? true) ? 'checked' : '' }}>
            <x-input-label for="is_active" value="公開する" class="!mb-0" />
        </div>

        <div class="flex gap-3 pt-2">
            <x-primary-button type="submit">{{ $campsite ? '更新する' : '追加する' }}</x-primary-button>
            <a href="{{ route('admin.campsites.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md text-sm hover:bg-gray-300">
                キャンセル
            </a>
        </div>
    </form>
</div>
