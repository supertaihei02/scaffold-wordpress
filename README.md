# scaffold for wordpress project
forked by FRAME LUNCH scaffold for wordpress project

## 何

よりシンプルにcomposer、デフォルトプラグインを削除し
themeにVue starterを設置して素早くwordpressテンプレートを作成する事に特化した。

## 動かす

1. `cp .env.sample .env`
    * 必要に応じてPortやコンテナ名を変更する
1. `make`
1. `yarn start`
1. 外観 > テーマ > 

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
|- .prettierignore          # prettier対象除外設定
|- .prettierrc              # prettier設定
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

##### 投稿内容へのスタイルあて

以下を参考に、利用者が投稿したコンテンツに対してスタイルを当てておく必要がある。
これでだいたいWISIWYGで付与されるスタイルは一通り当たってるはず。

```css
/* このクラスは適当 */
.post {
    h1,
    h2,
    h3,
    h4 {
        font-weight: var(--fontWeight-bold);
        margin: 0.5em 0;
    }

    h1 {
        font-size: 2.4em;
    }

    h2 {
        font-size: 2em;
    }

    h3 {
        font-size: 1.8em;
    }

    h4 {
        font-size: 1.6em;
    }

    h5 {
        font-size: 1.4em;
    }

    h6 {
        font-size: 1.2em;
    }

    em {
        font-style: italic;
    }

    strong {
        font-weight: var(--fontWeight-bold);
    }

    ul,
    ol {
        margin: 20px 0 20px 1em;
    }

    ul li {
        list-style-type: disc;
    }

    ol li {
        list-style-type: decimal;
    }

    blockquote {
        background: rgba(0, 0, 0, 0.1);
        margin: 20px 0;
        padding: 20px 30px;
        font-size: 1.1em;
    }

    a {
        color: var(--color-brand);
    }

    img {
        margin: 20px 0;
    }

    p {
        word-break: break-all;
    }

    hr {
        margin: 1rem 0;
    }
}
```

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

## 公開時の設定など

- サイトマップちゃんとしたほうがよろしい
    - Google XML Sitemaps使うのが楽っぽい。[参考](https://www.adminweb.jp/wordpress-plugin/list/index2.html)
- ちゃんとクローリングされるように設定から __検索エンジンがサイトをインデックスしないようにする__ のチェックを外すこと

## Prettierのすすめ

commit時に勝手にコードを整形してくれる。

エディタを設定すると保存時にもいい感じに整形してくれる。

### VSCode設定

1. [拡張](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode)を入れる
1. ローカル設定に以下を追加

```json
{
  // prettier
  "[javascript]": {
    "editor.formatOnSave": true
  },
  "[css]": {
    "editor.formatOnSave": true
  },
}
```

グローバル設定をどうするかは人によりますが、falseにしといたほうがいいと思います。他のプロジェクトで適当に整形されたりするので。

### IntelliJ設定

[この辺](https://qiita.com/kouchi67/items/6d3b5cf66f57c4ff6600)を参考に。

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
