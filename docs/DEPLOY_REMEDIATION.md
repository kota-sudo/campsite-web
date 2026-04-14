# Render（Docker）デプロイ不具合 — レメディ（対処まとめ）

このリポジトリ（Laravel + Vite + Tailwind）を Render で Docker デプロイした際に起きやすい問題と、すでにコードへ入れている対策・確認手順です。

---

## 1. CSS（Tailwind）が効かない

### 症状
- HTML と画像は出るが、スタイルが当たらない（素の HTML に見える）

### 主な原因と対策

| 原因 | 対策（リポジトリ内） |
|------|----------------------|
| **`public/hot` がコンテナに残っている** | Vite が「開発サーバー（HMR）」扱いになり、`127.0.0.1:5173` 向けのタグだけ出る。`Dockerfile` で `rm -f public/hot`、`docker/entrypoint.sh` 起動時にも削除。 |
| **HTTPS ページなのに CSS の URL が `http://`**（mixed content） | リバースプロキシの `X-Forwarded-Proto` を信頼する。`bootstrap/app.php` の `trustProxies(at: '*')`。 |
| **上記に加え、絶対 URL のズレ** | `AppServiceProvider` で `Vite::createAssetPathsUsing` により **`/build/assets/...` のルート相対**で出力。 |

### 確認方法
1. ページを表示 → **表示ソース**で `<link rel="stylesheet"` の `href` を確認。
2. 正しい例: `href="/build/assets/....css"`（同一オリジンのパス）。
3. 悪い例: `http://127.0.0.1:5173/...` や `http://（本番ホスト）/build/...` のみ（HTTPS だとブロックされやすい）。

### Render 側の確認
- **Environment**: `APP_URL` を実際の URL（例: `https://xxx.onrender.com`）に合わせる。
- 再デプロイ: 変更反映後 **Clear build cache & deploy** も検討。

---

## 2. `Database file ... database.sqlite does not exist`（セッション等）

### 症状
- `sessions` テーブル参照で `QueryException`

### 原因
- `SESSION_DRIVER=database` / `CACHE_STORE=database` などで SQLite を使う一方、**DB ファイルが無い**、または **マイグレーション未実行**。

### 対策（リポジトリ内）
- `docker/entrypoint.sh` で `database/database.sqlite` を `touch` し、`php artisan migrate --force` を実行。
- `DB_DATABASE` が絶対パス（永続ディスク）の場合も `touch` する分岐あり。

---

## 3. Docker ビルドで古い `vendor` / `node_modules` が混ざる

### 対策
- **`.dockerignore`** で `vendor`、`node_modules`、`public/build`、`public/hot` をホストから送らない。イメージ内の `composer install` / `npm run build` の結果だけを使う。

---

## 4. Vite 設定

### 要点
- `vite.config.js` は **`laravel-vite-plugin` の `input` のみ**（余分な `rollupOptions.input` は削除済み）。
- レイアウトは `@vite(['resources/css/app.css', 'resources/js/app.js'])`（`resources/views/layouts/app.blade.php` 等）。

---

## 5. まだ直らないときのチェックリスト

1. Render の **Build ログ**で `npm run build` が成功しているか。
2. コンテナ内に **`public/build/manifest.json`** があるか（イメージ or 起動後ログで確認）。
3. ブラウザ **開発者ツール → Network** で CSS が **200** か **blocked / (failed)** か。
4. `APP_DEBUG=true` を一時的に有効にし、スタックトレースの有無を確認（本番では戻す）。

---

以上が本プロジェクト向けのレメディです。GitHub Issues に転記してチケット化しても構いません。
