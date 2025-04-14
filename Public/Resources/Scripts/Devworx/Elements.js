import {Format} from './Format.js'

export class BasicElement extends HTMLElement {
  constructor() { 
    super()
  }
  
  devworx(){
    
  }
  
  set timeout(func){
    setTimeout(func,100)
  }
  
  get rect(){
    return {
      x: this.offsetLeft, 
      y: this.offsetTop, 
      w: this.offsetWidth, 
      h: this.offsetHeight
    }
  }
  
  get x(){ return this.offsetLeft }
  set x(value){ this.style.left = `${value}px` }
  get y(){ return this.offsetTop }
  set y(value){ this.style.top = `${value}px` }
  get w(){ return this.offsetWidth }
  set w(value){ this.style.width = `${value}px` }
  get h(){ return this.offsetHeight }
  set h(value){ this.style.height = `${value}px` }
  
  get salary(){
    let result = .0
    this.querySelectorAll('devworx-salary')
      .forEach(s=>{
        const v = parseFloat(s.getAttribute('value'))
        result += isNaN(v) ? 0 : v;
      })
    return result
  }
  
  get timespan(){
    let result = .0
    this.querySelectorAll('devworx-timespan')
      .forEach(s=>{
        const v = parseFloat(s.getAttribute('total-hours'))
        result += isNaN(v) ? 0 : v;
      })
    return result
  }
  
  find(selector,iterator){
    const result = this.querySelectorAll(selector)
    if( iterator ) result.forEach(iterator)
    return result
  }

}

export class View extends BasicElement {
  constructor() { 
    super()
    //this.devworx()
  }
  
  devworx(){
    
  }
}

export class List extends BasicElement {
  constructor() { 
    super()
    this.devworx()
  }
  
  devworx(){
    const 
      type = this.getAttribute('type'),
      itemSelector = `devworx-${type.toLowerCase()}`,
      items = this.querySelectorAll(itemSelector),
      countInfo = this.querySelector('info count')
    
    this.setAttribute('count',items.length)
    if( countInfo ) countInfo.innerHTML = `${items.length} Einträge`
  }
}

export class Project extends BasicElement {
  constructor() { 
    super();
    this.timeout = _=> this.devworx()
  }
  
  devworx(){
    const timespanInfo = this.querySelector('devworx-list info timespan')
    if( timespanInfo ) timespanInfo.innerHTML = Format.number(this.timespan) + " Stunden"
    const salaryInfo = this.querySelector('devworx-list info salary')
    if( salaryInfo ) salaryInfo.innerHTML = Format.currency(this.salary)
  }
}

export class Customer extends BasicElement {
  constructor() { 
    super()
    this.devworx()
  }
  devworx(){}
}

export class Protocol extends BasicElement {
  constructor() { 
    super()
    this.devworx()
  }
  devworx(){}
}

export class Invoice extends BasicElement {
  constructor() { 
    super()
    this.devworx()
  }
  devworx(){}
}

export class Domain extends BasicElement {
  constructor() { 
    super()
    this.devworx()
  }
  devworx(){}
}

export class Contract extends BasicElement {
  constructor() { 
    super()
    this.devworx()
  }
  devworx(){}
}

export class Tabs extends BasicElement {
  #lists
  #triggers
  #active = 0
  
  constructor() { 
    super()
    this.devworx()
  }
  
  set active(value){
    if( this.#active == value ) 
      return
    this.#lists[this.#active].classList.add('d-none')
    this.#active = value
    this.#triggers.forEach(el=>el.classList.remove('active'))
    this.#triggers[this.#active].classList.add('active')
    this.#lists[this.#active].classList.remove('d-none')
  }
  
  get active(){
    return this.#active
  }
  
  devworx(){  
    this.#active = 0
    this.#lists = this.querySelectorAll(':scope > tabs > *');
    this.#triggers = this.querySelectorAll(':scope > nav > *')
    
    let active = false
    this.#triggers.forEach((el,i)=>{
      if( el.classList.contains('active') && (active === false) ){
        active = i
      } else {
        this.#lists[i].classList.add('d-none')
        el.classList.remove('active')
      }
    })
       
    this.#triggers.forEach( (el,i) => {
      el.addEventListener('click',e => {
        e.preventDefault()
        e.stopPropagation()
        this.active = i;
      })
    })
    if( active === false ){
      this.active = 0
    }
  }
  
}

export class Dialog extends BasicElement {
  #elements = {}
  
  constructor() { 
    super()
  }
  
  devworx(){
    this.setAttribute('devworx-dialog','')
    this.classList.add(
      'd-none',
      'position-fixed',
      'col-12','col-md-8','col-lg-6',
      'z-5',
      'border','border-dark','rounded',
      'shadow'
    )
    this
      .createHeader()
      .createBody()
      .createFooter()
  }
  
  get elements(){
    return this.#elements
  }
  
  get hidden(){
    return this.classList.contains('d-none')
  }
  
  get visible(){
    return !this.hidden
  }
  
  show(){
    if( this.hidden )
      this.classList.remove('d-none')
    return this
  }
  
  close(){
    if( this.visible )
      this.classList.add('d-none')
    return this
  }
  
  center(){
    this.x = Math.floor( ( innerWidth - this.w ) * .5 )
    this.y = Math.floor( ( innerHeight - this.h ) * .5 )
    return this
  }
  
  createHeader(){
    
    const index = this.#elements
    
    let result = this.querySelector(':scope > [dialog-header]')
    if( !result ){
      result = document.createElement('header')
      result.setAttribute('dialog-header','')
      result.classList.add('d-flex','flex-row','flex-shrink-0','text-bg-primary')
      
      const icon = document.createElement('span')
      icon.classList.add('mi','mi-outline','flex-shrink-0')
      icon.setAttribute('dialog-icon','')
      index.icon = icon
      
      const title = document.createElement('span')
      title.setAttribute('dialog-title','')
      title.classList.add('d-flex','flex-row','flex-grow-1','py-2')
      index.title = title
      
      const controls = document.createElement('div')
      controls.setAttribute('dialog-controls','')
      controls.classList.add('d-flex','flex-row','flex-shrink-0')
      index.controls = controls
      
      const close = document.createElement('button')
      close.setAttribute('dialog-close','')
      close.innerHTML = 'X'
      close.classList.add('btn','btn-primary')
      controls.append(close)
      close.addEventListener('click',e=>{
        e.stopPropagation()
        e.preventDefault()
        this.close()
      })
      index.close = close
      
      result.append(icon,title,controls)
      this.append(result)
    }
    index.header = result
    
    return this
  }
  
  createBody(){
    const index = this.#elements
    
    let result = this.querySelector(':scope > [dialog-body]')
    if( !result ){
      result = document.createElement('div')
      result.classList.add('d-flex','flex-column','p-3','flex-grow-1','bg-white')
      result.setAttribute('dialog-body','')
      this.append(result)
    }
    index.body = result
    
    return this
  }
  
  createFooter(){
    const index = this.#elements
    let result = this.querySelector(':scope > [dialog-footer]')
    if( !result ){
      result = document.createElement('footer')
      result.classList.add('d-flex','flex-row')
      result.setAttribute('dialog-footer','')
      
      const status = document.createElement('span')
      status.setAttribute('dialog-status','')
      status.classList.add('d-flex','flex-grow-1')
      index.status = status
      result.append(status)
      
      this.append(result)
    }
    index.footer = result
    
    return this
  }
  
  getIcon(){
    return this.#elements.icon
  }
  
  setIcon(value){
    this.#elements.icon.innerHTML = value
  }
  
  getTitle(){
    return this.#elements.title
  }
  
  setTitle(value){
    this.#elements.title.innerHTML = value
  }
  
  getBody(){
    return this.#elements.body
  }
  
  setBody(value){
    this.#elements.body.innerHTML = value
  }
  
  getStatus(){
    return this.#elements.status
  }
  
  setStatus(value){
    this.#elements.status.innerHTML = value
  }
  
  clear(){
    this.setTitle('')
    this.setBody('')
    this.setStatus('')
  }
  
  connectedCallback() {
    this.devworx()
  }
  
}