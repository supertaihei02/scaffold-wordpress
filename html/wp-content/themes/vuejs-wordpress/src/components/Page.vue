<template>
    <b-container class="bv-example-row">
        <b-jumbotron header="Page" lead="Bootstrap 4 Components for Vue.js 2" >
        <p>{{ $route.params }}</p>
        <b-btn variant="primary" href="https://bootstrap-vue.js.org" target="_blank">More Info</b-btn>
        </b-jumbotron>
        <router-link :to="{ path: '/' }">TOP</router-link>
    </b-container>
</template>

<script>
export default {
  data: () => {
    return {
      post: void(0)
    }
  },
  mounted: () => {
    // TODO APIでpageデータを取得する（linkからの遷移ではpage_idが不明なのでguid遷移にする必要がある）
  }
}
</script>

<style lang="scss">
</style>