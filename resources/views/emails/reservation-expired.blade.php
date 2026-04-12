<x-mail::message>
# ご予約が自動キャンセルされました

{{ $reservation->user->name }} 様

誠に恐れ入りますが、ホストからの承認が **48時間以内** に得られなかったため、以下のご予約を自動的にキャンセルいたしました。

---

**キャンプサイト:** {{ $reservation->campsite->name }}

**チェックイン予定日:** {{ $reservation->check_in_date->isoFormat('YYYY年M月D日(ddd)') }}

**チェックアウト予定日:** {{ $reservation->check_out_date->isoFormat('YYYY年M月D日(ddd)') }}

**人数:** {{ $reservation->num_guests }}名

---

同じ日程で別のサイトをお探しいただくか、改めてご予約いただけますと幸いです。

<x-mail::button :url="route('campsites.index')">
サイトを探す
</x-mail::button>

ご不便をおかけして大変申し訳ございません。

{{ config('app.name') }}
</x-mail::message>
