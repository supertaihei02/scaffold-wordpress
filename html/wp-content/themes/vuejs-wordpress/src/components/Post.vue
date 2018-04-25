<template>
    <b-container class="bv-example-row">
        <b-jumbotron header="Post" lead="Bootstrap 4 Components for Vue.js 2" >
        <p>{{ $route.params }}</p>
        <b-btn variant="primary" href="https://bootstrap-vue.js.org" target="_blank">More Info</b-btn>
        </b-jumbotron>
        <router-link :to="{ path: '/' }">TOP</router-link>
    </b-container>
</template>

<script>
export default {
  components: {
  }
}
</script>

<style lang="scss">
</style>