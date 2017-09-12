# FRAME LUNCH scaffold for wordpress project

## 何

フロントまわりを極力npmに切り離したWordpress開発環境です。  
WordpressとMySQLはDocker使ってフレッシュ&クリーン&高速&シンプルな環境を用意しました。  
元ネタのテーマは[BlankSlate](http://tidythemes.com/concept/)を使いました。

## 動かす

1. `make`
2. `yarn start`
3. 外観 > テーマ > fl

あとはよしなに。 なお、 `yarn build` で例によって圧縮版を出力します。  
dockerポートが被るので同時起動できません。  
「docker-compose.yml」でポート番号を変更するか以下手順で起動中のプロジェクトを停止する必要があります。  

```bash
$ cd path/to/running/project
$ docker-compose down
```


## DBの状態をGit管理したいとき

DBファイルをバックアップ取ってやります。

```bash
# make export
```

## ディレクトリ構成

```text
.
|- /.github/                # GitHub用issue, PRテンプレ
|- /data/                   # DBデータ 今のところMySQLのみ(自動生成ディレクトリ)
|- /flow-typed/             # flowtype用型ファイル(自動生成ディレクトリ)
|- /frontend/               # フロントエンドを構成するファイル
|    |- /assets/            # まるごとthemes/fl以下にコピー
|    |- /scripts/           # JavaScriptはこちら
|    |- /styles/            # CSSはこちら
|- /logs/                   # ログ 今のところnginxしかない
|- /node_modules/           # 3rd-party libraries and utilities for nodeJs (自動生成ディレクトリ)
|- /settings/               # マウントしたい設定型ファイル 今のところnginxしかない
|- /tools/                  # ビルドツール関連
|    |- /gulp/              # gulpタスクを記述したjs。タスクごとに1ファイルとする
|    |- /shell-scripts/     # 便利系シェル
|    |- /webpack/           # webpackビルド設定
|    |- /config.js          # ビルド関係設定ファイル
|- /wordpress/              # テーマやプラグインはこちら
|    |- /plugins/           # プラグイン開発ディレクトリ WPコンテナにまるごとマウントされとる
|    |- /themes/            # テーマ開発ディレクトリ
|        |- /fl/            # 実際に扱うテーマ
|- .env                     # ローカル環境変数設定ファイル (自動生成ディレクトリ)
|- .env.sample              # .env元ネタ
|- .eslintignore            # eslintから除外するファイル
|- .eslintrc                # eslint設定ファイル
|- .flowconfig              # flowtype設定ファイル
|- .gitattributes           # git設定 yarn.lockをバイナリ扱いなど
|- .gitignore               # git管理対象外を記述
|- .node-version            # ndenv用のバージョン指定
|- .stylelintrc             # stylelint設定ファイル
|- gulpfile.js              # gulp実行ファイル
|- Makefile                 # makeコマンド設定ファイル
|- package.json             # The list of 3rd party libraries for nodeJs
|- README.md                # README
|- yarn.lock                # yarn用利用npmsバージョン管理ファイル
```

### flテーマ開発手引

#### 元ネタ

[BlankSlate](http://tidythemes.com/concept/)

ほとんどいじってないです。画像突っ込めるかの確認に"武丸"さんを貼っただけ

#### JS、CSSまわり

使っているツール周りは基本的に[scaffold-frontend](https://github.com/framelunch/scaffold-frontend)と同じです。
一部使ってないツールや設定(JSからのCSS読み込み)が混じってますが、どうしようか決めかねてます。

#### いじりどころ

共通っぽいファイルは端折った
ブログやるわけでない場合、かなり削除できる

- 設定とか: `functions.php`
- メイン: `index.php`
    - ヘッダー: `header.php`
    - フッター: `footer.php`
    - サイドバー: `sidebar.php`
    - エントリー: `entry.php`
    - ナビ: `nav-below.php`
- 個別: 'entry.php'
    - フッター: `entry-footer.php`
    - メタ(投稿者、日付): `entry-meta.php`
    - サマリー: `entry-summary.php` (カテゴリ一覧とか用)
        - 抜粋: `the_excerpt`
        - 詳細表示: `wp_link_pages`
    - 中身: `entry-content.php` (アーカイブされてない記事用)
        - サムネイル: `the_post_thumbnail`
        - 記事: `the_content`
        - 詳細表示: `wp_link_pages`
- サムネイル表示用: `attachment.php`
- 対象なし: `404.php`
- 月別一覧: `archive.php`
- 著者別一覧: `author.php`
- カテゴリ別一覧: `category.php`
- 検索結果: `search.php`
- 固定ページ: `page.php`
- タグ別一覧ページ: `tag.php`

## TODO or 野望

### そもそも案件こなしたことないからほとんど妄想なんだけど、こんなんでいいのかな？

- [x] 画像とかフォントとかassets関係の整備
- [x] Reset.css系の当て込み
- [ ] font-awesomeをnpmから突っ込む
- [x] ESLint
- [x] stylelint
- [x] Wordpressってwatchしてリロードってやってくんないの？
- [x] 初期データを適当に突っ込む
    - ダンプ読めるようにしてもらった
- [x] テーマの中を整備 またはドキュメントで補足
- [ ] ステージング用デプロイ環境なんかないか
- [x] プラグイン開発への対応
- [ ] WP REST APIあたりを上手に組み込めないか？
    - 使わないケースも当然多々あるので、切り替えがわかりやすくできるとベスト
- [ ] しばらくほっといて、久々に触ると502エラー

## メモ

- https://tech.recruit-mp.co.jp/infrastructure/post-11266/
- https://liginc.co.jp/327631
- http://tech.quartetcom.co.jp/2014/06/30/wordpress-silex-twig/
    - コレができたらいけてるだろうな・・・
- http://oki2a24.com/2014/06/28/set-wordpress-permalink-or-nginx/
- https://torounit.com/blog/2017/03/15/3396/
