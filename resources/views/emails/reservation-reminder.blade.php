<x-mail::message>
# チェックインまであと3日です

{{ $reservation->user->name }} 様

いよいよチェックインまであと **3日** となりました。楽しいキャンプをお楽しみください！

---

**キャンプサイト:** {{ $reservation->campsite->name }}

**チェックイン:** {{ $reservation->check_in_date->isoFormat('YYYY年M月D日(ddd)') }}

**チェックアウト:** {{ $reservation->check_out_date->isoFormat('YYYY年M月D日(ddd)') }}

**宿泊数:** {{ $reservation->nights() }}泊

**人数:** {{ $reservation->num_guests }}名

**合計金額:** ¥{{ number_format($reservation->total_price) }}

---

キャンセルはチェックイン前日まで無料で承っております。

<x-mail::button :url="route('reservations.show', $reservation)">
予約詳細を確認する
</x-mail::button>

ご不明な点がございましたら、お気軽にお問い合わせください。

{{ config('app.name') }}
</x-mail::message>
