<x-mail::message>
# 新しいお問い合わせが届きました

管理者の方へ

以下のお問い合わせが送信されました。管理画面よりご対応ください。

---

**氏名:** {{ $contact->name }}

**メールアドレス:** {{ $contact->email }}

**件名:** {{ $contact->subject }}

**内容:**

{{ $contact->body }}

---

<x-mail::button :url="route('admin.contacts.index')">
管理画面で確認する
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
