# メモ

- https://tech.recruit-mp.co.jp/infrastructure/post-11266/
- https://liginc.co.jp/327631
- 
## 動かすまで

1. `make`
2. `yarn start`

## make使わないなら

1. `cp .env.sample .env`
    - 設定ファイルを作成します。必要に応じて中身をいじりましょう。
2. `docker-compose up -d`
    - Dockerのイメージを起動します。必要に応じてダウンロードもしてきます。
3. `yarn`
    - または `npm install` 。いつものです。
4. `yarn start`
    - または `npm start` 。ビルド&ウォッチです。

