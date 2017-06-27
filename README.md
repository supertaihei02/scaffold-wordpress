# FRAME LUNCH scaffold for wordpress project

## 何

フロントまわりを極力npmに切り離したWordpress開発環境です。  
WordpressとMySQLはDocker使ってフレッシュ&クリーン&高速&シンプルな環境を用意しました。

元ネタのテーマは[BlankSlate](http://tidythemes.com/concept/)を使いました。
 
## 動かす

1. `make`
2. `yarn start`
3. ブラウザで `http://localhost`
    - 初回はいろいろ言われるので適当にセットアップ
4. 外観 > テーマ > fl

あとはよしなに。 `yarn build` で例によって圧縮版を出力します

### make使わないなら

1. `cp .env.sample .env`
    - 設定ファイルを作成します。必要に応じて中身をいじりましょう。
2. `docker-compose up -d`
    - Dockerのイメージを起動します。必要に応じてダウンロードもしてきます。
3. `yarn`
    - または `npm install` 。いつものです。
4. `yarn start`
    - または `npm start` 。ビルド&ウォッチです。
5. ブラウザで `http://localhost`
    - 初回はいろいろ言われるので適当にセットアップ
6. 外観 > テーマ > fl

## DBの状態をGit管理したいとき
sh tools/shell-scripts/export.sh

## TODO or 野望

### そもそも案件こなしたことないからほとんど妄想なんだけど、こんなんでいいのかな？

- [ ] 画像とかフォントとかassets関係の整備
- [ ] Reset.css系の当て込み
- [ ] font-awesomeをnpmから突っ込む
- [x] ESLint
- [x] stylelint
- [ ] Wordpressってwatchしてリロードってやってくんないの？
    - 手動でJS書けばOK
- [ ] 初期データを適当に突っ込む
- [ ] テーマの中を整備 またはドキュメントで補足
- [ ] ステージング用デプロイ環境なんかないか
- [x] プラグイン開発への対応

## メモ

- https://tech.recruit-mp.co.jp/infrastructure/post-11266/
- https://liginc.co.jp/327631
- http://tech.quartetcom.co.jp/2014/06/30/wordpress-silex-twig/
    - コレができたらいけてるだろうな・・・
- http://oki2a24.com/2014/06/28/set-wordpress-permalink-or-nginx/ 
