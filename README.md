# Frequently Bought Together for Elementor

Elementor用のAmazonスタイル「よく一緒に購入されている商品」ウィジェットプラグイン

## 概要

このプラグインは、ElementorとWooCommerceを使用したサイトに、Amazonのような「よく一緒に購入されている商品」機能を追加します。

## 主な機能

### 1. 商品選択機能
- 管理画面から表示する商品を自由に選択
- SELECT2を使用した商品検索機能
- 複数商品の選択が可能

### 2. チェックボックス機能
- 各商品カードの右上にチェックボックスを配置
- デフォルトで全商品にチェック
- ユーザーが任意で商品を選択/解除可能
- チェックした商品のみをカートに追加

### 3. ループテンプレート対応
- Elementorのループテンプレートを選択可能
- カスタムデザインの商品カードを使用できる
- デフォルトテンプレートも用意

### 4. 価格計算機能
- 選択した商品の合計価格を自動計算
- ポイント計算（1%）の表示
- WooCommerceの価格フォーマットに対応

### 5. カート追加機能
- 選択した商品を一括でカートに追加
- AJAX通信でページ遷移なし
- 成功/エラーメッセージの表示
- カートへの自動リダイレクト（オプション）

### 6. レスポンシブデザイン
- PC/タブレット/スマホに対応
- カラム数の自動調整
- タッチデバイスでも快適な操作

### 7. カスタマイズ可能
- Elementorのスタイル設定から細かくカスタマイズ
- カラー、フォント、間隔などを自由に調整
- カスタムCSSにも対応

## 必要な環境

- WordPress 5.8以上
- PHP 7.4以上
- Elementor（無料版でOK）
- WooCommerce

## インストール方法

1. このプラグインフォルダを `wp-content/plugins/` ディレクトリにアップロード
2. WordPressの管理画面から「プラグイン」→「インストール済みプラグイン」
3. 「Frequently Bought Together for Elementor」を有効化

## 使い方

### 1. Elementorでページを編集

1. Elementorでページまたはテンプレートを編集
2. ウィジェットパネルから「よく一緒に購入されている商品」を検索
3. ページにドラッグ&ドロップ

### 2. 商品を選択

1. 左側のパネルで「商品を選択」をクリック
2. 検索ボックスに商品名やIDを入力
3. 候補から商品を選択（複数選択可）

### 3. ループテンプレートを選択（オプション）

1. 「ループテンプレート」から使用したいテンプレートを選択
2. 未選択の場合はデフォルトデザインが使用されます

### 4. スタイルをカスタマイズ

1. 「スタイル」タブから各要素のデザインを調整
2. レイアウト、タイトル、商品カード、ボタンなどを個別に設定

## ファイル構成

```
frequently-bought-together/
├── frequently-bought-together.php   # メインプラグインファイル
├── README.md                         # このファイル
├── assets/
│   ├── css/
│   │   └── fbt-style.css            # フロントエンドスタイル
│   └── js/
│       └── fbt-script.js            # フロントエンドスクリプト
└── includes/
    └── widgets/
        └── class-fbt-widget.php      # Elementorウィジェットクラス
```

## カスタマイズ例

### テーマのfunctions.phpで追加のスタイルを適用

```php
add_action('wp_enqueue_scripts', 'custom_fbt_styles', 20);
function custom_fbt_styles() {
    wp_add_inline_style('fbt-style', '
        .fbt-product-item {
            border: 2px solid #your-color;
        }
    ');
}
```

### カートリダイレクトを無効化

```php
add_filter('fbt_redirect_to_cart', '__return_false');
```

### ポイント率を変更

```php
add_filter('fbt_point_rate', function($rate) {
    return 0.02; // 2%
});
```

## トラブルシューティング

### ウィジェットが表示されない

- Elementorが有効化されているか確認
- WooCommerceが有効化されているか確認
- ブラウザのキャッシュをクリア

### 商品がカートに追加されない

- ブラウザのコンソールでエラーを確認
- WooCommerceのカート機能が正常か確認
- 在庫設定を確認

### スタイルが反映されない

- Elementorのキャッシュをクリア
- WordPressのキャッシュプラグインをクリア
- ブラウザのキャッシュをクリア

## 開発者向け情報

### フック・フィルター

#### アクション

- `fbt_before_products_grid` - 商品グリッド前
- `fbt_after_products_grid` - 商品グリッド後
- `fbt_before_add_to_cart` - カート追加前
- `fbt_after_add_to_cart` - カート追加後

#### フィルター

- `fbt_product_args` - 商品クエリ引数
- `fbt_point_rate` - ポイント率
- `fbt_redirect_to_cart` - カートリダイレクト
- `fbt_ajax_response` - AJAX レスポンス

### JavaScriptイベント

```javascript
// カート追加前
jQuery(document).on('fbt_before_add_to_cart', function(e, productIds) {
    console.log('Adding products:', productIds);
});

// カート追加後
jQuery(document).on('fbt_after_add_to_cart', function(e, response) {
    console.log('Added successfully:', response);
});
```

## ライセンス

GPL v2 or later

## 作者

E-mot
https://e-mot.co.jp

## 更新履歴

### 1.0.0 (2025-12-23)
- 初回リリース
- 基本機能の実装
- Elementor対応
- WooCommerce対応

## サポート

不具合報告や機能要望は、GitHubのIssuesまたは直接お問い合わせください。
