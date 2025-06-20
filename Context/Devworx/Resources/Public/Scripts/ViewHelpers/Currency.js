import Format from '../Format.js'
import CustomElement from '../CustomElement.js'

export default class Currency extends CustomElement(HTMLDataElement) {
  constructor() { 
    super()
  }
  
  static get baseTag(){ return 'data' }
  
  connectedCallback(){
    const value = parseFloat( this.getAttribute('value') );
    this.innerHTML = Format.currency(value);
  }
}