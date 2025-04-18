import * as Devworx from './Devworx/Module.js';
Devworx.Load(Devworx)

class Provide {
  
  static #cache = {}
  /*
  static async ProtocolEditor(project){
    Devworx.Api.text(false)
    return Promise.all([
      Devworx.Api.Get({
        'controller': 'project',
        'action': 'get',
        'uid': project,
        'short': 1
      }),
      Devworx.Api.Get({
        'controller': 'protocol',
        'action': 'articles'
      })
    ]).then(
      result => Devworx.Api.text(true).Post({
        'controller': 'provider',
        'action': 'partial',
      },{
        'name': 'Editor/Protocol',
        'variables': {
          'project': result[0].result.item,
          'articles': result[1].result.list,
          'create': 1
        }
      })
    )
  }
  */
}

document.querySelectorAll('[data-confirm]')
  .forEach(a => {
    a.addEventListener('click',e=>{
      e.preventDefault()
      e.stopPropagation()
      if( confirm( a.dataset.confirm ) )
        window.location.href = a.getAttribute('href');
    })
  })
  
document.querySelectorAll('[data-toggle]')
  .forEach(toggle => {
    toggle.addEventListener('click',e=>{
      e.preventDefault()
      e.stopPropagation()
      toggle.classList.toggle('active')
      document.querySelectorAll(toggle.dataset.toggle)
        .forEach(target=>target.classList.toggle('d-none'))
    })
    toggle.classList.remove('active')
    document.querySelectorAll(toggle.dataset.toggle)
        .forEach(target=>target.classList.add('d-none'))
  })
  
document.querySelectorAll('[data-dialog]')
  .forEach(el => {
    el.addEventListener('click',e=>{
      e.preventDefault()
      e.stopPropagation()
      
      let dialog = null
      
      if( 'dialog' in el )
        dialog = el.dialog
      else {
        dialog = document.createElement('devworx-dialog')
        document.body.append(dialog)
        el.dialog = dialog
      }
      
      dialog.setTitle(el.title)
      
      let options = el.dataset.dialog === '' ? {} : JSON.parse(el.dataset.dialog)
      if( 'name' in options ){
        if( options.name in Provide ){
          console.log( 'Found Provider', options.name, ...options.args )
          Provide[options.name]( ...options.args )
            .then(result => {
              dialog.setBody(result)
              dialog.show()
              dialog.center()
              
              const form = dialog.getBody().querySelector(':scope form')
              if( form ){
                form.addEventListener('submit',e=>{
                  dialog.close()
                })
              }
            })
        }
      }

      return false
    })
  })

/*
Devworx.Api.register(
  'importDomains',
  _=> Devworx.Api.Get({
    'controller': 'import',
    'action': 'domainImport'
  })
)
Devworx.Api.trigger('importDomains').then(result=>console.log(result))
*/

/*
//Complex api call
Promise.all([
  Devworx.Api.Get({
    'controller': 'project',
    'action': 'get',
    'uid': 1,
    'short': 1
  }),
  Devworx.Api.Get({
    'controller': 'protocol',
    'action': 'articles'
  })
]).then(
  result => Devworx.Api.text(true).Post({
    'controller': 'provider',
    'action': 'partial',
  },{
    'name': `Editor/Protocol`,
    'variables': {
      'project': result[0].result.item,
      'articles': result[1].result.list,
      'create': 1
    }
  })
).then(partial=>{
  console.log( partial )
})
*/

//List calculations and formatting
document.addEventListener("DOMContentLoaded", e => {
  
})
