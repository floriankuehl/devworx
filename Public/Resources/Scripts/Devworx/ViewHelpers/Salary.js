import Format from '../Format.js'
import Timespan from './Timespan.js'

export default class Salary extends Timespan {
  constructor() { 
    super()
  }
  
  static get baseTag(){ return 'data' }
  
  connectedCallback(){
    super.connectedCallback()
    const 
      value = parseFloat( this.getAttribute('value') ),
      price = parseFloat( this.getAttribute('price') ),
      fix = this.getAttribute('fix') == '1'
    const total = fix ? value : value * price
    this.setAttribute('value',total)
    this.innerHTML = Format.currency(total);
  }
}