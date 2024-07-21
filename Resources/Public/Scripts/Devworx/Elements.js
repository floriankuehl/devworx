import {Format} from './Format.js'

export class BasicElement extends HTMLElement {
  constructor() { 
    super()
    //this.devworx()
  }
  
  devworx(){
    
  }
  
  set timeout(func){
    setTimeout(func,100)
  }
  
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
    this.#lists[this.#active].classList.remove('d-none')
  }
  
  get active(){
    return this.#active
  }
  
  devworx(){  
    this.#active = 0
    this.#lists = this.querySelectorAll(':scope > tabs > *');
    
    this.querySelectorAll(':scope > nav > *').forEach( (el,i) => {
      el.addEventListener('click',e => {
        e.preventDefault();
        e.stopPropagation();
        this.active = i;
      })
    })
  }
  
}
