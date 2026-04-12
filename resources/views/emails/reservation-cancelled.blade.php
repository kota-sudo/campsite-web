<x-mail::message>
# 予約のキャンセルが完了しました

{{ $reservation->user->name }} 様

以下の予約をキャンセルしました。またのご利用をお待ちしております。

---

**キャンプサイト:** {{ $reservation->campsite->name }}

**チェックイン:** {{ $reservation->check_in_date->isoFormat('YYYY年M月D日(ddd)') }}

**チェックアウト:** {{ $reservation->check_out_date->isoFormat('YYYY年M月D日(ddd)') }}

**宿泊数:** {{ $reservation->nights() }}泊

**人数:** {{ $reservation->num_guests }}名

**合計金額:** ¥{{ number_format($reservation->total_price) }}

---

キャンセルに関するご不明な点がございましたら、お問い合わせください。

<x-mail::button :url="route('campsites.index')">
他のサイトを探す
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
