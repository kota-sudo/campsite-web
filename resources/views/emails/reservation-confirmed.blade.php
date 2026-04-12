<x-mail::message>
# ご予約を受け付けました

{{ $reservation->user->name }} 様

この度は {{ config('app.name') }} をご利用いただきありがとうございます。
以下の内容でご予約を受け付けました。

---

**キャンプサイト:** {{ $reservation->campsite->name }}

**チェックイン:** {{ $reservation->check_in_date->isoFormat('YYYY年M月D日(ddd)') }}

**チェックアウト:** {{ $reservation->check_out_date->isoFormat('YYYY年M月D日(ddd)') }}

**宿泊数:** {{ $reservation->nights() }}泊

**人数:** {{ $reservation->num_guests }}名

**合計金額:** ¥{{ number_format($reservation->total_price) }}

**ステータス:** 確認中

@if($reservation->notes)
**備考:** {{ $reservation->notes }}
@endif

---

ご予約の確認・キャンセルはマイ予約ページからご確認いただけます。

<x-mail::button :url="route('reservations.show', $reservation)">
予約詳細を確認する
</x-mail::button>

ご不明な点がございましたら、お気軽にお問い合わせください。

{{ config('app.name') }}
</x-mail::message>
