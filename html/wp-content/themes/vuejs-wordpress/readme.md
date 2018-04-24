# vuejs-wordpress
forked by vuejs-wordpress-theme-starter

## Getting started

have to set prettier permalink woredpress setting

```
cd path/to/wp-content/themes/
git clone [repos] [theme-name]
cd [theme-name]
npm install
npm run watch
```

development and run `npm run production` to deploy the theme

## New to Vue?
Not sure where to begin? The [Vuejs documentation](https://vuejs.org/v2/guide/) is actually amazing, but if you're looking for video training I highly recommend the free [Learn Vue 2: Step By Step](https://laracasts.com/series/learn-vue-2-step-by-step) course over at Laracasts. There's also a [great playlist by Academind](https://www.youtube.com/watch?v=FXY1UyQfSFw&list=PL55RiY5tL51qxUbODJG9cgrsVd7ZHbPrt) available on YouTube that covers pretty much everything you'd want to know about building a fully-featured Vue app.

I've created a couple of example components in `src/components/widgets` to give you an idea of how to work with the Vuex store data.

If you're new to Vue/Vuex I would do the following:

- Open up `src/app.js` this is the main JS file for the app and will give you a glimpse into what's going on behind the scenes.
- Next open up `src/App.vue` this is the primary app component, a page wrapper of sorts. It contains the header/footer and the `<router-view>` component which is what loads in all of the other screens as you navigate around the app. Note that by default `src/components/Home.vue` is loaded into the `router-view` initially.
- Next open up `src/routes/index.js` and notice how the routes are setup by default. Out of the box there is only one route, the `/` or home view. For more information on setting up routing within your app checkout [Vue-Router](https://router.vuejs.org/).
- Next is the Vuex store. I won't cover how that works here as it's a tiny bit more advanced. Check out all files in `src/store` and start to familiarize yourself with what's going on. It's based on the official Vuex example project setup and uses Vuex modules. By default I've included a couple of modules I thought you might need.

The first thing you're probably going to want to do is start editing and components in `src/components/`. 

## Code Organization
All of the code you're going to edit is located in `/src/`. From there it's broken into a few logical directories.

- `/src`
  - `/api` for API requests
  - `/assets` for images mostly
  - `/components` Vue components
  - `/router` vue-router directives
  - `/store` vuex store and modules
  - `/styles` SCSS styles
  - `/vendor` 3rd party scripts and libraries

All scripts and styles in `/src` are compiled down to the `/dist` directory, which is what you will deploy. **When you're ready to deploy don't deploy the src/ directory.**

## External References
- [Creating Vuex Modules](https://vuex.vuejs.org/en/modules.html)
- [vue-router](https://github.com/vuejs/vue-router)
- [WordPress REST API](http://v2.wp-api.org/)

## Features coming soon:
- Example components for loading recent posts.
- WordPress menu integration
- A few more starter routes
- More documentation