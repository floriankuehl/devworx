import * as Devworx from './Devworx/Module.js';

Devworx.Load(Devworx)

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
    const target = document.querySelector(toggle.dataset.toggle)
    if( target ){
      toggle.addEventListener('click',e=>{
        e.preventDefault()
        e.stopPropagation()
        target.classList.toggle('d-none')
      })
      target.classList.add('d-none')
    }
  })
/*
Devworx.Api.debug(true)

const apiResult = Devworx.Api.Get({
  'controller': 'incoming',
  'action': 'mittwaldImport',
}).then(result=>{
  console.log( result )
})

const apiResult = Devworx.Api.Get({
  'controller': 'domain',
  'action': 'mittwaldImport',
}).then(result=>{
  console.log( result )
})
*/


//List triggering
document.addEventListener("DOMContentLoaded", e => {
  const list = document.querySelector('#protocols')
  if( list ){
    const ts = list.querySelector('info timespan')
    ts.innerHTML = Devworx.Format.number(list.timespan) + " Stunden"
    
    const s = list.querySelector('info salary')
    s.innerHTML = Devworx.Format.currency(list.salary)
  }
})