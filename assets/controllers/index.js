// assets/controllers/index.js
import { Application } from '@hotwired/stimulus'

// start Stimulus
const application = Application.start()

// import controllers manually
import PostController from './post_controller'
import HelloController from './hello_controller'
import AlbumsController from './albums_controller'
import extrafieldController from './extrafield_controller'

// register them
application.register('post', PostController)
application.register('hello', HelloController)
application.register('albums', AlbumsController)
application.register('extrafield', extrafieldController)

export default application
