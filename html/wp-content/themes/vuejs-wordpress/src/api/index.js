export default {
  getCategories (cb) {
    axios.get(window.SETTINGS.API_BASE_PATH + 'categories?sort=name&hide_empty=true&per_page=50')
      .then(response => {
        cb(response.data.filter(c => c.name !== "Uncategorized"))
      })
      .catch(e => {
        cb(e)
      })
  },

  getPages (cb) {
    axios.get(window.SETTINGS.API_BASE_PATH + 'pages?per_page=10')
      .then(response => {
        if( location.origin === undefined ){
          location.origin = location.protocol + "//" + location.hostname + (location.port ? ":" + location.port : "");
        }
        for(var i in response.data) {
          let page = response.data[i]
          if (!page.link.indexOf(location.origin)) { //same domain
            page.link = page.link.split(location.origin).join('')
          }
          response.data[i] = page
        }
        cb(response.data)
      })
      .catch(e => {
        cb(e)
      })
  },

  getPage (id, cb) {
    if (_.isNull(id) || !_.isNumber(id)) return false
    axios.get(window.SETTINGS.API_BASE_PATH + 'pages/'+id)
      .then(response => {
        if( location.origin === undefined ){
          location.origin = location.protocol + "//" + location.hostname + (location.port ? ":" + location.port : "");
        }
        if (!response.data.link.indexOf(location.origin)) { //same domain
          response.data.link = response.data.link.split(location.origin).join('')
        }
        cb(response.data)
      })
      .catch(e => {
        cb(e)
      })
  },

  getPosts (limit, cb) {
    if (_.isEmpty(limit)) { let limit = 5 }
    
    axios.get(window.SETTINGS.API_BASE_PATH + 'posts?per_page='+limit)
      .then(response => {
        if( location.origin === undefined ){
          location.origin = location.protocol + "//" + location.hostname + (location.port ? ":" + location.port : "");
        }
        for(var i in response.data) {
          let post = response.data[i]
          if (!post.link.indexOf(location.origin)) { //same domain
            post.link = post.link.split(location.origin).join('')
          }
          response.data[i] = post
        }
        cb(response.data)
      })
      .catch(e => {
        cb(e)
      })
  },
}
