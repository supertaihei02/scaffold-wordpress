import Vue from 'vue'
import Router from 'vue-router'

// Components
import Home from '../components/Home'
import Post from '../components/Post'
import Page from '../components/Page'

Vue.use(Router)

const router = new Router({
  routes: [
    // 上が優先
    { path: '/:year(\\d+)/:month(\\d+)/:id', name: 'Post', component: Post },
    { path: '/:slug', name: 'Page', component: Page },
    { path: '/:level1/:slug', name: 'Page', component: Page },
    { path: '/:level1/level2/:slug', name: 'Page', component: Page },
    { path: '/:level1/level2/level3/:slug?', name: 'Page', component: Page },
    { path: '/', name: 'Home', component: Home }, // ,props: { pageContentID: 383 }
  ],
  mode: 'history',
  base: '',
  scrollBehavior (to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { x: 0, y: 0 }
    }
  }
})

export default router
